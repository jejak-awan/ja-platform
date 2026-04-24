<?php

namespace Modules\School\Http\Controllers\Api\Operations;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Services\Operations\OperationsService;

class AnalyticsController extends BaseController
{
    protected OperationsService $service;

    public function __construct(OperationsService $service)
    {
        $this->service = $service;
    }

    public function status(): \Illuminate\Http\JsonResponse
    {
        return $this->sendResponse(['status' => 'online'], 'Analytics engine is online.');
    }

    public function executiveSummary(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', \Modules\School\Models\Student\Student::class);
        $schoolIdValue = $request->header('X-School-Id') ?? $request->input('school_id');
        $defaultId = \Modules\School\Models\Institution\School::value('id') ?? 1;
        $schoolId = is_numeric($schoolIdValue) ? (int)$schoolIdValue : (is_numeric($defaultId) ? (int)$defaultId : 1);
        $summary = $this->service->getExecutiveSummary($schoolId);

        return $this->sendResponse($summary, 'Executive summary retrieved successfully.');
    }

    public function financialCharts(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', \Modules\School\Models\Finance\Expense::class);
        $schoolIdValue = $request->header('X-School-Id') ?? $request->input('school_id');
        $defaultId = \Modules\School\Models\Institution\School::value('id') ?? 1;
        $schoolId = is_numeric($schoolIdValue) ? (int)$schoolIdValue : (is_numeric($defaultId) ? (int)$defaultId : 1);
        $yearValue = $request->input('year');
        $year = is_numeric($yearValue) ? (int)$yearValue : (int)date('Y');

        $data = $this->service->getFinancialChartData($schoolId, $year);

        return $this->sendResponse($data, 'Financial chart data retrieved successfully.');
    }
}
