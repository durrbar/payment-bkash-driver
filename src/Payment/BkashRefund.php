<?php

namespace Durrbar\PaymentBkashDriver\Payment;

use Durrbar\PaymentBkashDriver\Config\BkashConfig;
use Durrbar\PaymentBkashDriver\Http\BkashHttpClient;
use Durrbar\PaymentBkashDriver\Job\CheckBkashRefundStatusJob;
use Illuminate\Support\Facades\Log;

class BkashRefund
{
    protected $config;

    protected $httpClient;

    public function __construct(BkashConfig $config, BkashHttpClient $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
    }

    public function refundPayment(mixed $payment, ?float $refundAmount = null): array
    {
        if (! $payment->bank_payment_id || ! $payment->bank_tran_id) {
            Log::error('Invalid refund request: Missing paymentID or transactionId.');

            return [
                'status' => 'error',
                'message' => 'Invalid refund request. Missing transaction details.',
            ];
        }

        $refundAmount = $refundAmount ?? $payment->amount;
        $payload = [
            'paymentID' => $payment->bank_payment_id,
            'amount' => $refundAmount,
            'trxID' => $payment->bank_tran_id,
            'sku' => 'REFUND-'.$payment->tran_id,
            'reason' => 'Customer request',
        ];

        $response = $this->httpClient->client()->post('/v2/tokenized-checkout/refund/payment/transaction', $payload)->json();
        Log::info('bKash Refund Response', ['response' => $response]);

        if (! empty($response['statusCode']) && $response['statusCode'] === '0000') {
            $refundTransactions = $response['refundTransactions'] ?? [];
            foreach ($refundTransactions as $transaction) {
                if ($transaction['refundTransactionStatus'] === 'Completed') {
                    return [
                        'status' => 'success',
                        'message' => 'Refund successful',
                        'details' => $transaction,
                    ];
                }
            }

            // Dispatch job to check refund status after 30 seconds
            CheckBkashRefundStatusJob::dispatch($payment->transactionId)->delay(now()->addSeconds(30));

            return [
                'status' => 'pending',
                'message' => 'Refund initiated. Checking status in 30 seconds.',
            ];
        }

        return [
            'status' => 'error',
            'message' => $response['statusMessage'] ?? 'Refund failed',
        ];
    }

    public function checkRefundStatus(string $trxID): array
    {
        $response = $this->httpClient->client()->post('/v2/tokenized-checkout/refund/payment/status', ['trxID' => $trxID])->json();
        Log::info('bKash Refund Status', ['response' => $response]);

        if (! empty($response['statusCode']) && $response['statusCode'] === '0000') {
            $refundTransactions = $response['refundTransactions'] ?? [];
            foreach ($refundTransactions as $transaction) {
                if ($transaction['refundTransactionStatus'] === 'Completed') {
                    return [
                        'status' => 'success',
                        'message' => 'Refund successful',
                        'details' => $transaction,
                    ];
                }
            }
        }

        return [
            'status' => 'error',
            'message' => $response['statusMessage'] ?? 'Refund status check failed',
        ];
    }
}
