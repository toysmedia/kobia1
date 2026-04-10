<?php
namespace App\Console\Commands;

use App\Models\Router;
use App\Models\Nas;
use Illuminate\Console\Command;

class SyncNas extends Command
{
    protected $signature   = 'app:sync-nas';
    protected $description = 'Sync the NAS table from the routers table for FreeRADIUS';

    public function handle(): int
    {
        $routers = Router::where('is_active', true)->get();

        if ($routers->isEmpty()) {
            $this->info('No active routers found.');
            return 0;
        }

        $count = 0;
        foreach ($routers as $router) {
            $nasIp = $router->vpn_ip ?? $router->wan_ip;
            if (!$nasIp) {
                $this->warn("Skipping router '{$router->name}' (ID: {$router->id}): no IP address configured.");
                continue;
            }
            Nas::updateOrCreate(
                ['nasname' => $nasIp],
                [
                    'shortname'   => $router->name,
                    'type'        => 'other',
                    'secret'      => $router->radius_secret,
                    'description' => $router->name . ' - MikroTik',
                ]
            );
            $count++;
            $this->line("Synced NAS: {$nasIp} ({$router->name})");
        }

        $this->info("Synced {$count} NAS entries.");
        return 0;
    }
}