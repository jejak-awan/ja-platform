<?php

namespace Modules\School\Http\Controllers\Api\HR;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Http\Requests\HR\StoreStaffRequest;
use Modules\School\Http\Requests\HR\UpdateStaffRequest;
use Modules\School\Models\HR\Staff;
use Modules\School\Services\HR\HRService;

class StaffController extends BaseController
{
    public function __construct(protected HRService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Staff::class);

        /** @var array<string, mixed> $allInputs */
        $allInputs = $request->all();
        $perPageValue = $request->input('per_page', 20);
        $perPage = is_numeric($perPageValue) ? (int) $perPageValue : 20;
        $staff = $this->service->getStaffList($allInputs, $perPage);

        return $this->sendResponse($staff, 'Staff retrieved successfully.');
    }

    public function store(StoreStaffRequest $request): JsonResponse
    {
        $this->authorize('create', Staff::class);

        try {
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            $staff = $this->service->createStaff($validated);

            return $this->sendResponse($staff, 'Staff created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Failed to create staff.', [$e->getMessage()], 500);
        }
    }

    public function show(Staff $staff): JsonResponse
    {
        $this->authorize('view', $staff);

        return $this->sendResponse($staff->load(['school', 'user']), 'Staff retrieved successfully.');
    }

    public function update(UpdateStaffRequest $request, Staff $staff): JsonResponse
    {
        $this->authorize('update', $staff);

        try {
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            $staff = $this->service->updateStaff($staff, $validated);

            return $this->sendResponse($staff, 'Staff updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update staff.', [$e->getMessage()], 500);
        }
    }

    public function destroy(Staff $staff): JsonResponse
    {
        $this->authorize('delete', $staff);

        try {
            $this->service->deleteStaff($staff);

            return $this->sendResponse([], 'Staff deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to delete staff.', [$e->getMessage()], 500);
        }
    }
}
