<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // سيصبح ملوناً بعد كتابة السطر في الأسفل

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
        // هذا السطر هو المسؤول عن حل مشكلة "الموقع غير آمن" في الـ Forms
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }
}