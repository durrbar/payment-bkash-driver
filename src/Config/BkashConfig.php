<?php

declare(strict_types=1);

namespace Durrbar\PaymentBkashDriver\Config;

final class BkashConfig
{
    private $sandbox;

    private $appKey;

    private $appSecret;

    private $username;

    private $password;

    private $callbackURL;

    private $baseUrl;

    public function __construct()
    {
        $this->sandbox = config('payment.providers.bkash.sandbox', true);
        $this->appKey = config('payment.providers.bkash.app_key');
        $this->appSecret = config('payment.providers.bkash.app_secret');
        $this->username = config('payment.providers.bkash.username');
        $this->password = config('payment.providers.bkash.password');
        $this->callbackURL = config('payment.providers.bkash.callbackURL');
        $this->setBaseUrl();
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    public function getAppKey()
    {
        return $this->appKey;
    }

    public function getAppSecret()
    {
        return $this->appSecret;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getCallbackURL()
    {
        return $this->callbackURL;
    }

    private function setBaseUrl()
    {
        $this->baseUrl = $this->sandbox
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'
            : 'https://tokenized.pay.bka.sh/v1.2.0-beta';
    }
}
