<?php

namespace App\Providers;

use App\Models\Fournisseur;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        RateLimiter::for('public', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            $key = $request->user()?->getAuthIdentifier() ?: $request->ip();
            return Limit::perMinute(100)->by((string) $key);
        });

        View::composer(['store.*', 'auth.client-login', 'auth.client-register'], function ($view): void {
            $view->with([
                'distributors' => Fournisseur::query()
                    ->where('actif', 1)
                    ->orderBy('nom_frs')
                    ->get(['id', 'nom_frs', 'ville', 'adresse']),
                'selectedFrsId' => (int) session('selected_frs_id', 0),
            ]);
        });
    }
}
