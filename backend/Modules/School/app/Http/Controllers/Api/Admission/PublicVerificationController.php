<?php

namespace Modules\School\Http\Controllers\Api\Admission;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Admission\DocumentVerification;
use Modules\School\Models\Student\Student;


class PublicVerificationController extends BaseController
{
    public function verify(string $hash): \Illuminate\Http\JsonResponse
    {
        $verification = DocumentVerification::where('hash', $hash)->first();

        if (!$verification) {
            return $this->sendError('Document not found or invalid.', [], 404);
        }

        $data = [
            'type' => $verification->document_type,
            'verified_at' => $verification->created_at?->toDateTimeString() ?? date('Y-m-d H:i:s'),
            'details' => []
        ];

        if (in_array($verification->document_type, ['id_card', 'skl', 'profile'])) {
            $student = Student::with(['school', 'department', 'level'])->find($verification->document_id);
            if ($student) {
                $data['details'] = [
                    'Name' => $student->full_name,
                    'NISN' => $student->nisn,
                    'School' => $student->school->name,
                    'Department' => $student->department?->name,
                    'Level' => $student->level->name,
                    'Status' => 'Authentic Document'
                ];
            }
        }

        return $this->sendResponse($data, 'Document authenticity verified successfully.');
    }
}
