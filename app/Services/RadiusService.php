<?php
namespace App\Services;

use App\Models\Radcheck;
use App\Models\Radreply;
use App\Models\RadUserGroup;
use App\Models\IspPackage;
use App\Models\Subscriber;

class RadiusService
{
    /**
     * Provision a subscriber in FreeRADIUS tables.
     * Creates radcheck (password) and radreply (rate-limit, session-timeout) entries.
     */
    public function provisionUser(string $username, string $password, IspPackage $package): void
    {
        // Remove any existing entries
        $this->removeUser($username);

        // Create password entry in radcheck
        Radcheck::create([
            'username'  => $username,
            'attribute' => 'Cleartext-Password',
            'op'        => ':=',
            'value'     => $password,
        ]);

        // Rate limit: upload/download in Mbps format expected by MikroTik
        Radreply::create([
            'username'  => $username,
            'attribute' => 'Mikrotik-Rate-Limit',
            'op'        => '=',
            'value'     => "{$package->speed_upload}M/{$package->speed_download}M",
        ]);

        // Session timeout in seconds
        $sessionTimeout = $package->session_timeout;
        if ($sessionTimeout > 0) {
            Radreply::create([
                'username'  => $username,
                'attribute' => 'Session-Timeout',
                'op'        => '=',
                'value'     => (string) $sessionTimeout,
            ]);
        }

        // Address list for expiry grouping
        Radreply::create([
            'username'  => $username,
            'attribute' => 'Mikrotik-Address-List',
            'op'        => '=',
            'value'     => 'active-users',
        ]);

        // Framed-Protocol for PPPoE
        Radreply::create([
            'username'  => $username,
            'attribute' => 'Framed-Protocol',
            'op'        => '=',
            'value'     => 'PPP',
        ]);
    }

    /**
     * Provision a hotspot voucher (M-Pesa reference as username + password).
     */
    public function provisionHotspotVoucher(string $mpesaRef, IspPackage $package): void
    {
        $this->removeUser($mpesaRef);

        Radcheck::create([
            'username'  => $mpesaRef,
            'attribute' => 'Cleartext-Password',
            'op'        => ':=',
            'value'     => $mpesaRef, // M-Pesa receipt IS the password
        ]);

        Radreply::create([
            'username'  => $mpesaRef,
            'attribute' => 'Mikrotik-Rate-Limit',
            'op'        => '=',
            'value'     => "{$package->speed_upload}M/{$package->speed_download}M",
        ]);

        $sessionTimeout = $package->session_timeout;
        if ($sessionTimeout > 0) {
            Radreply::create([
                'username'  => $mpesaRef,
                'attribute' => 'Session-Timeout',
                'op'        => '=',
                'value'     => (string) $sessionTimeout,
            ]);
        }

        Radreply::create([
            'username'  => $mpesaRef,
            'attribute' => 'Mikrotik-Address-List',
            'op'        => '=',
            'value'     => 'hotspot-active',
        ]);
    }

    /**
     * Suspend a user by moving them to the 'suspended' group.
     */
    public function suspendUser(string $username): void
    {
        // Remove active entries
        Radreply::where('username', $username)->delete();

        // Set simultaneous-use to 0 to block login
        Radcheck::where('username', $username)
            ->where('attribute', 'Simultaneous-Use')
            ->delete();

        Radcheck::create([
            'username'  => $username,
            'attribute' => 'Simultaneous-Use',
            'op'        => ':=',
            'value'     => '0',
        ]);

        // Assign to suspended group
        RadUserGroup::where('username', $username)->delete();
        RadUserGroup::create([
            'username'  => $username,
            'groupname' => 'suspended',
            'priority'  => 1,
        ]);
    }

    /**
     * Remove all RADIUS entries for a user.
     */
    public function removeUser(string $username): void
    {
        Radcheck::where('username', $username)->delete();
        Radreply::where('username', $username)->delete();
        RadUserGroup::where('username', $username)->delete();
    }

    /**
     * Update rate limit for an existing user.
     */
    public function updateRateLimit(string $username, IspPackage $package): void
    {
        Radreply::where('username', $username)
            ->where('attribute', 'Mikrotik-Rate-Limit')
            ->update(['value' => "{$package->speed_upload}M/{$package->speed_download}M"]);
    }

    /**
     * Check if a username exists in radcheck.
     */
    public function userExists(string $username): bool
    {
        return Radcheck::where('username', $username)->exists();
    }
}
