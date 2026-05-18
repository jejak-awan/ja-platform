<?php

namespace Modules\School\Http\Controllers\Api\Operations;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Institution\School;
use Modules\School\Models\Student\Student;
use Modules\School\Services\Operations\OperationsService;

class AnalyticsController extends BaseController
{
    public function __construct(protected OperationsService $service) {}

    public function status(): JsonResponse
    {
        return $this->sendResponse(['status' => 'online'], 'Analytics engine is online.');
    }

    public function executiveSummary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Student::class);
        $schoolIdValue = $request->header('X-School-Id') ?? (string) $request->string('school_id');
        $defaultId = School::value('id');
        $schoolId = is_scalar($schoolIdValue) && $schoolIdValue !== '' ? (string) $schoolIdValue : (is_scalar($defaultId) ? (string) $defaultId : '1');
        $summary = $this->service->getExecutiveSummary($schoolId);

        return $this->sendResponse($summary, 'Executive summary retrieved successfully.');
    }
}
