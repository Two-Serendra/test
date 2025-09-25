<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

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

        Blade::if('role', function ($roleId) {
            return auth()->check() && auth()->user()->role_id == $roleId;
        });

        Blade::if('roles', function (...$roleIds) {
            return auth()->check() && in_array(auth()->user()->role_id, $roleIds);
        });
    }
}
