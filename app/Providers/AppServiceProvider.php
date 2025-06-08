<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\QuizService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registra il QuizService come singleton
        $this->app->singleton(QuizService::class, function ($app) {
            return new QuizService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}