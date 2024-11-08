<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\SitterProfile;
use App\Policies\SitterProfilePolicy;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bestaande Vite configuratie
        Vite::prefetch(concurrency: 3);

        // Bestaande Gate policy
        Gate::policy(SitterProfile::class, SitterProfilePolicy::class);

        // Nieuwe Inertia share configuratie
        Inertia::share([
            'auth' => fn () => [
                'user' => auth()->user() ? [
                    'id' => auth()->user()->id,
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'is_admin' => auth()->user()->is_admin,
                ] : null,
            ],
            'flash' => [
                'message' => fn () => session('message'),
                'error' => fn () => session('error'),
                'success' => fn () => session('success'),
            ],
        ]);
    }
}