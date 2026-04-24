<?php

namespace Modules\School\Http\Controllers\Api\Operations;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Services\Operations\OperationsService;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends BaseController
{
    protected OperationsService $service;

    public function __construct(OperationsService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', Activity::class);
        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $perPageValue = $request->get('per_page', 20);
        $perPage = is_numeric($perPageValue) ? (int)$perPageValue : 20;
        $logs = $this->service->getAuditLogs($filters, $perPage);
        return $this->sendResponse($logs, 'Audit logs retrieved successfully.');
    }

    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', Activity::class);
        /** @var Activity $log */
        $log = Activity::with(['causer', 'subject'])->findOrFail($id);
        return $this->sendResponse($log, 'Audit log detail retrieved.');
    }
}
