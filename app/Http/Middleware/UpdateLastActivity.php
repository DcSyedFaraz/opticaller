<?php

namespace App\Http\Middleware;

use App\Events\UserStatusChanged;
use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // $user = Auth::user();

        // if ($user) {
        //     $loginTime = Auth::user()->loginTimes()?->whereNull('logout_time')->orderBy('id', 'desc')->first();
        //     if ($loginTime) {
        //         $loginTime->update(['last_activity' => now()]);
        //         broadcast(new UserStatusChanged(Auth::id(), 'online'));
        //     }
        // }
        return $next($request);
    }
}
