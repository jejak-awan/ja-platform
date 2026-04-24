<?php

namespace Modules\School\Policies;

use Modules\Core\Models\User;
use Modules\School\Models\Finance\StudentBill;

class FinancePolicy
{
    /**
     * Determine if the user can view any finance records.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view school finance', 'manage school finance']);
    }

    /**
     * Determine if the user can view the bill.
     */
    public function view(User $user, StudentBill $bill): bool
    {
        // Students can view their own bills
        $student = $bill->student;
        if ((int)$user->id === (int)$student->user_id) {
            return true;
        }

        return $user->hasAnyPermission(['view school finance', 'manage school finance']);
    }

    /**
     * Determine if the user can manage fee types.
     */
    public function manageFeeTypes(User $user): bool
    {
        return $user->hasAnyPermission(['manage school finance']);
    }

    /**
     * Determine if the user can generate bills.
     */
    public function generateBills(User $user): bool
    {
        return $user->hasAnyPermission(['manage school finance']);
    }

    /**
     * Determine if the user can process payments.
     */
    public function processPayment(User $user): bool
    {
        return $user->hasAnyPermission(['manage school finance']);
    }

    /**
     * Determine if the user can manage expenses.
     */
    public function manageExpenses(User $user): bool
    {
        return $user->hasAnyPermission(['manage school finance']);
    }

    /**
     * Determine if the user can manage budgets.
     */
    public function manageBudgets(User $user): bool
    {
        return $user->hasAnyPermission(['manage school finance']);
    }
}
