<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckEmployeSession
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('employe')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
