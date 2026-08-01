<?php

/**
 * Regression tests for the ledger-aware PHP migration runner.
 *
 * All integration checks use TEMPORARY tables, so the real migration ledger
 * and application data are never changed by this test.
 */

define('ROOT_PATH', dirname(__DIR__));

final class MigrationRunnerSafetyTest
{
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    public function run(): void
    {
        echo "========================================================\n";
        echo "=== TECHPILOT MIGRATION RUNNER SAFETY TEST SUITE    ===\n";
        echo "========================================================\n\n";

        $runnerReady = $this->testSourceContracts();

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();
        $this->assert($db instanceof PDO, 'Có PDO để chạy integration bằng TEMPORARY tables');

        if ($runnerReady && $db instanceof PDO) {
            $this->testLedgerAwareRunner($db);
            $this->testFailureStopsQueue($db);
            $this->testBaselineDoesNotExecuteMigrations($db);
            $this->testPromoMigrationIsIdempotent($db);
        }

        echo "\n════════════════════════════════════════════════════════\n";
        echo "Migration Runner Safety Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "════════════════════════════════════════════════════════\n";

        if ($this->failed > 0) {
            echo "\n[FAIL] MIGRATION RUNNER SAFETY ERRORS:\n";
            foreach ($this->errors as $error) {
                echo "  - {$error}\n";
            }
            exit(1);
        }

        exit(0);
    }

    private function testSourceContracts(): bool
    {
        echo "--- 1. Source contracts ---\n";

        $runnerClassPath = ROOT_PATH . '/scripts/database/MigrationRunner.php';
        $runnerExists = file_exists($runnerClassPath);
        $this->assert($runnerExists, 'MigrationRunner tách khỏi CLI để có thể kiểm thử cô lập');

        if ($runnerExists) {
            require_once $runnerClassPath;
        }

        $runnerReady = class_exists('MigrationRunner');
        $this->assert($runnerReady, 'MigrationRunner class tồn tại');

        if ($runnerReady) {
            $reflection = new ReflectionClass('MigrationRunner');
            $this->assert($reflection->hasMethod('run'), 'Runner có run() cho migration pending');
            $this->assert($reflection->hasMethod('status'), 'Runner có status() để kiểm tra an toàn');
            $this->assert($reflection->hasMethod('baseline'), 'Runner có baseline() cho database legacy đã đồng bộ');
        }

        $cliSource = file_get_contents(ROOT_PATH . '/scripts/database/migrate.php');
        $this->assert(
            str_contains($cliSource, '--baseline-existing'),
            'CLI chỉ baseline khi có cờ --baseline-existing rõ ràng'
        );

        $promoSource = file_get_contents(
            ROOT_PATH . '/database/migrations/2026_08_01_000001_reset_promo_data.php'
        );
        $this->assert(!str_contains($promoSource, 'rand('), 'Promo migration không dùng random');
        $this->assert(
            str_contains($promoSource, 'MOD(`id`, 11)'),
            'Promo migration dùng công thức giảm giá deterministic theo product id'
        );

        $seedSource = file_get_contents(ROOT_PATH . '/database/seed.sql');
        $migrationFiles = glob(ROOT_PATH . '/database/migrations/*.php') ?: [];
        sort($migrationFiles, SORT_STRING);

        foreach ($migrationFiles as $migrationFile) {
            $basename = basename($migrationFile);
            $this->assert(
                str_contains($seedSource, $basename),
                "Seed baseline chứa {$basename}"
            );
        }

        return $runnerReady;
    }

    private function testLedgerAwareRunner(PDO $db): void
    {
        echo "\n--- 2. Chỉ chạy migration pending đúng một lần ---\n";
        $this->resetTemporaryTables($db);

        $runner = new MigrationRunner(
            $db,
            ROOT_PATH . '/tests/fixtures/migrations/success'
        );

        $initialStatus = $runner->status();
        $this->assert($initialStatus['pending'] === 2, 'Status ban đầu báo đúng 2 migration pending');
        $this->assert($initialStatus['applied'] === 0, 'Status ban đầu chưa có migration fixture nào applied');

        $firstRun = $runner->run();
        $this->assert($firstRun['applied'] === 2, 'Lần chạy đầu áp dụng 2 migration');
        $this->assert($firstRun['skipped'] === 0, 'Lần chạy đầu không skip migration');
        $this->assert($firstRun['failed'] === 0, 'Lần chạy đầu không lỗi');
        $this->assert($this->probeValue($db, 'success_one') === 1, 'Migration success_one chỉ tăng một lần');
        $this->assert($this->probeValue($db, 'success_two') === 1, 'Migration success_two chỉ tăng một lần');
        $this->assert($this->ledgerCount($db) === 2, 'Ledger ghi đúng 2 migration sau khi thành công');

        $secondRun = $runner->run();
        $this->assert($secondRun['applied'] === 0, 'Lần chạy hai không áp dụng lại migration');
        $this->assert($secondRun['skipped'] === 2, 'Lần chạy hai skip cả 2 migration đã ghi ledger');
        $this->assert($secondRun['failed'] === 0, 'Lần chạy hai không lỗi');
        $this->assert($this->probeValue($db, 'success_one') === 1, 'success_one không bị chạy lại');
        $this->assert($this->probeValue($db, 'success_two') === 1, 'success_two không bị chạy lại');
    }

    private function testFailureStopsQueue(PDO $db): void
    {
        echo "\n--- 3. Dừng ngay khi migration lỗi ---\n";
        $this->resetTemporaryTables($db);

        $runner = new MigrationRunner(
            $db,
            ROOT_PATH . '/tests/fixtures/migrations/failure'
        );
        $result = $runner->run();

        $this->assert($result['applied'] === 1, 'Migration đứng trước lỗi được ghi ledger');
        $this->assert($result['failed'] === 1, 'Runner báo đúng một migration lỗi');
        $this->assert($this->probeValue($db, 'failure_before') === 1, 'Migration trước lỗi đã chạy');
        $this->assert($this->probeValue($db, 'failure_after') === 0, 'Migration sau lỗi không được chạy');
        $this->assert($this->ledgerCount($db) === 1, 'Migration lỗi không bị ghi ledger');
    }

    private function testBaselineDoesNotExecuteMigrations(PDO $db): void
    {
        echo "\n--- 4. Baseline không gọi up() ---\n";
        $this->resetTemporaryTables($db);

        $runner = new MigrationRunner(
            $db,
            ROOT_PATH . '/tests/fixtures/migrations/baseline'
        );
        $baseline = $runner->baseline();

        $this->assert($baseline['baselined'] === 1, 'Baseline ghi nhận đúng một migration');
        $this->assert($baseline['failed'] === 0, 'Baseline hoàn tất không lỗi');
        $this->assert($this->probeValue($db, 'baseline_should_not_run') === 0, 'Baseline không gọi migration up()');
        $this->assert($this->ledgerCount($db) === 1, 'Baseline chỉ thêm một ledger record');

        $run = $runner->run();
        $this->assert($run['skipped'] === 1, 'Runner skip migration vừa baseline');
        $this->assert($this->probeValue($db, 'baseline_should_not_run') === 0, 'Migration baseline vẫn không bị chạy');
    }

    private function testPromoMigrationIsIdempotent(PDO $db): void
    {
        echo "\n--- 5. Promo migration deterministic và idempotent ---\n";

        $db->exec('DROP TEMPORARY TABLE IF EXISTS `products`');
        $db->exec(
            "CREATE TEMPORARY TABLE `products` (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY,
                `price` DECIMAL(15,2) NOT NULL,
                `sale_price` DECIMAL(15,2) NULL,
                `status` VARCHAR(30) NOT NULL
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "INSERT INTO `products` (`id`, `price`, `sale_price`, `status`) VALUES
                (1, 100000, 50000, 'active'),
                (2, 200000, 100000, 'active'),
                (3, 300000, 150000, 'inactive'),
                (7, 400000, NULL, 'active')"
        );

        $migrationPath = ROOT_PATH . '/database/migrations/2026_08_01_000001_reset_promo_data.php';
        require_once $migrationPath;
        $migrationClass = 'Migration_2026_08_01_000001_reset_promo_data';

        try {
            $firstResult = $migrationClass::up($db);
            $firstState = $db->query(
                'SELECT `id`, `sale_price` FROM `products` ORDER BY `id`'
            )->fetchAll(PDO::FETCH_ASSOC);

            $secondResult = $migrationClass::up($db);
            $secondState = $db->query(
                'SELECT `id`, `sale_price` FROM `products` ORDER BY `id`'
            )->fetchAll(PDO::FETCH_ASSOC);

            $this->assert($firstResult === true && $secondResult === true, 'Promo up() trả về true ở cả hai lần');
            $this->assert($firstState === $secondState, 'Chạy promo up() lần hai cho kết quả y hệt lần một');
            $this->assert($firstState[0]['sale_price'] !== null, 'Sản phẩm active thuộc tập promo có sale_price');
            $this->assert($firstState[1]['sale_price'] === null, 'Sản phẩm ngoài tập promo có sale_price NULL');
            $this->assert($firstState[2]['sale_price'] === null, 'Sản phẩm inactive không được áp promo');
        } finally {
            $db->exec('DROP TEMPORARY TABLE IF EXISTS `products`');
            $db->exec('DROP TEMPORARY TABLE IF EXISTS `migration_runner_probe`');
            $db->exec('DROP TEMPORARY TABLE IF EXISTS `migrations`');
        }
    }

    private function resetTemporaryTables(PDO $db): void
    {
        $db->exec('DROP TEMPORARY TABLE IF EXISTS `migration_runner_probe`');
        $db->exec('DROP TEMPORARY TABLE IF EXISTS `migrations`');
        $db->exec(
            "CREATE TEMPORARY TABLE `migrations` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `migration` VARCHAR(255) NOT NULL,
                `executed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `migration` (`migration`)
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `migration_runner_probe` (
                `probe` VARCHAR(100) NOT NULL PRIMARY KEY,
                `run_count` INT UNSIGNED NOT NULL DEFAULT 0
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "INSERT INTO `migration_runner_probe` (`probe`) VALUES
                ('success_one'),
                ('success_two'),
                ('failure_before'),
                ('failure_after'),
                ('baseline_should_not_run')"
        );
    }

    private function probeValue(PDO $db, string $probe): int
    {
        $stmt = $db->prepare(
            'SELECT `run_count` FROM `migration_runner_probe` WHERE `probe` = :probe'
        );
        $stmt->execute([':probe' => $probe]);
        return (int)$stmt->fetchColumn();
    }

    private function ledgerCount(PDO $db): int
    {
        return (int)$db->query('SELECT COUNT(*) FROM `migrations`')->fetchColumn();
    }

    private function assert(bool $condition, string $message): void
    {
        if ($condition) {
            $this->passed++;
            echo "[PASS] {$message}\n";
            return;
        }

        $this->failed++;
        $failure = "[FAIL] {$message}";
        $this->errors[] = $failure;
        echo "{$failure}\n";
    }
}

(new MigrationRunnerSafetyTest())->run();
