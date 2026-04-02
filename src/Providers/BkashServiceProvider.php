<?php

declare(strict_types=1);

namespace Durrbar\PaymentBkashDriver\Providers;

use Illuminate\Support\ServiceProvider;

final class BkashServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/bkash.php', 'payment.providers.bkash');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/bkash.php' => config_path('bkash.php'),
        ], 'bkash-config');
    }
}
