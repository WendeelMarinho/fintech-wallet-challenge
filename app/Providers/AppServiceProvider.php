<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        if (config('app.env') !== 'production') {
            return;
        }

        URL::forceScheme('https');

        $appUrl = config('app.url');
        if (is_string($appUrl) && $appUrl !== '') {
            if (str_starts_with($appUrl, 'http://')) {
                $appUrl = 'https://'.substr($appUrl, 7);
            }
            URL::forceRootUrl($appUrl);
        }

        $assetUrl = config('app.asset_url');
        if (is_string($assetUrl) && $assetUrl !== '') {
            if (str_starts_with($assetUrl, 'http://')) {
                $assetUrl = 'https://'.substr($assetUrl, 7);
            }
            URL::useAssetOrigin($assetUrl);
        }
    }
}
