<?php

namespace Modules\School\Listeners;

use Modules\School\Events\BillPaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogFinancialActivity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BillPaid $event): void
    {
        Log::channel('school_audit')->info('Bill payment processed: ' . $event->transaction->reference_number, [
            'transaction_id' => $event->transaction->id,
            'bill_id' => $event->transaction->student_bill_id,
            'amount' => $event->transaction->amount,
            'school_id' => $event->transaction->school_id,
            'user' => auth()->id() ?? 'system'
        ]);
    }
}
