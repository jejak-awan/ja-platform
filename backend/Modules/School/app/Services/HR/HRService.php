<?php

namespace Modules\School\Services\HR;

use Modules\School\Models\HR\Staff;
use Illuminate\Pagination\LengthAwarePaginator;

class HRService
{
    /**
     * Get staff with filters.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Staff>
     */
    public function getStaffList(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Staff::with('user');

        if (!empty($filters['search']) && is_string($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search): void {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('nuptk', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['ptk_type'])) {
            $query->where('ptk_type', $filters['ptk_type']);
        }

        if (!empty($filters['employment_status'])) {
            $query->where('employment_status', $filters['employment_status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new staff member.
     *
     * @param array<string, mixed> $data
     */
    public function createStaff(array $data): Staff
    {
        /** @var Staff $staff */
        $staff = Staff::create($data);
        return $staff;
    }

    /**
     * Update staff member details.
     *
     * @param array<string, mixed> $data
     */
    public function updateStaff(Staff $staff, array $data): Staff
    {
        $staff->update($data);
        return $staff;
    }

    /**
     * Delete a staff member.
     */
    public function deleteStaff(Staff $staff): bool
    {
        return (bool) $staff->delete();
    }
}
