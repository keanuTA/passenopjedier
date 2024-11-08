<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return to_route('login');
        }

        // Debug logging toevoegen
        \Log::info('IsAdmin middleware check', [
            'user_id' => auth()->id(),
            'is_admin' => auth()->user()->is_admin,
            'isAdmin()' => auth()->user()->isAdmin()
        ]);

        // We kunnen hier beide methodes gebruiken, maar isAdmin() is explicieter
        if (!auth()->user()->isAdmin()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Toegang geweigerd. Alleen voor administrators.'], 403);
            }

            return redirect()->route('dashboard')->with('error', 'Toegang geweigerd. Alleen voor administrators.');
        }

        return $next($request);
    }
}