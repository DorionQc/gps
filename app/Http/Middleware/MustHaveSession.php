<?php

namespace App\Http\Middleware;

use Closure;

class MustHaveSession
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
        if ($request->session()->has('sessionId')) {
            return $next($request);
        }
        return redirect('/truck/pickSession');
    }
}
