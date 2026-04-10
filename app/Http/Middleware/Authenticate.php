<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        // Normalize the prefix — strip any leading slash so comparisons are consistent.
        $prefix = ltrim($request->route()->getPrefix() ?? '', '/');

        if ($prefix === 'admin') {
            $route_name = 'admin.login';
        } elseif ($prefix === 'seller') {
            $route_name = 'seller.login';
        } elseif ($prefix === 'customer') {
            $route_name = 'customer.login';
        } elseif (str_starts_with($prefix, 'super-admin')) {
            $route_name = 'super-admin.login';
        } else {
            $route_name = 'login';
        }
        return $request->expectsJson() ? null : route($route_name);
    }
}
