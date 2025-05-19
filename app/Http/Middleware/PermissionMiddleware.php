<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $permission
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (Auth::guest()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        if (!$request->user()->can($permission)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
