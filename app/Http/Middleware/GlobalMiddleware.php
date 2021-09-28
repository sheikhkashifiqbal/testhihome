<?php

namespace App\Http\Middleware;

use Closure;

class GlobalMiddleware
{
    /**
     * Handle an incoming request for any global requirements
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next) {
        return $next($request);
    }
}
