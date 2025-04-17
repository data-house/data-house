<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StripImageTransformCookies
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Let the request run all the way through the pipeline.
        $response = $next($request);
 
        // Only apply to our transform route.
        if ($request->routeIs('avatar')) {
            // Strip cookies
            $response->headers->remove('Set-Cookie');
        }
 
        return $response;
    }
}
