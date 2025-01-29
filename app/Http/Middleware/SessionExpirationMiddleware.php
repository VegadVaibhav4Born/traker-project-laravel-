<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class SessionExpirationMiddleware
{
    public function handle($request, Closure $next)
    {
        $sessionData = Session::all();

        foreach ($sessionData as $key => $value) {
            if (is_array($value) && isset($value['expires_at'])) {
                if (now()->timestamp > $value['expires_at']) {
                    Session::forget($key);
                }
            }
        }

        return $next($request);
    }
}
