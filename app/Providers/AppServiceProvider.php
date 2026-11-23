<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
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
        Paginator::useBootstrapFive();

        // Tự động cấu hình HTTPS và Dynamic Asset URL khi truy cập qua Cloudflare Tunnel (trycloudflare.com), ngrok, localtunnel
        if (
            ! $this->app->runningInConsole() &&
            (
                request()->server('HTTP_X_FORWARDED_PROTO') === 'https' ||
                request()->header('X-Forwarded-Proto') === 'https' ||
                request()->isSecure() ||
                str_contains(request()->getHttpHost() ?? '', 'trycloudflare.com') ||
                str_contains(request()->getHttpHost() ?? '', 'ngrok') ||
                str_contains(request()->getHttpHost() ?? '', 'loca.lt')
            )
        ) {
            URL::forceScheme('https');
        }
    }
}
