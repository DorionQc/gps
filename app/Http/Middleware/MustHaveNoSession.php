<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Closure;

class MustHaveNoSession
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
            return redirect('/truck/session');
        }
        $sessions = DB::select("select id from sessions where driverId=? and end is null", [Auth::user()->id]);
        if (count($sessions) > 0) {
            $request->session()->put('sessionId', $sessions[0]->id);
            return redirect('/truck/session');
        }
        return $next($request);
    }
}
