<?php

namespace Modules\School\Services\HR;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\School\Models\HR\Staff;

class HRService
{
    /**
     * Get staff with filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Staff>
     */
    public function getStaffList(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Staff::with('user');

        if (! empty($filters['search']) && is_string($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $searchStr = strtolower($search);
                $q->where(DB::raw('lower(full_name)'), 'like', "%{$searchStr}%")
                    ->orWhere(DB::raw('lower(nik)'), 'like', "%{$searchStr}%")
                    ->orWhere(DB::raw('lower(nip)'), 'like', "%{$searchStr}%")
                    ->orWhere(DB::raw('lower(nuptk)'), 'like', "%{$searchStr}%");
            });
        }

        if (! empty($filters['ptk_type'])) {
            $query->where('ptk_type', $filters['ptk_type']);
        }

        if (! empty($filters['employment_status'])) {
            $query->where('employment_status', $filters['employment_status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new staff member.
     *
     * @param  array<string, mixed>  $data
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
     * @param  array<string, mixed>  $data
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
