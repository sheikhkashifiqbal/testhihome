<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;

class GlobalApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next) {

        $available_devices = ['web', 'android', 'ios'];
        if ($request->header('device-type') != null && in_array($request->header('device_type'), $available_devices)) {
            return $next($request);
        }

        throw new \UnexpectedValueException('device_type key is required in header and must be one of this types (web,android,ios)', Response::HTTP_BAD_REQUEST);

        abort(Response::HTTP_BAD_REQUEST);
    }
}
