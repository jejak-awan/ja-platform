<?php

namespace Modules\School\Services\Operations;

use Modules\School\Models\Operations\Visitor;
use Modules\School\Models\Operations\LibraryBook;
use Modules\School\Models\Operations\LibraryCirculation;
use Modules\School\Models\Operations\UksVisit;
use Modules\School\Models\Operations\GuestLog;
use Modules\School\Models\Student\Alumni;
use Modules\School\Models\Student\TracerStudy;
use Modules\School\Models\Student\Student;
use Modules\School\Models\HR\Staff;
use Modules\School\Models\Admission\Enrollment;
use Modules\School\Models\HR\Payroll;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OperationsService
{
    /**
     * Get visitors with status filter.
     * @return LengthAwarePaginator<int, Visitor>
     */
    public function getVisitors(?string $status = null, int $perPage = 20): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Visitor> $paginator */
        $paginator = Visitor::when($status, fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate($perPage);
        return $paginator;
    }

    /**
     * Check-in a visitor.
     * @param array<string, mixed> $data
     * @param \Illuminate\Http\UploadedFile|null $photo
     * @param \Illuminate\Http\UploadedFile|null $idCardPhoto
     */
    public function checkInVisitor(array $data, $photo = null, $idCardPhoto = null): Visitor
    {
        $data['check_in'] = now();
        $data['status'] = 'checked_in';

        if ($photo instanceof \Illuminate\Http\UploadedFile) {
            $data['photo_path'] = $photo->store('visitors/photos', 'public');
        }
        if ($idCardPhoto instanceof \Illuminate\Http\UploadedFile) {
            $data['id_card_photo_path'] = $idCardPhoto->store('visitors/ids', 'public');
        }

        /** @var Visitor $visitor */
        $visitor = Visitor::create($data);
        return $visitor;
    }

    /**
     * Check-out a visitor.
     */
    public function checkOutVisitor(Visitor $visitor): Visitor
    {
        if ($visitor->status === 'checked_out') {
            throw new \Exception('Visitor already checked out.');
        }

        $visitor->update([
            'check_out' => now(),
            'status' => 'checked_out'
        ]);

        return $visitor;
    }

    // --- Audit Logs ---

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Activity>
     */
    public function getAuditLogs(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Activity> $paginator */
        $paginator = Activity::with(['causer', 'subject'])
            ->when(!empty($filters['log_name']) && is_string($filters['log_name']), fn($q) => $q->where('log_name', $filters['log_name']))
            ->when(!empty($filters['search']) && is_string($filters['search']), function ($q) use ($filters) {
                /** @var string $search */
                $search = $filters['search'];
                return $q->where('description', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate($perPage);
        return $paginator;
    }

    // --- Analytics ---

    /**
     * @return array{students: array{total: int, active: int, graduated: int}, admission: array{total_applicants: int, admitted: int, conversion_rate: float}, staff: array{total: int, attendance_today: int}}
     */
    public function getExecutiveSummary(int $schoolId): array
    {
        return [
            'students' => [
                'total' => (int) Student::where('school_id', $schoolId)->count(),
                'active' => (int) Student::where('school_id', $schoolId)->where('status', 'active')->count(),
                'graduated' => (int) Student::where('school_id', $schoolId)->where('status', 'graduated')->count(),
            ],
            'admission' => [
                'total_applicants' => (int) Enrollment::where('school_id', $schoolId)->count(),
                'admitted' => (int) Enrollment::where('school_id', $schoolId)->where('status', 'admitted')->count(),
                'conversion_rate' => (float) (Enrollment::where('school_id', $schoolId)->count() > 0 
                    ? round((Enrollment::where('school_id', $schoolId)->where('status', 'admitted')->count() / Enrollment::where('school_id', $schoolId)->count()) * 100, 2)
                    : 0),
            ],
            'staff' => [
                'total' => (int) Staff::where('school_id', $schoolId)->count(),
                'attendance_today' => 0,
            ]
        ];
    }



    // --- Library ---

    /**
     * @return Collection<int, LibraryBook>
     */
    public function getLibraryBooks(): Collection
    {
        /** @var Collection<int, LibraryBook> $books */
        $books = LibraryBook::latest()->get();
        return $books;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createLibraryBook(array $data): LibraryBook
    {
        /** @var LibraryBook $book */
        $book = LibraryBook::create($data);
        return $book;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateLibraryBook(LibraryBook $book, array $data): LibraryBook
    {
        $book->update($data);
        return $book;
    }

    /**
     * @return Collection<int, LibraryCirculation>
     */
    public function getLibraryCirculations(): Collection
    {
        /** @var Collection<int, LibraryCirculation> $circulations */
        $circulations = LibraryCirculation::with(['book', 'borrower'])->latest()->get();
        return $circulations;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createLibraryCirculation(array $data): LibraryCirculation
    {
        /** @var LibraryCirculation $circulation */
        $circulation = LibraryCirculation::create($data);
        return $circulation;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateLibraryCirculation(LibraryCirculation $circulation, array $data): LibraryCirculation
    {
        $circulation->update($data);
        return $circulation;
    }

    // --- UKS ---

    /**
     * @return Collection<int, UksVisit>
     */
    public function getUksVisits(): Collection
    {
        /** @var Collection<int, UksVisit> $visits */
        $visits = UksVisit::with('patient')->latest()->get();
        return $visits;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createUksVisit(array $data): UksVisit
    {
        /** @var UksVisit $visit */
        $visit = UksVisit::create($data);
        return $visit;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateUksVisit(UksVisit $visit, array $data): UksVisit
    {
        $visit->update($data);
        return $visit;
    }

    // --- Guest ---

    /**
     * @return Collection<int, GuestLog>
     */
    public function getGuestLogs(): Collection
    {
        /** @var Collection<int, GuestLog> $logs */
        $logs = GuestLog::latest()->get();
        return $logs;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createGuestLog(array $data): GuestLog
    {
        /** @var GuestLog $log */
        $log = GuestLog::create($data);
        return $log;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateGuestLog(GuestLog $log, array $data): GuestLog
    {
        $log->update($data);
        return $log;
    }

    // --- Alumni ---

    /**
     * @return Collection<int, Alumni>
     */
    public function getAlumni(): Collection
    {
        /** @var Collection<int, Alumni> $alumni */
        $alumni = Alumni::with('student')->latest()->get();
        return $alumni;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createAlumni(array $data): Alumni
    {
        /** @var Alumni $alumnus */
        $alumnus = Alumni::create($data);
        return $alumnus;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateAlumni(Alumni $alumnus, array $data): Alumni
    {
        $alumnus->update($data);
        return $alumnus;
    }

    /**
     * @return Collection<int, TracerStudy>
     */
    public function getTracerStudies(): Collection
    {
        /** @var Collection<int, TracerStudy> $studies */
        $studies = TracerStudy::with('alumni.student')->latest()->get();
        return $studies;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createTracerStudy(array $data): TracerStudy
    {
        /** @var TracerStudy $study */
        $study = TracerStudy::create($data);
        return $study;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateTracerStudy(TracerStudy $study, array $data): TracerStudy
    {
        $study->update($data);
        return $study;
    }
}
