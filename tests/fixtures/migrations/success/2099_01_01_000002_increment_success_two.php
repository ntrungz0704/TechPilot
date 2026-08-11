<?php

class Migration_2099_01_01_000002_increment_success_two
{
    public static function up(PDO $db): bool
    {
        $db->exec(
            "UPDATE `migration_runner_probe`
             SET `run_count` = `run_count` + 1
             WHERE `probe` = 'success_two'"
        );
        return true;
    }
}
