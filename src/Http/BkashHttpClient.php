<?php

namespace Durrbar\PaymentBkashDriver\Http;

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

    public function sendRequest($url, $method, $data = null, $useToken = true, $customHeaders = [], $retried = false)
    {
        $headers = array_merge([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ], $customHeaders);

        if ($useToken) {
            $this->token = cache()->get('bkash_token', null);
            if (!$this->token && !$retried) {
                Log::warning('bKash token missing, attempting refresh.');
                $this->refreshToken();
                return $this->sendRequest($url, $method, $data, $useToken, $customHeaders, true);
            }
            if (!$this->token) {
                Log::error('bKash token retrieval failed. Cannot proceed with request.');
                return ['status' => 'error', 'message' => 'Authentication failed'];
            }
            $headers['Authorization'] = 'Bearer ' . $this->token;
            $headers['X-APP-Key'] = $this->config->getAppKey();
        }

        $response = Http::withHeaders($headers)->{$method}($this->config->getBaseUrl() . $url, $data ?? []);

        if (!$response->successful()) {
            Log::error("bKash API Request Failed", ['url' => $url, 'response' => $response->body()]);
            return [
                'status' => 'error',
                'message' => 'bKash API request failed',
                'error' => $response->body(),
            ];
        }

        return $response->json();
    }

    protected function getToken()
    {
        if ($this->token = cache()->get('bkash_token')) {
            return; // Use cached token
        }

        $response = $this->sendRequest('/tokenized/checkout/token/grant', 'POST', [
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

        $response = $this->sendRequest('/tokenized/checkout/token/refresh', 'POST', [
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
}
