<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyMpesaIp
{
    /**
     * Safaricom production IP whitelist.
     *
     * @var array<int, string>
     */
    protected array $safaricomIps = [
        '196.201.214.200',
        '196.201.214.206',
        '196.201.213.114',
        '196.201.214.207',
        '196.201.214.208',
        '196.201.213.44',
        '196.201.212.127',
        '196.201.212.128',
        '196.201.212.129',
        '196.201.212.132',
        '196.201.212.136',
        '196.201.212.138',
    ];

    /**
     * Handle an incoming request.
     * Blocks requests that do not originate from a known Safaricom IP address.
     *
     * Set MPESA_VERIFY_IP=false in .env to disable verification (e.g., sandbox/local testing).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('mpesa.verify_ip', true)) {
            return $next($request);
        }

        $clientIp = $request->ip();

        if (!in_array($clientIp, $this->safaricomIps, true)) {
            Log::warning('VerifyMpesaIp: Request from unauthorized IP blocked.', [
                'ip'  => $clientIp,
                'url' => $request->fullUrl(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden',
            ], 403);
        }

        return $next($request);
    }
}
