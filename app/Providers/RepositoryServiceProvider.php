<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->bind(
            \App\Interfaces\UserInterface::class,
            \App\Repositories\UserRepository::class,
        );
        $this->app->bind(
            \App\Interfaces\StudentInterface::class,
            \App\Repositories\StudentRepository::class,
        );
    }
}
