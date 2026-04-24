<?php

namespace Modules\School\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\School\Services\Finance\FinanceService;
use Illuminate\Support\Facades\Log;

class GenerateBillsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var array<string, mixed> */
    protected array $data;

    /**
     * Create a new job instance.
     * 
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(FinanceService $financeService): void
    {
        try {
            $count = $financeService->generateBills($this->data);
            $schoolIdValue = $this->data['school_id'] ?? 0;
            $schoolId = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 0;
            Log::info("GenerateBillsJob successfully created $count bills", ['school_id' => $schoolId]);
        } catch (\Exception $e) {
            Log::error("GenerateBillsJob failed", [
                'error' => $e->getMessage(),
                'data' => $this->data
            ]);
            throw $e;
        }
    }
}
