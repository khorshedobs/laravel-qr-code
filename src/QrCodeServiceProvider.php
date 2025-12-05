<?php

namespace RSSoftBD\QrCode;

use Illuminate\Support\ServiceProvider;
use RSSoftBD\QrCode\QrGenerator;

class QrCodeServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('qr-code', function () {
            return new QrGenerator();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['qrcode'];
    }
}
