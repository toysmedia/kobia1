<?php

namespace App\Http\Middleware;

use App\Traits\Install;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotInstalled
{
    use Install;

    /**
     * Handle an incoming request.
     * If the app is already installed, block access to install routes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $status = $this->checkInstall();
        if ($status['status'] === true) {
            abort(404);
        }

        return $next($request);
    }
}
