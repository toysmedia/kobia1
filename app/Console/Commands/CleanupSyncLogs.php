<?php

namespace App\Console\Commands;

use App\Models\RouterSyncLog;
use Illuminate\Console\Command;

/**
 * Remove router sync log entries older than the configured retention period.
 *
 * With 1-second sync cadence, a single router generates 86,400 rows per day.
 * This command keeps the table size manageable by pruning old snapshots.
 */
class CleanupSyncLogs extends Command
{
    protected $signature   = 'app:cleanup-sync-logs {--hours=24 : Retention period in hours}';
    protected $description = 'Prune router_sync_logs entries older than the retention period';

    public function handle(): int
    {
        $hours   = (int) $this->option('hours');
        $cutoff  = now()->subHours($hours);

        $deleted = RouterSyncLog::where('synced_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} sync log row(s) older than {$hours} hour(s).");

        return 0;
    }
}