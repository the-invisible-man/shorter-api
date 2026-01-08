<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RedirectLegacyDomains
{
    public function handle(Request $request, Closure $next)
    {
        $primaryHost = config('app.primary_host');
        $legacyHosts = config('app.legacy_hosts', []);

        if (!$primaryHost || empty($legacyHosts)) {
            return $next($request);
        }

        $host = strtolower($request->getHost());

        // If request came to legacy host, redirect to primary host.
        if (in_array($host, array_map('strtolower', $legacyHosts), true) && $host !== strtolower($primaryHost)) {
            $target = 'https://' . $primaryHost . $request->getRequestUri();
            // getRequestUri() includes path + query string.

            return redirect()->to($target, Response::HTTP_MOVED_PERMANENTLY)
                ->header('Cache-Control', 'public, max-age=31536000') // optional but common for 301
                ->header('X-Robots-Tag', 'noindex'); // optional: prevents indexing the legacy domain
        }

        return $next($request);
    }
}
