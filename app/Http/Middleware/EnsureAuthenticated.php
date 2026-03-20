<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->get('authenticated', false)) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
