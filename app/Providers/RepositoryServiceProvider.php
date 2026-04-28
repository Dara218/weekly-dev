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
        $this->app->bind(
            \App\Interfaces\ParentInterface::class,
            \App\Repositories\ParentRepository::class,
        );
        $this->app->bind(
            \App\Interfaces\AcademicYearInterface::class,
            \App\Repositories\AcademicYearRepository::class,
        );
        $this->app->bind(
            \App\Interfaces\ClassesInterface::class,
            \App\Repositories\ClassesRepository::class,
        );
        $this->app->bind(
            \App\Interfaces\TeacherClassAssignmentInterface::class,
            \App\Repositories\TeacherClassAssignmentRepository::class,
        );
        $this->app->bind(
            \App\Interfaces\UserFileInterface::class,
            \App\Repositories\UserFileRepository::class,
        );
    }
}
