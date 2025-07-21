<?php

namespace Durrbar\PaymentBkashDriver;

use Durrbar\PaymentBkashDriver\Config\BkashConfig;
use Durrbar\PaymentBkashDriver\Http\BkashHttpClient;
use Durrbar\PaymentBkashDriver\Payment\BkashHandler;
use Durrbar\PaymentBkashDriver\Payment\BkashPayment;
use Durrbar\PaymentBkashDriver\Payment\BkashRefund;
use Modules\Payment\Drivers\BasePaymentDriver;

class PaymentBkashDriver extends BasePaymentDriver
{
    protected $config;

    protected $httpClient;

    protected $payment;

    protected $refund;

    protected $handler;

    public function __construct()
    {
        $this->config = new BkashConfig();
        $this->httpClient = new BkashHttpClient($this->config);
        $this->payment = new BkashPayment($this->config, $this->httpClient, $this);
        $this->refund = new BkashRefund($this->config, $this->httpClient);
        $this->handler = new BkashHandler($this->config, $this->httpClient, $this);
    }

    public function initiatePayment(mixed $payment): array
    {
        return $this->payment->initiatePayment($payment);
    }

    public function verifyPayment(string $transactionId): array
    {
        return $this->payment->verifyPayment($transactionId);
    }

    public function refundPayment(mixed $payment): array
    {
        return $this->refund->refundPayment($payment);
    }

    public function handleIPN(array $data): array
    {
        return $this->handler->handleIPN($data);
    }

    public function handleSuccess(array $data): array
    {
        return $this->handler->handleSuccess($data);
    }

    public function handleFailure(array $data): array
    {
        return $this->handler->handleFailure($data);
    }

    public function handleCancel(array $data): array
    {
        return $this->handler->handleCancel($data);
    }
}
