<?php

namespace Durrbar\PaymentBkashDriver\Job;

use Durrbar\PaymentBkashDriver\Payment\BkashRefund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels; // Import BkashRefund
use Illuminate\Support\Facades\Log;

class CheckBkashRefundStatusJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $transactionId;

    public function __construct(string $transactionId)
    {
        $this->transactionId = $transactionId;
    }

    public function handle(BkashRefund $bkashRefund)
    {
        // Call checkRefundStatus from BkashRefund
        $status = $bkashRefund->checkRefundStatus($this->transactionId);

        // Log the result
        Log::info('Checked bKash refund status', [
            'transactionId' => $this->transactionId,
            'status' => $status,
        ]);
    }
}
