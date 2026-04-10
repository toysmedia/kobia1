<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommunityVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('community')->user();
        if ($user && !$user->email_verified_at) {
            return redirect()->route('community.verification.notice')
                ->with('warning', 'Please verify your email address.');
        }
        return $next($request);
    }
}
