<?php

namespace Tests\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Tests\Services\ActionPage;

class TestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerTestRoutes();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/');
    }

    public function registerTestRoutes(): void
    {
        if (!$this->app->environment('testing')) {
            return;
        }

        Route::get('/admin/pages/action-page', ActionPage::class)->name('filament.admin.pages.action-page');
    }
}
