<?php

namespace Modules\School\Services\Finance;

use Modules\School\Models\Finance\FeeType;
use Modules\School\Models\Finance\StudentBill;
use Modules\School\Models\Finance\PaymentTransaction;
use Modules\School\Models\Finance\Budget;
use Modules\School\Models\Finance\Expense;
use Modules\School\Models\Student\Student;
use Modules\School\Models\Academic\StudyGroup;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class FinanceService
{
    /**
     * Get all fee types.
     *
     * @return Collection<int, FeeType>
     */
    public function getAllFeeTypes(): Collection
    {
        return FeeType::all();
    }

    /**
     * Create a new fee type.
     *
     * @param array<string, mixed> $data
     */
    public function createFeeType(array $data): FeeType
    {
        /** @var FeeType $feeType */
        $feeType = FeeType::create($data);
        return $feeType;
    }

    /**
     * Get bills with filters.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, StudentBill>
     */
    public function getBills(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = StudentBill::with(['student', 'feeType', 'academicYear']);
        
        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }
        
        if (!empty($filters['status']) && is_string($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Generate bills for a study group.
     *
     * @param array<string, mixed> $data
     */
    public function generateBills(array $data): int
    {
        /** @var int $feeTypeId */
        $feeTypeId = is_numeric($data['fee_type_id'] ?? null) ? (int)$data['fee_type_id'] : 0;
        /** @var FeeType $feeType */
        $feeType = FeeType::findOrFail($feeTypeId);

        /** @var int $studyGroupId */
        $studyGroupId = is_numeric($data['study_group_id'] ?? null) ? (int)$data['study_group_id'] : 0;
        /** @var StudyGroup $group */
        $group = StudyGroup::with('students')->findOrFail($studyGroupId);
        
        // Pre-fetch existing bills to avoid N+1 queries
        $existingBills = StudentBill::where('fee_type_id', $feeType->id)
            ->where('academic_year_id', $data['academic_year_id'])
            ->where('month', $data['month'] ?? null)
            ->whereIn('student_id', $group->students->pluck('id'))
            ->pluck('student_id')
            ->toArray();

        // Batch build insert data
        $billsToInsert = [];
        $now = now();
        foreach ($group->students as $student) {
            if (!in_array($student->id, $existingBills)) {
                $billsToInsert[] = [
                    'school_id' => $data['school_id'],
                    'school_level_id' => $data['school_level_id'],
                    'student_id' => $student->id,
                    'fee_type_id' => $feeType->id,
                    'academic_year_id' => $data['academic_year_id'],
                    'month' => $data['month'] ?? null,
                    'amount' => (float) $feeType->amount,
                    'paid_amount' => 0.0,
                    'due_date' => $data['due_date'] ?? null,
                    'status' => 'unpaid',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Batch insert for performance (chunks of 100 to avoid query size limits)
        if (!empty($billsToInsert)) {
            foreach (array_chunk($billsToInsert, 100) as $chunk) {
                StudentBill::insert($chunk);
            }
        }

        return count($billsToInsert);
    }

    /**
     * Process bill payment.
     *
     * @param array<string, mixed> $data
     */
    public function payBill(StudentBill $bill, array $data, int $verifiedBy): PaymentTransaction
    {
        /** @var PaymentTransaction $transaction */
        $transaction = PaymentTransaction::create([
            'school_id' => $bill->school_id,
            'student_bill_id' => $bill->id,
            'amount' => is_numeric($data['amount'] ?? null) ? (float)$data['amount'] : 0.0,
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'],
            'reference_number' => $data['reference_number'] ?? null,
            'notes' => $data['notes'] ?? null,
            'verified_by' => $verifiedBy,
        ]);

        $amount = is_numeric($data['amount'] ?? null) ? (float)$data['amount'] : 0.0;
        $billPaidAmount = is_numeric($bill->paid_amount) ? (float)$bill->paid_amount : 0.0;
        $bill->paid_amount = (string)($billPaidAmount + $amount);
        
        if ((float)$bill->paid_amount >= (float)$bill->amount) {
            $bill->status = 'paid';
        } elseif ((float)$bill->paid_amount > 0.0) {
            $bill->status = 'partially_paid';
        }
        $bill->save();

        return $transaction;
    }

    /**
     * Get financial summary for a school.
     *
     * @return array{total_receivables: float, total_collections: float, total_expenses: float, net_balance: float, collection_rate: float}
     */
    public function getSummary(int $schoolId, ?int $academicYearId = null): array
    {
        $billQuery = StudentBill::where('school_id', $schoolId);
        $expenseQuery = Expense::where('school_id', $schoolId);

        if ($academicYearId) {
            $billQuery->where('academic_year_id', $academicYearId);
        }

        $totalBills = (float)$billQuery->sum('amount');
        $totalPaid = (float)$billQuery->sum('paid_amount');
        $totalExpenses = (float)$expenseQuery->sum('amount');

        return [
            'total_receivables' => (float)($totalBills - $totalPaid),
            'total_collections' => (float)$totalPaid,
            'total_expenses' => (float)$totalExpenses,
            'net_balance' => (float)($totalPaid - $totalExpenses),
            'collection_rate' => $totalBills > 0.0 ? (float)round(($totalPaid / $totalBills) * 100, 2) : 0.0,
        ];
    }

    /**
     * Sync budget actuals based on expenses.
     */
    public function syncBudgetActuals(int $schoolId, string $category): void
    {
        $totalActual = (float) Expense::where('school_id', $schoolId)
            ->where('category', $category)
            ->sum('amount');

        /** @var Budget|null $budget */
        $budget = Budget::where('school_id', $schoolId)
            ->where('category', $category)
            ->first();

        if ($budget) {
            $isWarning = false;
            if ((float)$budget->planned_amount > 0.0) {
                $percentage = ($totalActual / (float)$budget->planned_amount) * 100;
                if ($percentage >= (float)($budget->warning_threshold ?? 90)) {
                    $isWarning = true;
                }
            }
            $budget->update([
                'actual_amount' => $totalActual,
                'is_warning' => $isWarning
            ]);
        }
    }
}
