<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventSessionForApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Force Laravel to think usage of session is disabled
        // This prevents 'Set-Cookie' headers for session/xsrf
        config(['session.driver' => 'array']); 
        
        $response = $next($request);
        
        // Double check and strip cookies if they snuck in
        $response->headers->remove('Set-Cookie');

        return $response;
    }
}
