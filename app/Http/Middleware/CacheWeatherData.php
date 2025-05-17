<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheWeatherData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply caching for GET requests to the weather endpoint
        if ($request->isMethod('GET') && $request->is('api/weather') && $request->has('city')) {
            $city = $request->input('city');
            $user = $request->user();
            $cacheKey = "weather_data_{$city}_{$user->id}";
            
            // Return cached response if it exists
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }
            
            $response = $next($request);
            
            // Only cache successful responses
            if ($response instanceof JsonResponse && $response->getStatusCode() === 200) {
                // Cache the response for 30 minutes
                Cache::put($cacheKey, $response, 60 * 30);
            }
            
            return $response;
        }
        
        return $next($request);
    }
}
