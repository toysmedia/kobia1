<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Reseller;

class ResellerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->guard('web')->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login.');
        }

        $reseller = Reseller::where('user_id', $user->id)->where('is_active', true)->first();
        if (!$reseller) {
            abort(403, 'Reseller access required.');
        }

        return $next($request);
    }
}
