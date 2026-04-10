<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $admin = auth('admin')->user();

        if (!$admin) {
            abort(403, 'Unauthenticated.');
        }

        // Super admin always has full access
        if ($admin->hasRole('super_admin')) {
            return $next($request);
        }

        if (!$admin->hasPermission($permission)) {
            abort(403, "You do not have permission: {$permission}");
        }

        return $next($request);
    }
}
