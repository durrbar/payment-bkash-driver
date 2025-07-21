<?php

namespace Durrbar\PaymentBkashDriver\Payment;

use Durrbar\PaymentBkashDriver\Config\BkashConfig;
use Durrbar\PaymentBkashDriver\Http\BkashHttpClient;
use Modules\Payment\Drivers\BasePaymentDriver;

class BkashPayment
{
    protected $config;

    protected $httpClient;

    protected $driver;

    public function __construct(BkashConfig $config, BkashHttpClient $httpClient, BasePaymentDriver $driver)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
        $this->driver = $driver;
    }

    public function initiatePayment(array $payment): array
    {
        if (empty($this->config->getCallbackURL())) {
            return [
                'status' => 'error',
                'message' => 'Callback URL is missing',
            ];
        }

        $payload = [
            'mode' => '0011', // For Checkout (URL based), the value of this parameter should be "0011".
            'payerReference' => $payment['tran_id'],
            'callbackURL' => $this->config->getCallbackURL(),
            'amount' => $payment['amount'],
            'currency' => $payment['currency'] ?? 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => $payment['tran_id'],
        ];

        $response = $this->httpClient->client()->post('/tokenized/checkout/payment/create', $payload)->json();

        $status = isset($response['statusCode']) && $response['statusCode'] === '0000' ? 'success' : 'error';
        $message = $response['statusMessage'] ?? $response['errorMessage'] ?? 'Unknown error';
        $bkashURL = $response['bkashURL'] ?? null;

        return $this->driver->formatInitialPaymentResponse($status, $bkashURL, $message, $response, 'bKash');
    }

    public function verifyPayment(string $transactionId): array
    {
        return $this->httpClient->client()->post("/tokenized/checkout/payment/query/{$transactionId}")->json();
    }
}
