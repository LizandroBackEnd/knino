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
        // Register Blade aliases for components stored in resources/views/shared
        // so you can use <x-header /> and <x-sidebar /> even though they live
        // in the `shared` folder.
        Blade::component('shared.header', 'header');
        Blade::component('shared.sidebar', 'sidebar');
    
    }
}
