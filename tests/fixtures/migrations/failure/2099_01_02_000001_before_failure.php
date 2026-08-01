<?php

class Migration_2099_01_02_000001_before_failure
{
    public static function up(PDO $db): bool
    {
        $db->exec(
            "UPDATE `migration_runner_probe`
             SET `run_count` = `run_count` + 1
             WHERE `probe` = 'failure_before'"
        );
        return true;
    }
}
