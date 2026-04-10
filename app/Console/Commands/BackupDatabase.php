<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature   = 'app:backup-database';
    protected $description = 'Create a gzip-compressed MySQL database backup in storage/app/backups/';

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup-' . now()->format('Y-m-d-His') . '.sql.gz';
        $filepath = $backupDir . '/' . $filename;

        $db       = config('database.connections.mysql');
        $host     = escapeshellarg($db['host']);
        $port     = (int) ($db['port'] ?? 3306);
        $database = escapeshellarg($db['database']);
        $username = escapeshellarg($db['username']);
        $password = $db['password'];

        $envVar  = 'MYSQL_PWD=' . escapeshellarg($password);
        $command = "{$envVar} mysqldump -h {$host} -P {$port} -u {$username} {$database} | gzip > " . escapeshellarg($filepath);

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error('Backup failed. Exit code: ' . $returnCode);
            if (!empty($output)) {
                $this->error(implode("\n", $output));
            }
            return 1;
        }

        $this->info("Backup saved: {$filepath}");

        // Keep only the last 7 days of backups
        $files = glob($backupDir . '/backup-*.sql.gz');
        if ($files && count($files) > 7) {
            usort($files, fn($a, $b) => filemtime($a) - filemtime($b));
            foreach (array_slice($files, 0, count($files) - 7) as $old) {
                unlink($old);
                $this->line("Removed old backup: {$old}");
            }
        }

        return 0;
    }
}
