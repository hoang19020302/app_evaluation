<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Cache;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        
        'api/*',
        'check-info'
    ];

    /**
     * Determine if the session and input CSRF tokens match.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        $cachedToken = Cache::get('csrf_token');

        // If no token is stored in cache or the token in cache does not match the token in the request, return false
        if ($cachedToken === null || !$cachedToken || $cachedToken !== $request->input('_token')) {
            return false;
        }

        // Token is valid
        return true;
    }
}
