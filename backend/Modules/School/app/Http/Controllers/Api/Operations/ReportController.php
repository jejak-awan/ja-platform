<?php

namespace Modules\School\Http\Controllers\Api\Operations;

use Illuminate\Http\Request;
use Modules\School\Http\Controllers\Api\Common\BaseController;
use Modules\School\Models\Student\Student;

use Modules\School\Models\Institution\School;
use Mpdf\Mpdf;

class ReportController extends BaseController
{
    public function studentProfile(string $id): \Illuminate\Http\Response
    {
        $this->authorize('view', Student::class);
        /** @var Student $student */
        $student = Student::with(['department', 'school'])->findOrFail($id);
        $school = $student->school;

        $verificationUrl = $student->getVerificationUrl('profile');
        $html = view('school::reports.student_profile', ['student' => $student, 'school' => $school, 'verificationUrl' => $verificationUrl])->render();

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
        ]);

        $mpdf->WriteHTML($html);
        
        return response((string)$mpdf->Output('student_profile.pdf', 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="student_profile.pdf"');
    }



    public function studentIdCard(string $id): \Illuminate\Http\Response
    {
        $this->authorize('view', Student::class);
        /** @var Student $student */
        $student = Student::with(['school', 'activeEnrollment'])->findOrFail($id);
        $school = $student->school;

        $verificationUrl = $student->getVerificationUrl('id_card');
        $html = view('school::reports.id_card', ['student' => $student, 'school' => $school, 'verificationUrl' => $verificationUrl])->render();

        $mpdf = new Mpdf([
            'format' => [86, 54], // ID-1 standard size 85.60 × 53.98 mm
            'margin_left' => 0,
            'margin_right' => 0,
            'margin_top' => 0,
            'margin_bottom' => 0,
        ]);

        $mpdf->WriteHTML($html);

        return response((string)$mpdf->Output('id_card.pdf', 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="id_card.pdf"');
    }

    public function graduationCertificate(string $id): \Illuminate\Http\Response
    {
        $this->authorize('view', Student::class);
        /** @var Student $student */
        $student = Student::with(['school', 'department'])->findOrFail($id);
        $school = $student->school;

        $verificationUrl = $student->getVerificationUrl('skl');
        $html = view('school::reports.graduation_skl', ['student' => $student, 'school' => $school, 'verificationUrl' => $verificationUrl])->render();

        $mpdf = new Mpdf([
            'format' => 'A4',
            'margin_left' => 20,
            'margin_right' => 20,
            'margin_top' => 20,
            'margin_bottom' => 20,
        ]);

        $mpdf->WriteHTML($html);

        return response((string)$mpdf->Output('skl.pdf', 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="skl.pdf"');
    }
}
