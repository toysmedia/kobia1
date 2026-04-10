<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DailyBackup extends Command
{
    protected $signature   = 'app:daily-backup';
    protected $description = 'Create a MySQL database backup in storage/app/backups/';

    public function handle(): int
    {
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filename = 'backup-' . now()->format('Y-m-d-His') . '.sql';
        $filepath = $backupDir . '/' . $filename;

        $db       = config('database.connections.mysql');
        $host     = escapeshellarg($db['host']);
        $port     = (int) ($db['port'] ?? 3306);
        $database = escapeshellarg($db['database']);
        $username = escapeshellarg($db['username']);
        $password = $db['password'];

        $envVar  = 'MYSQL_PWD=' . escapeshellarg($password);
        $command = "{$envVar} mysqldump -h {$host} -P {$port} -u {$username} {$database} > " . escapeshellarg($filepath);

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error('Backup failed. Exit code: ' . $returnCode);
            return 1;
        }

        $this->info("Backup saved: {$filepath}");

        // Keep only last 7 backups
        $files = glob($backupDir . '/backup-*.sql');
        if (count($files) > 7) {
            usort($files, fn($a, $b) => filemtime($a) - filemtime($b));
            foreach (array_slice($files, 0, count($files) - 7) as $old) {
                unlink($old);
                $this->line("Removed old backup: {$old}");
            }
        }

        return 0;
    }
}
