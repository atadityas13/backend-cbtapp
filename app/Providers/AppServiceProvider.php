<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

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
        // Set default string length for legacy MySQL index support
        Schema::defaultStringLength(191);

        // Gate to control who can manage other proctors (only Super Admins)
        Gate::define('manage-proctors', function (User $user) {
            return $user->role === 'admin';
        });
    }
}
