<?php

namespace App\Http\Middleware;

use Closure;

class sellerAppApiMiddleware
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
        //        config(['onesignal.app_id' => env('SELLER_ONESIGNAL_APP_ID')]);
        //        config(['onesignal.rest_api_key' => env('SELLER_ONESIGNAL_REST_API_KEY')]);
        //        config(['onesignal.user_auth_key' => env('SELLER_ONESIGNAL_USER_AUTH_KEY')]);
        return $next($request);
    }
}
