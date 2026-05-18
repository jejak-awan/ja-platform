<?php

namespace Modules\School\Http\Controllers\Api\Operations;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Operations\DocumentTemplate;
use Modules\School\Models\Operations\GraduationResult;
use Modules\School\Models\Operations\GraduationSetting;
use Modules\School\Models\Student\Student;
use Modules\School\Services\Operations\DocumentService;
use Modules\School\Services\Student\StudentService;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GraduationController extends BaseController
{
    public function __construct(protected StudentService $studentService, protected DocumentService $documentService) {}

    /**
     * Get graduation results.
     */
    public function indexResults(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Student::class);

        $results = GraduationResult::with('student.level')
            ->when($request->year, fn ($q) => $q->where('graduation_year', $request->year))
            ->latest()
            ->paginate($request->integer('per_page', 50));

        return $this->sendResponse($results, 'Graduation results retrieved successfully.');
    }

    /**
     * Update/Create graduation result for a student.
     */
    public function updateResult(Request $request): JsonResponse
    {
        $this->authorize('update', Student::class);

        $validated = $request->validate([
            'student_id' => 'required|exists:sch_std_students,id',
            'status' => 'required|string|in:graduated,not_graduated,deferred',
            'graduation_year' => 'required|integer',
            'grades' => 'nullable|array',
            'certificate_number' => 'nullable|string',
            'published_at' => 'nullable|date',
        ]);

        $result = GraduationResult::updateOrCreate(
            ['student_id' => $validated['student_id'], 'graduation_year' => $validated['graduation_year']],
            $validated
        );

        return $this->sendResponse($result, 'Graduation result updated successfully.');
    }

    /**
     * Get students eligible for graduation.
     */
    public function eligibleStudents(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Student::class);

        $filters = $request->only(['level_id', 'department_id', 'search']);
        // Default to active students
        $filters['status'] = 'active';

        $students = $this->studentService->getStudentList($filters, $request->integer('per_page', 50));

        return $this->sendResponse($students, 'Eligible students retrieved successfully.');
    }

    /**
     * Batch update students to graduated.
     */
    public function batchGraduate(Request $request): JsonResponse
    {
        $this->authorize('update', Student::class);

        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:sch_std_students,id',
            'graduation_year' => 'required|integer',
            'status' => 'required|string|in:graduated,not_graduated,deferred',
        ]);

        $studentIds = (array) $validated['student_ids'];
        $year = (int) $validated['graduation_year'];
        $status = (string) $validated['status'];

        $count = 0;
        foreach ($studentIds as $id) {
            GraduationResult::updateOrCreate(
                ['student_id' => $id, 'graduation_year' => $year],
                ['status' => $status]
            );

            // Also update student status if graduated
            if ($status === 'graduated') {
                Student::where('id', $id)->update(['status' => 'graduated']);
            }
            $count++;
        }

        return $this->sendResponse(['updated_count' => $count], "Successfully updated {$count} students.");
    }

    /**
     * Public check for graduation status.
     */
    public function publicCheck(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string', // NISN or NIS
            'dob' => 'required|date',
        ]);

        $student = $this->studentService->findForGraduationCheck(
            $request->string('identifier')->toString(),
            $request->string('dob')->toString()
        );

        if (! $student instanceof Student) {
            return $this->sendError('Data siswa tidak ditemukan. Pastikan NISN/NIS dan Tanggal Lahir sudah benar.', [], 404);
        }

        $result = GraduationResult::where('student_id', $student->id)
            ->latest('graduation_year')
            ->first();

        // Retrieve Graduation Setting for the result's year or current year
        $year = $result ? $result->graduation_year : date('Y');
        $setting = GraduationSetting::where('graduation_year', $year)->first();

        if ($setting && ! $setting->is_open) {
            // Check if there is an announcement date and it is in the future
            if ($setting->announcement_date && now()->lt($setting->announcement_date)) {
                return $this->sendError('Pengumuman kelulusan belum dibuka.', [
                    'announcement_date' => $setting->announcement_date,
                ], 403);
            }

            if (! $setting->announcement_date) {
                return $this->sendError('Pengumuman kelulusan ditutup oleh admin.', [], 403);
            }
        }

        $status = $result ? $result->status : $student->status;

        return $this->sendResponse([
            'full_name' => $student->full_name,
            'nisn' => $student->nisn,
            'status' => $status,
            'is_graduated' => $status === 'graduated',
            'grades' => $result ? $result->grades : null,
            'certificate_url' => $status === 'graduated' ? url("/api/v1/public/graduation/certificate/{$student->id}") : null,
        ], 'Graduation status retrieved successfully.');
    }

    /**
     * Download certificate public.
     */
    public function downloadCertificatePublic(string $studentId): mixed
    {
        $student = Student::findOrFail($studentId);
        $result = GraduationResult::where('student_id', $studentId)
            ->where('status', 'graduated')
            ->latest('graduation_year')
            ->firstOrFail();

        $template = DocumentTemplate::where('school_id', $student->school_id)
            ->where('type', 'skl')
            ->where('is_active', true)
            ->first();

        if (! $template) {
            return $this->sendError('Template sertifikat belum diatur.', [], 404);
        }

        $data = array_merge($student->toArray(), [
            'graduation_year' => $result->graduation_year,
            'certificate_number' => $result->certificate_number,
            'grades' => $result->grades,
            'published_date' => $result->published_at ? $result->published_at->format('d F Y') : now()->format('d F Y'),
        ]);

        $pdfBinary = $this->documentService->generatePdf($template, $data);

        return response($pdfBinary)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="SKL_'.$student->nisn.'.pdf"');
    }

    /**
     * Import grades from CSV/Excel file.
     * Expected format: NISN | Subject1 | Subject2 | ...
     * First row is header with subject names.
     */
    public function importGrades(Request $request): JsonResponse
    {
        $this->authorize('update', Student::class);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
            'graduation_year' => 'required|integer',
        ]);

        $file = $request->file('file');
        $year = $request->integer('graduation_year');
        $ext = strtolower($file->getClientOriginalExtension());

        $rows = [];

        if (in_array($ext, ['csv', 'txt'])) {
            // Parse CSV natively
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle !== false) {
                while (($row = fgetcsv($handle, 0, ',')) !== false) {
                    // Also try semicolon delimiter if only 1 column
                    if (count($row) === 1 && is_string($row[0]) && str_contains($row[0], ';')) {
                        $row = str_getcsv($row[0], ';');
                    }
                    $rows[] = $row;
                }
                fclose($handle);
            }
        } elseif (in_array($ext, ['xlsx', 'xls'])) {
            // Use PhpSpreadsheet if available
            if (! class_exists(IOFactory::class)) {
                return $this->sendError('PhpSpreadsheet is required for Excel files. Please use CSV format instead.', [], 422);
            }
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $rows[] = $rowData;
            }
        }

        if (count($rows) < 2) {
            return $this->sendError('File must contain a header row and at least one data row.', [], 422);
        }

        // First row = headers: NISN, Subject1, Subject2, ...
        $headers = array_map(fn ($v) => is_string($v) ? trim($v) : (is_scalar($v) ? (string) $v : ''), $rows[0]);
        array_shift($rows); // Remove header

        $updated = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowZero = $row[0] ?? '';
            $nisn = is_string($rowZero) ? trim($rowZero) : (is_scalar($rowZero) ? trim((string) $rowZero) : '');
            if ($nisn === '' || $nisn === '0') {
                continue;
            }

            $student = Student::where('nisn', $nisn)->first();
            if (! $student) {
                $errors[] = 'Row '.($index + 2).": NISN '{$nisn}' not found.";

                continue;
            }

            // Build grades array from columns
            $grades = [];
            $counter = count($headers);
            for ($i = 1; $i < $counter; $i++) {
                $subjectName = $headers[$i];
                $value = $row[$i] ?? null;
                if (! empty($subjectName) && $value !== null && $value !== '') {
                    $grades[$subjectName] = is_numeric($value) ? (float) $value : $value;
                }
            }

            GraduationResult::updateOrCreate(
                ['student_id' => $student->id, 'graduation_year' => $year],
                ['grades' => $grades]
            );
            $updated++;
        }

        return $this->sendResponse([
            'imported' => $updated,
            'errors' => $errors,
        ], "Successfully imported grades for {$updated} students.");
    }
}
