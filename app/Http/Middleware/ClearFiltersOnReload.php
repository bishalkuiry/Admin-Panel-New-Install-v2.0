<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClearFiltersOnReload
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if it's a GET request with query parameters (filters)
        if ($request->isMethod('GET') && !empty($request->query())) {
            
            // Check for explicit "refresh" headers sent by browsers on F5/Reload
            // 'Cache-Control' => 'max-age=0' is the standard for F5
            $cacheControl = $request->header('Cache-Control');
            
            if ($cacheControl === 'max-age=0') {
                 // It is a reload, redirect to the clean URL (without query params)
                 return redirect()->to($request->url());
            }
        }

        return $next($request);
    }
}
