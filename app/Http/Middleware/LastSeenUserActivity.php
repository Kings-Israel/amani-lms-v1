<?php

namespace App\Http\Middleware;

use App\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LastSeenUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('web')->check()) {
            $expireTime = Carbon::now()->addMinutes(120); // keep online for 5 min

            Cache::put('is_online'.Auth::id(), true, $expireTime);

            User::find(Auth::id())->update(['last_seen' => Carbon::now()]);
        }
        return $next($request);
    }
}
