<?php

namespace Durrbar\PaymentBkashDriver\Auth;

use Illuminate\Support\Facades\Log;

class BkashAuth
{
    protected $config;
    protected $httpClient;
    protected $token;
    protected $refreshToken;

    public function __construct($config, $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->getToken();
    }

    protected function getToken()
    {
        if ($this->token = cache()->get('bkash_token')) {
            return; // Use cached token
        }

        $response = $this->httpClient->sendRequest('/tokenized/checkout/token/grant', 'POST', [
            'app_key' => $this->config->getAppKey(),
            'app_secret' => $this->config->getAppSecret(),
        ], false, [
            'username' => $this->config->getUsername(),
            'password' => $this->config->getPassword(),
        ]);

        if (!empty($response['id_token'])) {
            $this->token = $response['id_token'];
            $this->refreshToken = $response['refresh_token'];
            cache()->put('bkash_token', $this->token, now()->addMinutes(55));
            cache()->put('bkash_refresh_token', $this->refreshToken, now()->addDays(1));
            Log::info('bKash token granted successfully.');
        } else {
            Log::error('bKash token grant failed', ['response' => $response]);
        }
    }

    protected function refreshToken()
    {
        $refreshToken = cache()->get('bkash_refresh_token');
        if (!$refreshToken) {
            Log::error('No refresh token found, requesting a new token.');
            return $this->getToken(); // Request a new token if refresh token is missing
        }

        $response = $this->httpClient->sendRequest('/tokenized/checkout/token/refresh', 'POST', [
            'app_key' => $this->config->getAppKey(),
            'app_secret' => $this->config->getAppSecret(),
            'refresh_token' => $refreshToken,
        ], false, [
            'username' => $this->config->getUsername(),
            'password' => $this->config->getPassword(),
        ]);

        if (!empty($response['id_token'])) {
            $this->token = $response['id_token'];
            $this->refreshToken = $response['refresh_token'];
            cache()->put('bkash_token', $this->token, now()->addMinutes(55));
            cache()->put('bkash_refresh_token', $this->refreshToken, now()->addDays(1));
            Log::info('bKash token refreshed successfully.');
        } else {
            Log::error('bKash token refresh failed', ['response' => $response]);
        }
    }

    public function getTokenHeader()
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }
}
