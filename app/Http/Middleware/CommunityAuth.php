<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommunityAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('community')->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('community.login')->with('error', 'Please log in to continue.');
        }
        return $next($request);
    }
}
