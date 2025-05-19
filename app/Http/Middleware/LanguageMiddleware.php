<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get language from header or default to English
        $locale = $request->header('Accept-Language', 'en');
        
        // Only allow supported languages
        if (!in_array($locale, ['en', 'es'])) {
            $locale = 'en';
        }
        
        // Set application locale
        App::setLocale($locale);
        
        return $next($request);
    }
}
