<?php

namespace Modules\Core\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Modules\Core\Helpers\IpHelper;
use Modules\Core\Services\AnomalyDetectionService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Handle rate limiting exceptions - add retry_after to response body
        if ($e instanceof TooManyRequestsHttpException) {
            $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many attempts. Please try again later.',
                    'retry_after' => (int) $retryAfter,
                ], 429)->withHeaders([
                    'Retry-After' => $retryAfter,
                ]);
            }
        }

        // Track 404 anomalies for statistical profiling
        if ($e instanceof NotFoundHttpException) {
            app(AnomalyDetectionService::class)->trackEvent(
                '404',
                IpHelper::getClientIp($request),
                $request->hasSession() ? session()->getId() : null
            );
        }

        return parent::render($request, $e);
    }
}
