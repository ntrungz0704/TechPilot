<?php

class Migration_2099_01_03_000001_baseline_only
{
    public static function up(PDO $db): bool
    {
        $db->exec(
            "UPDATE `migration_runner_probe`
             SET `run_count` = `run_count` + 1
             WHERE `probe` = 'baseline_should_not_run'"
        );
        return true;
    }
}
