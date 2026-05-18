<?php

namespace Modules\School\Http\Controllers\Api\Lms;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Lms\Course;
use Modules\School\Models\Student\Student;

class LmsFileController extends BaseController
{
    /**
     * Stream a private file after checking permissions.
     *
     * URL pattern: api/v1/lms/files/private/{path}
     */
    public function stream(Request $request, string $path): mixed
    {
        // 1. Basic Path Validation
        if (! Storage::disk('local')->exists($path)) {
            return $this->sendError('File not found.', [], 404);
        }

        // 2. Extract School & Course ID from path for permission check
        // Path format: lms/school_{id}/course_{id}/...
        preg_match('/lms\/school_(\d+)\/course_(\d+)\//', $path, $matches);

        if (count($matches) === 3) {
            $schoolId = (int) $matches[1];
            $courseId = (int) $matches[2];

            // 3. Permission Check
            $user = auth()->user();
            if (! $user) {
                return $this->sendError('Unauthorized.', [], 401);
            }

            // If Admin, allow all
            if ($user->isAtLeastRole('admin')) {
                return Storage::disk('local')->response($path);
            }

            // If Student, check enrollment
            /** @var Student|null $student */
            $student = Student::where('user_id', $user->id)->first();
            if ($student) {
                $isEnrolled = Course::where('id', $courseId)
                    ->whereHas('enrollments', fn ($q) => $q->where('student_id', $student->id))
                    ->exists();

                if ($isEnrolled) {
                    return Storage::disk('local')->response($path);
                }
            }

            // If Teacher, check if they own the course (future implementation)
            // For now, if they are teacher in that school, we might allow it
            if ($user->isAtLeastRole('teacher')) {
                // TODO: Add strict course ownership check
                return Storage::disk('local')->response($path);
            }
        }

        return $this->sendError('Access denied.', [], 403);
    }
}
