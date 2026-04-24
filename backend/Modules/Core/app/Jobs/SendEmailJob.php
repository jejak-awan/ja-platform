<?php

namespace Modules\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Cms\Models\EmailTemplate;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 60;

    /** @var array<int, int> */
    public array $backoff = [30, 60, 120]; // Retry after 30s, 1min, 2min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $to,
        public string $subject,
        public string $body,
        public ?string $templateSlug = null,
        /** @var array<string, mixed> */
        public array $data = [],
        public ?string $from = null,
        public ?string $fromName = null
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // If template slug is provided, use email template
            if ($this->templateSlug) {
                $template = EmailTemplate::getBySlug($this->templateSlug);
                if ($template) {
                    $rendered = $template->render($this->data);
                    $this->subject = $rendered['subject'];
                    $this->body = $rendered['body'];
                }
            }

            // Send email
            Mail::raw($this->body, function ($message) {
                $message->to($this->to)
                    ->subject($this->subject);

                if ($this->from) {
                    $message->from($this->from, $this->fromName);
                }
            });

            Log::channel('email')->info('SendEmailJob: Email sent successfully', [
                'to' => $this->to,
                'subject' => $this->subject,
            ]);
        } catch (\Exception $e) {
            Log::channel('email')->error('SendEmailJob failed: '.$e->getMessage(), [
                'to' => $this->to,
                'subject' => $this->subject,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('email')->error('SendEmailJob permanently failed', [
            'to' => $this->to,
            'subject' => $this->subject,
            'error' => $exception->getMessage(),
        ]);
    }
}
