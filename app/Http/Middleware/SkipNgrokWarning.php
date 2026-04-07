<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SkipNgrokWarning
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Process the request and get the response
        $response = $next($request);

        // 2. Add the specific header that tells ngrok to skip the warning page
        // The value 'any-value' can literally be anything.
        $response->headers->set('ngrok-skip-browser-warning', 'true');

        return $response;
    }
}