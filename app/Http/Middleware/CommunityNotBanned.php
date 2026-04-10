<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommunityNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('community')->user();
        if ($user && $user->is_banned) {
            auth('community')->logout();
            return redirect()->route('community.login')
                ->with('error', 'Your account has been banned. Reason: ' . ($user->ban_reason ?? 'Violation of community guidelines.'));
        }
        return $next($request);
    }
}
