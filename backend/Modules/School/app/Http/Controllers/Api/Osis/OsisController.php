<?php

namespace Modules\School\Http\Controllers\Api\Osis;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Osis\OsisProgram;
use Modules\School\Models\Osis\OsisMember;
use Modules\School\Models\Osis\OsisFinance;
use Modules\School\Models\Osis\OsisSuggestion;
use Modules\School\Services\Osis\OsisService;

class OsisController extends BaseController
{
    public function __construct(protected OsisService $service)
    {
    }

    // --- Programs ---
    public function programs(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', OsisProgram::class);
        $schoolIdValue = $request->header('X-School-Id') ?? $request->input('school_id');
        $schoolId = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        
        $programs = $this->service->getPrograms($schoolId);
        return $this->sendResponse($programs, 'OSIS programs retrieved successfully.');
    }

    public function storeProgram(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', OsisProgram::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'planned_date' => 'nullable|date',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'estimated_budget' => 'numeric|min:0',
        ]);

        $schoolIdValue = $request->header('X-School-Id') ?? $request->input('school_id');
        $validated['school_id'] = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        
        $program = $this->service->createProgram($validated);

        return $this->sendResponse($program, 'OSIS program created successfully.', 201);
    }

    public function updateProgram(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var OsisProgram $program */
        $program = OsisProgram::findOrFail($id);
        $this->authorize('update', $program);

        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'planned_date' => 'nullable|date',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'estimated_budget' => 'numeric|min:0',
        ]);

        $program = $this->service->updateProgram($program, $validated);
        return $this->sendResponse($program, 'OSIS program updated successfully.');
    }

    public function destroyProgram(int $id): \Illuminate\Http\JsonResponse
    {
        /** @var OsisProgram $program */
        $program = OsisProgram::findOrFail($id);
        $this->authorize('delete', $program);
        $this->service->deleteProgram($program);
        return $this->sendResponse([], 'OSIS program deleted successfully.');
    }

    // --- Members ---
    public function members(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', OsisMember::class);
        $schoolIdValue = $request->header('X-School-Id') ?? $request->input('school_id');
        $schoolId = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        $members = $this->service->getMembers($schoolId);
        return $this->sendResponse($members, 'OSIS members retrieved successfully.');
    }

    public function storeMember(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', OsisMember::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'student_id' => 'required|exists:sch_stud_students,id',
            'position' => 'required|string|max:100',
            'period' => 'required|string|max:50',
        ]);

        $schoolIdValue = $request->header('X-School-Id') ?? $request->input('school_id');
        $validated['school_id'] = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        $member = $this->service->addMember($validated);

        return $this->sendResponse($member, 'OSIS member added successfully.', 201);
    }

    // --- Finances ---
    public function finances(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', OsisFinance::class);
        $schoolIdValue = $request->header('X-School-Id') ?? $request->input('school_id');
        $schoolId = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        $finances = $this->service->getFinances($schoolId);
        return $this->sendResponse($finances, 'OSIS finances retrieved successfully.');
    }

    public function storeFinance(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('create', OsisFinance::class);
        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'program_id' => 'nullable|exists:sch_osis_programs,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'transaction_date' => 'required|date',
        ]);

        $schoolIdValue = $request->header('X-School-Id') ?? $request->input('school_id');
        $validated['school_id'] = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        $finance = $this->service->createFinance($validated);

        return $this->sendResponse($finance, 'OSIS finance entry created successfully.', 201);
    }

    // --- Suggestions ---
    public function suggestions(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', OsisSuggestion::class);
        $schoolIdValue = $request->header('X-School-Id') ?? $request->input('school_id');
        $schoolId = is_numeric($schoolIdValue) ? (int)$schoolIdValue : 1;
        $suggestions = $this->service->getSuggestions($schoolId);
        return $this->sendResponse($suggestions, 'OSIS suggestions retrieved successfully.');
    }

    public function updateSuggestion(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        /** @var OsisSuggestion $suggestion */
        $suggestion = OsisSuggestion::findOrFail($id);
        $this->authorize('update', $suggestion);

        /** @var array<string, mixed> $validated */
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,actioned,rejected',
            'response' => 'nullable|string',
        ]);

        $suggestion = $this->service->updateSuggestion($suggestion, $validated);
        return $this->sendResponse($suggestion, 'OSIS suggestion updated successfully.');
    }
}
