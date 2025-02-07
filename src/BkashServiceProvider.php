<?php

namespace Durrbar\PaymentBkashDriver;

use Illuminate\Support\ServiceProvider;

class BkashServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/bkash.php', 'payment.providers.bkash');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/bkash.php' => config_path('bkash.php'),
        ], 'bkash-config');
    }
}
