<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CacheHeaders Middleware
 * Sets appropriate Cache-Control headers for static assets.
 */
class CacheHeaders
{
    /**
     * File extension → max-age (seconds) mapping.
     */
    protected array $cacheDurations = [
        // Immutable versioned assets (Vite fingerprinted) — 1 year
        'js'    => 31536000,
        'css'   => 31536000,
        // Images — 30 days
        'png'   => 2592000,
        'jpg'   => 2592000,
        'jpeg'  => 2592000,
        'gif'   => 2592000,
        'webp'  => 2592000,
        'svg'   => 2592000,
        'ico'   => 2592000,
        // Fonts — 1 year
        'woff'  => 31536000,
        'woff2' => 31536000,
        'ttf'   => 31536000,
        'eot'   => 31536000,
    ];

    /**
     * Paths that must never be cached (e.g. service worker).
     */
    protected array $noCachePaths = [
        '/sw.js',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $path = $request->getPathInfo();
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        // Service worker and other paths that must not be cached
        if (in_array($path, $this->noCachePaths)) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
            return $response;
        }

        if (isset($this->cacheDurations[$ext])) {
            $maxAge = $this->cacheDurations[$ext];

            if ($maxAge >= 31536000) {
                // Long-lived assets: immutable
                $response->headers->set('Cache-Control', "public, max-age={$maxAge}, immutable");
            } else {
                // Medium-lived assets
                $response->headers->set('Cache-Control', "public, max-age={$maxAge}");
            }
        }

        return $response;
    }
}
