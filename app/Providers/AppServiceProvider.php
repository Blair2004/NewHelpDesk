<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\AuthorizationManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('authorize', function () {
            return new AuthorizationManager;
        });
    }

    public function boot(): void
    {
        //
    }
}
