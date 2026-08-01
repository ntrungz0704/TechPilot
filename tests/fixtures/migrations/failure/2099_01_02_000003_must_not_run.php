<?php

class Migration_2099_01_02_000003_must_not_run
{
    public static function up(PDO $db): bool
    {
        $db->exec(
            "UPDATE `migration_runner_probe`
             SET `run_count` = `run_count` + 1
             WHERE `probe` = 'failure_after'"
        );
        return true;
    }
}
