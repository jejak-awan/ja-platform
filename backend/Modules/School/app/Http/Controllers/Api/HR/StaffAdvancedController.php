<?php

namespace Modules\School\Http\Controllers\Api\HR;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\HR\StaffShift;
use Modules\School\Models\HR\LeaveRequest;
use Modules\School\Models\HR\Staff;
use Modules\School\Models\HR\Payroll;
use Modules\School\Models\HR\SalaryStructure;
use Modules\School\Models\HR\StaffAttendance;
use Carbon\Carbon;

class StaffAdvancedController extends BaseController
{
    public function shifts(): \Illuminate\Http\JsonResponse
    {
        $shifts = StaffShift::all();
        return $this->sendResponse($shifts, 'Staff shifts retrieved successfully.');
    }

    public function storeShift(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'description' => 'nullable|string',
        ]);

        $shift = StaffShift::create($validated);
        return $this->sendResponse($shift, 'Staff shift created successfully.', 201);
    }

    public function updateShift(Request $request, int|string $id): \Illuminate\Http\JsonResponse
    {
        $shift = StaffShift::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'description' => 'nullable|string',
        ]);

        $shift->update($validated);
        return $this->sendResponse($shift, 'Staff shift updated successfully.');
    }

    public function destroyShift(int|string $id): \Illuminate\Http\JsonResponse
    {
        $shift = StaffShift::findOrFail($id);
        $shift->delete();
        return $this->sendResponse([], 'Staff shift deleted successfully.');
    }

    public function leaveRequests(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = LeaveRequest::with(['staff', 'approver']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPageValue = $request->get('per_page', 20);
        $perPage = is_numeric($perPageValue) ? (int)$perPageValue : 20;
        $requests = $query->latest()->paginate($perPage);
        return $this->sendResponse($requests, 'Leave requests retrieved successfully.');
    }

    public function storeLeaveRequest(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'staff_id' => 'required|exists:staff,id',
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $validated['status'] = 'pending';
        $leaveRequest = LeaveRequest::create($validated);
        return $this->sendResponse($leaveRequest, 'Leave request submitted.', 201);
    }

    public function updateLeaveStatus(Request $request, int|string $id): \Illuminate\Http\JsonResponse
    {
        $leaveRequest = LeaveRequest::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $leaveRequest->update([
            'status' => $validated['status'],
            'approved_by' => auth()->id(),
        ]);

        return $this->sendResponse($leaveRequest, 'Leave status updated successfully.');
    }

    /* 
    // Salary Structures (Disabled for BOS compliance)
    public function salaryStructures(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = SalaryStructure::with('staff');
        
        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->input('staff_id'));
        }

        return $this->sendResponse($query->get(), 'Salary structures retrieved successfully.');
    }

    public function storeSalaryStructure(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'workspace_id' => 'required|exists:school_units,id',
            'staff_id' => 'required|exists:staff,id',
            'base_salary' => 'required|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'meal_allowance' => 'nullable|numeric|min:0',
            'other_allowance' => 'nullable|numeric|min:0',
        ]);

        $structure = SalaryStructure::updateOrCreate(
            ['staff_id' => $validated['staff_id'], 'workspace_id' => $validated['workspace_id']],
            $validated
        );

        return $this->sendResponse($structure, 'Salary structure saved successfully.');
    }
    */

    /*
    // Payrolls (Disabled for BOS compliance)
    public function payrolls(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Payroll::with('staff');

        if ($request->has('period')) {
            $query->where('period', $request->input('period'));
        }
        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->input('staff_id'));
        }

        return $this->sendResponse($query->latest()->paginate(20), 'Payrolls retrieved successfully.');
    }

    public function generatePayroll(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'workspace_id' => 'required|exists:school_units,id',
            'period' => 'required|string', // YYYY-MM
        ]);

        $staffMembers = Staff::where('school_id', $validated['school_id'])->get();
        $count = 0;

        $startDate = Carbon::parse($validated['period'] . '-01');
        $endDate = $startDate->copy()->endOfMonth();

        foreach ($staffMembers as $staff) {
            $structure = SalaryStructure::where('staff_id', $staff->id)->first();
            if (!$structure) continue;

            // Avoid double generation
            $exists = Payroll::where([
                'staff_id' => $staff->id,
                'period' => $validated['period']
            ])->exists();

            if (!$exists) {
                // Calculate Deductions from Attendance
                $absences = StaffAttendance::where('staff_id', $staff->id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->where('status', 'A') // Alpa
                    ->count();

                // Deduction logic: Base Salary / 25 days per absence
                $dailyRate = (float)$structure->base_salary / 25;
                $totalDeduction = round((float)$absences * $dailyRate, 2);

                $totalAllowance = (float)$structure->transport_allowance + (float)$structure->meal_allowance + (float)$structure->other_allowance;
                Payroll::create([
                    'school_id' => $validated['school_id'],
                    'workspace_id' => $validated['workspace_id'],
                    'staff_id' => $staff->id,
                    'period' => $validated['period'],
                    'basic_salary' => $structure->base_salary,
                    'total_allowance' => $totalAllowance,
                    'total_deduction' => $totalDeduction,
                    'net_salary' => ((float)$structure->base_salary + $totalAllowance) - $totalDeduction,
                    'status' => 'Draft'
                ]);
                $count++;
            }
        }

        return $this->sendResponse([], "Successfully generated payroll for $count staff members with attendance-linked deductions.");
    }
    */
}
