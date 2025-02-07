<?php

return [
    'driver' => \Durrbar\PaymentBkashDriver\PaymentBkashDriver::class,
    'sandbox' => env('BKASH_SANDBOX', true), // true for testing
    'app_key' => env('BKASH_APP_KEY'),
    'app_secret' => env('BKASH_APP_SECRET'),
    'username' => env('BKASH_USERNAME'),
    'password' => env('BKASH_PASSWORD'),
    'callbackURL' => env('BKASH_CALLBACK_URL'),
    'timezone' => 'Asia/Dhaka',
];
