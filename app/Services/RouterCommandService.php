<?php

namespace App\Services;

use App\Models\PendingRouterCommand;
use App\Models\Router;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Log;

/**
 * Service responsible for queueing commands to MikroTik routers and
 * attempting immediate delivery via the REST API.
 *
 * Usage pattern:
 *   1. Try REST API push immediately.
 *   2. If the router is offline or the push fails, queue the command in
 *      `pending_router_commands` for the router to pick up on next sync.
 */
class RouterCommandService
{
    public function __construct(protected MikrotikApiService $api) {}

    // ── Subscriber provisioning ───────────────────────────────────────────────

    /**
     * Push a new PPPoE secret to the router; fall back to queue on failure.
     */
    public function provisionPppSecret(Subscriber $subscriber, string $plainPassword): void
    {
        $router = $this->resolveRouter($subscriber);
        if (!$router) {
            return;
        }

        $profile = $subscriber->package->pppoe_profile ?? 'pppoe-profile';

        try {
            $this->api->init($router);
            $this->api->addPppSecret($subscriber->username, $plainPassword, $profile);
        } catch (\Exception $e) {
            Log::warning('RouterCommandService: REST push failed, queueing add_ppp_secret', [
                'subscriber' => $subscriber->username,
                'error'      => $e->getMessage(),
            ]);
            $this->queue($router, 'add_ppp_secret', [
                'username' => $subscriber->username,
                'password' => $plainPassword,
                'profile'  => $profile,
            ]);
        }
    }

    /**
     * Push a PPPoE secret update (e.g. password change) or queue it.
     */
    public function updatePppSecret(Subscriber $subscriber, string $plainPassword): void
    {
        $router = $this->resolveRouter($subscriber);
        if (!$router) {
            return;
        }

        $profile = $subscriber->package->pppoe_profile ?? 'pppoe-profile';

        try {
            $this->api->init($router);
            // Remove and re-add is the safest update strategy
            $this->api->removePppSecret($subscriber->username);
            $this->api->addPppSecret($subscriber->username, $plainPassword, $profile);
        } catch (\Exception $e) {
            Log::warning('RouterCommandService: REST push failed, queueing update for ppp_secret', [
                'subscriber' => $subscriber->username,
                'error'      => $e->getMessage(),
            ]);
            $this->queue($router, 'add_ppp_secret', [
                'username' => $subscriber->username,
                'password' => $plainPassword,
                'profile'  => $profile,
            ]);
        }
    }

    /**
     * Remove PPPoE secret and disconnect session; fall back to queue on failure.
     */
    public function removePppSecret(Subscriber $subscriber): void
    {
        $router = $this->resolveRouter($subscriber);
        if (!$router) {
            return;
        }

        try {
            $this->api->init($router);
            $this->api->disconnectPppSession($subscriber->username);
            $this->api->removePppSecret($subscriber->username);
        } catch (\Exception $e) {
            Log::warning('RouterCommandService: REST push failed, queueing remove_ppp_secret', [
                'subscriber' => $subscriber->username,
                'error'      => $e->getMessage(),
            ]);
            $this->queue($router, 'disconnect_ppp', ['username' => $subscriber->username]);
            $this->queue($router, 'remove_ppp_secret', ['username' => $subscriber->username]);
        }
    }

    /**
     * Push a new Hotspot user to the router; fall back to queue on failure.
     */
    public function provisionHotspotUser(Subscriber $subscriber, string $plainPassword): void
    {
        $router = $this->resolveRouter($subscriber);
        if (!$router) {
            return;
        }

        $profile = $subscriber->package->hotspot_profile ?? 'default';

        try {
            $this->api->init($router);
            $this->api->addHotspotUser($subscriber->username, $plainPassword, $profile);
        } catch (\Exception $e) {
            Log::warning('RouterCommandService: REST push failed, queueing add_hotspot_user', [
                'subscriber' => $subscriber->username,
                'error'      => $e->getMessage(),
            ]);
            $this->queue($router, 'add_hotspot_user', [
                'username' => $subscriber->username,
                'password' => $plainPassword,
                'profile'  => $profile,
            ]);
        }
    }

    /**
     * Remove Hotspot user; fall back to queue on failure.
     */
    public function removeHotspotUser(Subscriber $subscriber): void
    {
        $router = $this->resolveRouter($subscriber);
        if (!$router) {
            return;
        }

        try {
            $this->api->init($router);
            $this->api->disconnectHotspotSession($subscriber->username);
            $this->api->removeHotspotUser($subscriber->username);
        } catch (\Exception $e) {
            Log::warning('RouterCommandService: REST push failed, queueing remove_hotspot_user', [
                'subscriber' => $subscriber->username,
                'error'      => $e->getMessage(),
            ]);
            $this->queue($router, 'disconnect_hotspot', ['username' => $subscriber->username]);
            $this->queue($router, 'remove_hotspot_user', ['username' => $subscriber->username]);
        }
    }

    // ── Expiry ────────────────────────────────────────────────────────────────

    /**
     * Disconnect an expired subscriber and add them to the `expired` address list.
     */
    public function expireSubscriber(Subscriber $subscriber): void
    {
        $router = $this->resolveRouter($subscriber);
        if (!$router) {
            return;
        }

        $type    = $subscriber->connection_type ?? 'pppoe';
        $address = $subscriber->ip_address ?? '';

        try {
            $this->api->init($router);

            if ($type === 'hotspot') {
                $this->api->disconnectHotspotSession($subscriber->username);
            } else {
                $this->api->disconnectPppSession($subscriber->username);
            }

            if ($address) {
                $this->api->updateAddressList('expired', $address, "Expired: {$subscriber->username}");
            }
        } catch (\Exception $e) {
            Log::warning('RouterCommandService: REST push failed, queueing expiry commands', [
                'subscriber' => $subscriber->username,
                'error'      => $e->getMessage(),
            ]);

            if ($type === 'hotspot') {
                $this->queue($router, 'disconnect_hotspot', ['username' => $subscriber->username]);
            } else {
                $this->queue($router, 'disconnect_ppp', ['username' => $subscriber->username]);
            }

            if ($address) {
                $this->queue($router, 'update_address_list', [
                    'list'    => 'expired',
                    'address' => $address,
                    'comment' => "Expired: {$subscriber->username}",
                ]);
            }
        }
    }

    // ── Low-level queue helper ────────────────────────────────────────────────

    /**
     * Enqueue a command for the router to execute on next sync.
     */
    public function queue(Router $router, string $command, array $payload = []): PendingRouterCommand
    {
        return PendingRouterCommand::create([
            'router_id' => $router->id,
            'command'   => $command,
            'payload'   => $payload,
            'status'    => 'pending',
        ]);
    }

    /**
     * Execute a single router command immediately via the given API service.
     *
     * This is the central dispatch map shared by RouterCommandService and
     * ProcessPendingRouterCommands to avoid duplication.
     *
     * @throws \UnhandledMatchError when the command is not recognized
     */
    public static function dispatch(MikrotikApiService $api, string $command, array $payload): void
    {
        match ($command) {
            'add_ppp_secret'      => $api->addPppSecret(
                                         $payload['username'] ?? '',
                                         $payload['password'] ?? '',
                                         $payload['profile'] ?? 'pppoe-profile',
                                         $payload['remote_address'] ?? ''
                                     ),
            'remove_ppp_secret'   => $api->removePppSecret($payload['username'] ?? ''),
            'disconnect_ppp'      => $api->disconnectPppSession($payload['username'] ?? ''),
            'add_hotspot_user'    => $api->addHotspotUser(
                                         $payload['username'] ?? '',
                                         $payload['password'] ?? '',
                                         $payload['profile'] ?? 'default',
                                         $payload['server'] ?? 'all'
                                     ),
            'remove_hotspot_user' => $api->removeHotspotUser($payload['username'] ?? ''),
            'disconnect_hotspot'  => $api->disconnectHotspotSession($payload['username'] ?? ''),
            'update_address_list' => $api->updateAddressList(
                                         $payload['list'] ?? 'expired',
                                         $payload['address'] ?? '',
                                         $payload['comment'] ?? '',
                                         $payload['timeout'] ?? ''
                                     ),
            'remove_address_list' => $api->removeAddressListEntry(
                                         $payload['list'] ?? 'expired',
                                         $payload['address'] ?? ''
                                     ),
            default               => null,
        };
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function resolveRouter(Subscriber $subscriber): ?Router
    {
        if (!$subscriber->router_id) {
            return null;
        }
        // Return already-loaded relation if available, otherwise query
        if ($subscriber->relationLoaded('router')) {
            return $subscriber->router;
        }
        return Router::find($subscriber->router_id);
    }
}