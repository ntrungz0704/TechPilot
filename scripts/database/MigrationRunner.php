<?php

/**
 * Chạy PHP migrations theo thứ tự tên file và ghi ledger sau khi up() thành công.
 *
 * Class này không tự tạo kết nối database để integration test có thể truyền vào
 * một PDO đang dùng TEMPORARY tables, tránh chạm dữ liệu thật.
 */
final class MigrationRunner
{
    private const LEDGER_TABLE = 'migrations';
    private const LOCK_TIMEOUT_SECONDS = 10;

    private PDO $db;
    private string $migrationsDir;
    private string $lockName;

    public function __construct(PDO $db, string $migrationsDir)
    {
        $this->db = $db;
        $this->migrationsDir = rtrim($migrationsDir, '/\\');

        $databaseName = (string)$db->query('SELECT DATABASE()')->fetchColumn();
        $this->lockName = 'techpilot_migrations_' . substr(
            hash('sha256', $databaseName !== '' ? $databaseName : 'default'),
            0,
            32
        );
    }

    /**
     * @return array{total:int, applied:int, pending:int, entries:array<int, array<string, string>>}
     */
    public function status(): array
    {
        $files = $this->migrationFiles();
        $appliedLookup = $this->ledgerTableExists() ? $this->appliedLookup() : [];
        $entries = [];
        $applied = 0;
        $pending = 0;

        foreach ($files as $file) {
            $migration = basename($file);
            $state = isset($appliedLookup[$migration]) ? 'applied' : 'pending';
            if ($state === 'applied') {
                $applied++;
            } else {
                $pending++;
            }
            $entries[] = [
                'migration' => $migration,
                'state' => $state,
            ];
        }

        return [
            'total' => count($files),
            'applied' => $applied,
            'pending' => $pending,
            'entries' => $entries,
        ];
    }

    /**
     * @return array{total:int, applied:int, skipped:int, failed:int, entries:array<int, array<string, string>>}
     */
    public function run(): array
    {
        $this->acquireLock();

        try {
            $this->ensureLedgerTable();

            $files = $this->migrationFiles();
            $appliedLookup = $this->appliedLookup();
            $result = [
                'total' => count($files),
                'applied' => 0,
                'skipped' => 0,
                'failed' => 0,
                'entries' => [],
            ];

            foreach ($files as $file) {
                $migration = basename($file);

                if (isset($appliedLookup[$migration])) {
                    $result['skipped']++;
                    $result['entries'][] = [
                        'migration' => $migration,
                        'state' => 'skipped',
                        'message' => 'đã có trong ledger',
                    ];
                    continue;
                }

                try {
                    $className = $this->loadAndValidate($file);
                    $migrationResult = $className::up($this->db);

                    if ($migrationResult !== true) {
                        throw new RuntimeException('up() phải trả về true khi thành công.');
                    }

                    $this->recordApplied($migration);
                    $appliedLookup[$migration] = true;
                    $result['applied']++;
                    $result['entries'][] = [
                        'migration' => $migration,
                        'state' => 'applied',
                        'message' => 'up() thành công và đã ghi ledger',
                    ];
                } catch (Throwable $error) {
                    $result['failed'] = 1;
                    $result['entries'][] = [
                        'migration' => $migration,
                        'state' => 'failed',
                        'message' => $error->getMessage(),
                    ];

                    // Không chạy migration phía sau khi database đang ở trạng thái chưa xác định.
                    break;
                }
            }

            return $result;
        } finally {
            $this->releaseLock();
        }
    }

    /**
     * Ghi nhận các migration hiện có mà không gọi up(). Chỉ dùng một lần cho
     * database legacy đã được xác minh là khớp schema/data hiện tại.
     *
     * @return array{total:int, baselined:int, skipped:int, failed:int, entries:array<int, array<string, string>>}
     */
    public function baseline(): array
    {
        $this->acquireLock();

        try {
            $this->ensureLedgerTable();

            $files = $this->migrationFiles();
            $appliedLookup = $this->appliedLookup();
            $pendingFiles = [];
            $result = [
                'total' => count($files),
                'baselined' => 0,
                'skipped' => 0,
                'failed' => 0,
                'entries' => [],
            ];

            // Xác thực toàn bộ file trước khi ghi bất kỳ ledger record nào.
            foreach ($files as $file) {
                $migration = basename($file);

                if (isset($appliedLookup[$migration])) {
                    $result['skipped']++;
                    $result['entries'][] = [
                        'migration' => $migration,
                        'state' => 'skipped',
                        'message' => 'đã có trong ledger',
                    ];
                    continue;
                }

                try {
                    $this->loadAndValidate($file);
                    $pendingFiles[] = $file;
                } catch (Throwable $error) {
                    $result['failed'] = 1;
                    $result['entries'][] = [
                        'migration' => $migration,
                        'state' => 'failed',
                        'message' => $error->getMessage(),
                    ];
                    return $result;
                }
            }

            if ($pendingFiles === []) {
                return $result;
            }

            $managesTransaction = !$this->db->inTransaction();
            $entriesBeforeTransaction = $result['entries'];

            try {
                if ($managesTransaction) {
                    $this->db->beginTransaction();
                }

                foreach ($pendingFiles as $file) {
                    $migration = basename($file);
                    $this->recordApplied($migration);
                    $result['baselined']++;
                    $result['entries'][] = [
                        'migration' => $migration,
                        'state' => 'baselined',
                        'message' => 'chỉ ghi ledger, không gọi up()',
                    ];
                }

                if ($managesTransaction) {
                    $this->db->commit();
                }
            } catch (Throwable $error) {
                if ($managesTransaction && $this->db->inTransaction()) {
                    $this->db->rollBack();
                }

                $result['baselined'] = 0;
                $result['failed'] = 1;
                $result['entries'] = $entriesBeforeTransaction;
                $result['entries'][] = [
                    'migration' => '[ledger]',
                    'state' => 'failed',
                    'message' => $error->getMessage(),
                ];
            }

            return $result;
        } finally {
            $this->releaseLock();
        }
    }

    private function ensureLedgerTable(): void
    {
        $this->db->exec(
            "CREATE TABLE IF NOT EXISTS `" . self::LEDGER_TABLE . "` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `migration` VARCHAR(255) NOT NULL,
                `executed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `migration` (`migration`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $columns = $this->db->query(
            "SHOW COLUMNS FROM `" . self::LEDGER_TABLE . "`"
        )->fetchAll(PDO::FETCH_COLUMN);
        $missingColumns = array_diff(['id', 'migration', 'executed_at'], $columns);

        if ($missingColumns !== []) {
            throw new RuntimeException(
                'Bảng migrations thiếu cột bắt buộc: ' . implode(', ', $missingColumns)
            );
        }
    }

    private function ledgerTableExists(): bool
    {
        try {
            $this->db->query(
                "SELECT 1 FROM `" . self::LEDGER_TABLE . "` LIMIT 1"
            );
            return true;
        } catch (PDOException $error) {
            $driverCode = (int)($error->errorInfo[1] ?? 0);
            if ((string)$error->getCode() === '42S02' || $driverCode === 1146) {
                return false;
            }

            throw $error;
        }
    }

    /** @return array<int, string> */
    private function migrationFiles(): array
    {
        if (!is_dir($this->migrationsDir)) {
            return [];
        }

        $files = glob($this->migrationsDir . '/*.php');
        if ($files === false) {
            throw new RuntimeException('Không thể quét thư mục migrations: ' . $this->migrationsDir);
        }

        sort($files, SORT_STRING);
        return $files;
    }

    /** @return array<string, bool> */
    private function appliedLookup(): array
    {
        $rows = $this->db->query(
            "SELECT `migration` FROM `" . self::LEDGER_TABLE . "`"
        )->fetchAll(PDO::FETCH_COLUMN);

        $lookup = [];
        foreach ($rows as $migration) {
            $lookup[(string)$migration] = true;
        }

        return $lookup;
    }

    private function loadAndValidate(string $file): string
    {
        $migration = basename($file);
        $nameWithoutExtension = pathinfo($migration, PATHINFO_FILENAME);
        $className = 'Migration_' . $nameWithoutExtension;

        require_once $file;

        if (!class_exists($className, false)) {
            throw new RuntimeException("Không tìm thấy class {$className}.");
        }

        $method = new ReflectionMethod($className, 'up');
        if (!$method->isPublic() || !$method->isStatic()) {
            throw new RuntimeException("{$className}::up() phải là public static.");
        }

        $parameters = $method->getParameters();

        if (count($parameters) !== 1) {
            throw new RuntimeException("{$className}::up() phải nhận đúng một tham số PDO.");
        }

        $parameterType = $parameters[0]->getType();

        if (
            !$parameterType instanceof ReflectionNamedType
            || $parameterType->getName() !== PDO::class
        ) {
            throw new RuntimeException("{$className}::up() phải nhận đúng một tham số PDO.");
        }

        $returnType = $method->getReturnType();
        if (!$returnType instanceof ReflectionNamedType || $returnType->getName() !== 'bool') {
            throw new RuntimeException("{$className}::up() phải khai báo kiểu trả về bool.");
        }

        return $className;
    }

    private function recordApplied(string $migration): void
    {
        $statement = $this->db->prepare(
            "INSERT INTO `" . self::LEDGER_TABLE . "` (`migration`) VALUES (:migration)"
        );
        $statement->execute([':migration' => $migration]);
    }

    private function acquireLock(): void
    {
        $statement = $this->db->prepare('SELECT GET_LOCK(:lock_name, :timeout_seconds)');
        $statement->bindValue(':lock_name', $this->lockName, PDO::PARAM_STR);
        $statement->bindValue(':timeout_seconds', self::LOCK_TIMEOUT_SECONDS, PDO::PARAM_INT);
        $statement->execute();

        if ((int)$statement->fetchColumn() !== 1) {
            throw new RuntimeException(
                'Không lấy được migration lock; có thể một runner khác đang hoạt động.'
            );
        }
    }

    private function releaseLock(): void
    {
        $statement = $this->db->prepare('SELECT RELEASE_LOCK(:lock_name)');
        $statement->execute([':lock_name' => $this->lockName]);
    }
}
