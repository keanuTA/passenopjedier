<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return to_route('login');
        }

        if (auth()->user()->is_admin !== 1) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Toegang geweigerd. Alleen voor administrators.'], 403);
            }

            return to_route('dashboard')->with('error', 'Toegang geweigerd. Alleen voor administrators.');
        }

        return $next($request);
    }
}