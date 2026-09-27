<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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
        Schema::defaultStringLength(191);

        Gate::before(function ($user, string $ability) {
            if ($user->hasRole('superadmin')) {
                return true;
            }

            if ($user->hasRole('admin2')) {
                return preg_match('/(^|[.\/_-])(delete|destroy|eliminar|borrar|vaciar|truncate|remove)([.\/_-]|$)/i', $ability) === 1
                    ? false
                    : true;
            }

            return null;
        });

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        Paginator::useBootstrapFive(); // o useBootstrapFour()
    }
}
