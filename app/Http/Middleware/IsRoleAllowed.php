<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsRoleAllowed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->user()->role == 7 || auth()->user()->role == 9) {

        } else {
            abort('403');
        }
        return $next($request);
    }
}
