<?php

namespace Modules\School\Http\Controllers\Api\Finance;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Finance\FeeType;
use Modules\School\Models\Finance\StudentBill;
use Modules\School\Models\Finance\PaymentTransaction;
use Modules\School\Models\Finance\Budget;
use Modules\School\Models\Finance\Expense;
use Modules\School\Services\Finance\FinanceService;
use Modules\School\Http\Requests\Finance\StoreFeeTypeRequest;
use Modules\School\Http\Requests\Finance\GenerateBillsRequest;
use Modules\School\Http\Requests\Finance\PayBillRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Context;

class FinanceController extends BaseController
{
    protected FinanceService $service;

    public function __construct(FinanceService $service)
    {
        $this->service = $service;
    }

    public function feeTypes(): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('viewAny', StudentBill::class);

        $feeTypes = $this->service->getAllFeeTypes();
        return $this->sendResponse($feeTypes, 'Fee types retrieved successfully.');
    }

    public function storeFeeType(StoreFeeTypeRequest $request): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('manageFeeTypes', StudentBill::class);

        try {
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            $feeType = $this->service->createFeeType($validated);
            return $this->sendResponse($feeType, 'Fee type created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Failed to create fee type.', [$e->getMessage()], 500);
        }
    }

    public function updateFeeType(StoreFeeTypeRequest $request, int $id): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('manageFeeTypes', StudentBill::class);

        try {
            /** @var FeeType $feeType */
            $feeType = FeeType::findOrFail($id);
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            $feeType->update($validated);
            return $this->sendResponse($feeType, 'Fee type updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update fee type.', [$e->getMessage()], 500);
        }
    }

    public function destroyFeeType(int $id): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('manageFeeTypes', StudentBill::class);

        try {
            /** @var FeeType $feeType */
            $feeType = FeeType::findOrFail($id);
            $feeType->delete();
            return $this->sendResponse([], 'Fee type deleted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to delete fee type.', [$e->getMessage()], 500);
        }
    }

    public function bills(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', StudentBill::class);

        /** @var array<string, mixed> $filters */
        $filters = $request->all();
        $perPage = $request->has('per_page') && is_numeric($request->get('per_page')) 
            ? (int) $request->get('per_page') 
            : 20;

        $bills = $this->service->getBills($filters, $perPage);
        return $this->sendResponse($bills, 'Bills retrieved successfully.');
    }

    public function generateBills(GenerateBillsRequest $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('generateBills', StudentBill::class);

        try {
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            \Modules\School\Jobs\GenerateBillsJob::dispatch($validated);
            return $this->sendResponse([], "Bill generation started in the background.");
        } catch (\Exception $e) {
            return $this->sendError('Failed to queue bill generation.', [$e->getMessage()], 500);
        }
    }

    public function payBill(PayBillRequest $request, StudentBill $bill): \Illuminate\Http\JsonResponse
    {
        $this->authorize('processPayment', StudentBill::class);

        try {
            /** @var int $userId */
            $userId = (int) auth()->id();
            /** @var array<string, mixed> $validated */
            $validated = $request->validated();
            $transaction = $this->service->payBill($bill, $validated, $userId);
            return $this->sendResponse($transaction, 'Payment processed successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to process payment.', [$e->getMessage()], 500);
        }
    }

    // --- Expenses ---

    public function expenses(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', StudentBill::class);

        $query = Expense::query();

        if ($request->has('category')) {
            $category = $request->input('category');
            if (is_string($category)) {
                $query->where('category', $category);
            }
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->input('start_date'), $request->input('end_date')]);
        }

        return $this->sendResponse($query->latest()->paginate(20), 'Expenses retrieved successfully.');
    }

    public function storeExpense(Request $request): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('manageExpenses', StudentBill::class);

        /** @var array{school_id: int, school_level_id: int, category: string, amount: float, date: string, description: string, attachment_path?: string} $validated */
        $validated = $request->validate([
            'school_id' => 'required|exists:sch_ins_schools,id',
            'school_level_id' => 'required|exists:sch_ins_school_levels,id',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'required|string|max:255',
        ]);

        try {
            if ($request->hasFile('evidence')) {
                /** @var \Illuminate\Http\UploadedFile $file */
                $file = $request->file('evidence');
                $validated['attachment_path'] = $file->store('expenses', 'public');
            }

            $expense = Expense::create($validated);
            $this->service->syncBudgetActuals((int)$expense->school_id, (string)$expense->category);

            return $this->sendResponse($expense, 'Expense recorded successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Failed to record expense.', [$e->getMessage()], 500);
        }
    }

    public function budgets(Request $request): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('viewAny', StudentBill::class);

        $schoolId = $request->input('school_id')
            ?? $request->header('X-School-Id')
            ?? Context::get('school_id');

        $query = Budget::with('academicYear');

        if ($schoolId && is_numeric($schoolId)) {
            $query->where('school_id', (int)$schoolId);
        }

        return $this->sendResponse($query->get(), 'Budgets retrieved successfully.');
    }

    public function storeBudget(Request $request): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('manageBudgets', StudentBill::class);

        /** @var array{school_id: int, school_level_id: int, academic_year_id: int, category: string, planned_amount: float, notes?: string|null} $validated */
        $validated = $request->validate([
            'school_id' => 'required|exists:sch_ins_schools,id',
            'school_level_id' => 'required|exists:sch_ins_school_levels,id',
            'academic_year_id' => 'required|exists:sch_acad_years,id',
            'category' => 'required|string|max:100',
            'planned_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $budget = Budget::create($validated);
            $this->service->syncBudgetActuals((int)$budget->school_id, (string)$budget->category);

            return $this->sendResponse($budget, 'Budget created successfully.', 201);
        } catch (\Exception $e) {
            return $this->sendError('Failed to create budget.', [$e->getMessage()], 500);
        }
    }

    public function summary(Request $request): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('viewAny', StudentBill::class);

        $schoolId = $request->header('X-School-Id')
            ?? $request->input('school_id')
            ?? Context::get('school_id');

        if (!$schoolId || !is_numeric($schoolId)) {
            $schoolModel = \Modules\School\Models\Institution\School::first();
            $schoolId = $schoolModel ? $schoolModel->id : null;
        }

        if (!$schoolId) {
             return $this->sendError('School not found.', [], 404);
        }

        try {
            $schoolIdValue = (int)$schoolId;
            $academicYearId = $request->header('X-Academic-Year-Id');
            $ayId = is_numeric($academicYearId) ? (int)$academicYearId : null;
            $summary = $this->service->getSummary($schoolIdValue, $ayId);
            return $this->sendResponse($summary, 'Financial summary retrieved successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to retrieve financial summary.', [$e->getMessage()], 500);
        }
    }
}
