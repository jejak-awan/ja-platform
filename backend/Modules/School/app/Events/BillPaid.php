<?php

namespace Modules\School\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\School\Models\Finance\PaymentTransaction;

class BillPaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PaymentTransaction $transaction;

    /**
     * Create a new event instance.
     */
    public function __construct(PaymentTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
