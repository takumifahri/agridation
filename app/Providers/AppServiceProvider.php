<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
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
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
        // Set Guzzle HTTP client options globally if needed
        config(['guzzle.defaults' => [
            'verify' => env('CURL_CA_BUNDLE', true),
        ]]);
        // if (app()->environment('local')) {
        //     URL::forceScheme('https');
        // }
    }
}
