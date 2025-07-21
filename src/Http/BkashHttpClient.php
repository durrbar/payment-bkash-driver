<?php

namespace Durrbar\PaymentBkashDriver\Http;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BkashHttpClient
{
    protected $config;

    protected $token;

    protected $refreshToken;

    public function __construct($config)
    {
        $this->config = $config;
        $this->getToken();
    }

    /**
     * Initialize the HTTP client with base configuration.
     */
    public function client(): PendingRequest
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->token,
            'X-APP-Key' => $this->config->getAppKey(),
        ])
            ->baseUrl($this->config->getBaseUrl())
            ->timeout(60);
    }

    /**
     * Retrieve or refresh the bKash token.
     */
    protected function getToken()
    {
        if ($this->token = cache()->get('bkash_token')) {
            return; // Use cached token
        }

        $response = $this->client()->post('/tokenized/checkout/token/grant', [
            'app_key' => $this->config->getAppKey(),
            'app_secret' => $this->config->getAppSecret(),
        ], [
            'username' => $this->config->getUsername(),
            'password' => $this->config->getPassword(),
        ])->json();

        if (! empty($response['id_token'])) {
            $this->token = $response['id_token'];
            $this->refreshToken = $response['refresh_token'];
            cache()->put('bkash_token', $this->token, now()->addMinutes(55));
            cache()->put('bkash_refresh_token', $this->refreshToken, now()->addDays(1));
            Log::info('bKash token granted successfully.');
        } else {
            Log::error('bKash token grant failed', ['response' => $response]);
        }
    }

    /**
     * Refresh the bKash token.
     */
    protected function refreshToken()
    {
        $refreshToken = cache()->get('bkash_refresh_token');
        if (! $refreshToken) {
            Log::error('No refresh token found, requesting a new token.');

            return $this->getToken(); // Request a new token if refresh token is missing
        }

        $response = $this->client()->post('/tokenized/checkout/token/refresh', [
            'app_key' => $this->config->getAppKey(),
            'app_secret' => $this->config->getAppSecret(),
            'refresh_token' => $refreshToken,
        ], [
            'username' => $this->config->getUsername(),
            'password' => $this->config->getPassword(),
        ])->json();

        if (! empty($response['id_token'])) {
            $this->token = $response['id_token'];
            $this->refreshToken = $response['refresh_token'];
            cache()->put('bkash_token', $this->token, now()->addMinutes(55));
            cache()->put('bkash_refresh_token', $this->refreshToken, now()->addDays(1));
            Log::info('bKash token refreshed successfully.');
        } else {
            Log::error('bKash token refresh failed', ['response' => $response]);
        }
    }
}
