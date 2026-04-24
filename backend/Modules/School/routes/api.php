<?php

use Illuminate\Support\Facades\Route;
use Modules\School\Http\Controllers\Api\Academic\AcademicController;
use Modules\School\Http\Controllers\Api\Academic\GradeController;
use Modules\School\Http\Controllers\Api\Admission\AdmissionController;
use Modules\School\Http\Controllers\Api\Logistics\FacilityController;
use Modules\School\Http\Controllers\Api\Finance\FinanceController;
use Modules\School\Http\Controllers\Api\Institution\SchoolController;
use Modules\School\Http\Controllers\Api\Institution\SchoolLevelController;
use Modules\School\Http\Controllers\Api\HR\StaffController;
use Modules\School\Http\Controllers\Api\HR\StaffAdvancedController;
use Modules\School\Http\Controllers\Api\HR\CareerController;
use Modules\School\Http\Controllers\Api\Student\StudentController;
use Modules\School\Http\Controllers\Api\Student\StudentPortalController;
use Modules\School\Http\Controllers\Api\Logistics\HostelController;
use Modules\School\Http\Controllers\Api\Logistics\TransportController;
use Modules\School\Http\Controllers\Api\Logistics\InventoryController;
use Modules\School\Http\Controllers\Api\Logistics\SarprasController;
use Modules\School\Http\Controllers\Api\Operations\OperationController;
use Modules\School\Http\Controllers\Api\Operations\AuditLogController;
use Modules\School\Http\Controllers\Api\Operations\AnalyticsController;
use Modules\School\Http\Controllers\Api\Operations\ReportController;
use Modules\School\Http\Controllers\Api\Operations\AdminExtensionController;
use Modules\School\Http\Controllers\Api\Lms\LmsController;
use Modules\School\Http\Controllers\Api\Lms\CbtController;
use Modules\School\Http\Controllers\Api\Osis\OsisController;
use Modules\School\Http\Controllers\Api\Teacher\TeacherPortalController;

Route::prefix('v1')->group(function () {
    Route::prefix('admin')->middleware([
        'auth:sanctum',
        'throttle:admin',
        \Modules\School\Http\Middleware\LevelContextMiddleware::class,
    ])->group(function () {
        Route::middleware('permission:view schools|manage schools|create schools|edit schools|delete schools')->group(function () {
            Route::get('school/stats', [SchoolController::class, 'stats']);
            Route::get('school/setup-status', [SchoolController::class, 'checkSetupStatus']);
            Route::get('school/default', [SchoolController::class, 'defaultSchool']);
            Route::apiResource('school', SchoolController::class);

            Route::prefix('institution')->group(function () {
                Route::get('/', [SchoolController::class, 'index']);
                Route::get('stats', [SchoolController::class, 'stats']);
                Route::get('setup-status', [SchoolController::class, 'checkSetupStatus']);
                Route::post('logo', [SchoolController::class, 'updateLogo']);
                Route::apiResource('levels', SchoolLevelController::class);
            });
        });

        Route::middleware('permission:view students|manage students|create students|edit students|delete students')->group(function () {
            Route::apiResource('students', StudentController::class);
        });

        Route::middleware('permission:view staff|manage staff|create staff|edit staff|delete staff')->group(function () {
            Route::apiResource('staff', StaffController::class);
        });

        Route::middleware('permission:view academic|manage academic|manage curriculum|manage schedule|grade assignments|input journal|manage question bank')->group(function () {
            Route::prefix('academic')->group(function () {
                Route::get('departments', [AcademicController::class, 'departments']);
                Route::post('departments', [AcademicController::class, 'storeDepartment']);
                Route::put('departments/{id}', [AcademicController::class, 'updateDepartment']);
                Route::delete('departments/{id}', [AcademicController::class, 'destroyDepartment']);

                Route::get('overview', [AcademicController::class, 'overview']);

                Route::get('years', [AcademicController::class, 'years']);
                Route::post('years', [AcademicController::class, 'storeYear']);
                Route::put('years/{id}', [AcademicController::class, 'updateYear']);
                Route::delete('years/{id}', [AcademicController::class, 'destroyYear']);

                Route::get('semesters', [AcademicController::class, 'semesters']);
                Route::post('semesters', [AcademicController::class, 'storeSemester']);
                Route::put('semesters/{id}', [AcademicController::class, 'updateSemester']);
                Route::delete('semesters/{id}', [AcademicController::class, 'destroySemester']);

                Route::get('subjects', [AcademicController::class, 'subjects']);
                Route::post('subjects', [AcademicController::class, 'storeSubject']);
                Route::put('subjects/{id}', [AcademicController::class, 'updateSubject']);
                Route::delete('subjects/{id}', [AcademicController::class, 'destroySubject']);

                Route::get('study-groups', [AcademicController::class, 'studyGroups']);
                Route::post('study-groups', [AcademicController::class, 'storeStudyGroup']);
                Route::put('study-groups/{id}', [AcademicController::class, 'updateStudyGroup']);
                Route::delete('study-groups/{id}', [AcademicController::class, 'destroyStudyGroup']);

                Route::get('study-groups/{groupId}/members', [AcademicController::class, 'groupMembers']);
                Route::post('study-groups/{groupId}/members', [AcademicController::class, 'addGroupMember']);
                Route::delete('study-groups/{groupId}/members/{studentId}', [AcademicController::class, 'removeGroupMember']);

                Route::get('schedules', [AcademicController::class, 'schedules']);
                Route::post('schedules', [AcademicController::class, 'storeSchedule']);
                Route::put('schedules/{id}', [AcademicController::class, 'updateSchedule']);
                Route::delete('schedules/{id}', [AcademicController::class, 'destroySchedule']);

                Route::get('journals', [AcademicController::class, 'journals']);
                Route::post('journals', [AcademicController::class, 'storeJournal']);
                Route::put('journals/{id}', [AcademicController::class, 'updateJournal']);
                Route::delete('journals/{id}', [AcademicController::class, 'destroyJournal']);

                Route::get('grades/{studentId}', [GradeController::class, 'getStudentGrades']);
                Route::post('grades', [GradeController::class, 'saveGrade']);
                Route::post('grades/bulk', [GradeController::class, 'bulkSaveGrades']);
            });
        });

        // LMS & Admin Extensions
        Route::middleware('permission:view lms|manage lms|create lms|edit lms|delete lms|manage question bank')->group(function () {
            Route::prefix('lms')->group(function () {
                Route::get('overview', [LmsController::class, 'overview']);

                Route::get('banks', [LmsController::class, 'questionBanks']);
                Route::post('banks', [LmsController::class, 'storeQuestionBank']);
                Route::put('banks/{id}', [LmsController::class, 'updateQuestionBank']);
                Route::delete('banks/{id}', [LmsController::class, 'destroyQuestionBank']);

                Route::get('exams', [LmsController::class, 'exams']);
                Route::post('exams', [LmsController::class, 'storeExam']);
                Route::put('exams/{id}', [LmsController::class, 'updateExam']);
                Route::delete('exams/{id}', [LmsController::class, 'destroyExam']);
                Route::get('exams/{id}/questions', [LmsController::class, 'examQuestions']);
                Route::post('exams/{id}/questions/sync', [LmsController::class, 'syncExamQuestions']);

                Route::get('results', [LmsController::class, 'examResults']);
                Route::post('results', [LmsController::class, 'storeExamResult']);
                Route::put('results/{id}', [LmsController::class, 'updateExamResult']);
                Route::delete('results/{id}', [LmsController::class, 'destroyExamResult']);

                Route::get('banks/{bankId}/questions', [LmsController::class, 'questions']);
                Route::post('banks/{bankId}/questions', [LmsController::class, 'storeQuestion']);
                Route::post('questions/{id}', [LmsController::class, 'updateQuestion']);
                Route::delete('questions/{id}', [LmsController::class, 'destroyQuestion']);

                Route::get('courses', [LmsController::class, 'courses']);
                Route::post('courses', [LmsController::class, 'storeCourse']);
                Route::get('courses/{id}', [LmsController::class, 'showCourse']);
                Route::put('courses/{id}', [LmsController::class, 'updateCourse']);
                Route::delete('courses/{id}', [LmsController::class, 'destroyCourse']);

                // Teacher Course Management
                Route::get('manage/stats', [LmsController::class, 'teacherStats']);
                Route::get('manage/courses', [LmsController::class, 'teacherCourses']);
                Route::get('manage/courses/{id}/monitoring', [LmsController::class, 'courseMonitoring']);
            });
        });

        // CBT (Computer Based Testing)
        Route::middleware('permission:view lms|manage lms')->group(function () {
            Route::prefix('cbt')->group(function () {
                Route::get('exams', [CbtController::class, 'exams']);
                Route::get('sessions', [CbtController::class, 'indexSessions']);
                Route::post('sessions', [CbtController::class, 'storeSession']);
                Route::put('sessions/{id}', [CbtController::class, 'updateSession']);
                Route::delete('sessions/{id}', [CbtController::class, 'destroySession']);
                Route::post('sessions/{id}/token', [CbtController::class, 'generateToken']);
            });
        });

        // Operations (split middleware: attendance vs student affairs vs visitors)
        Route::prefix('operations')->group(function () {
            Route::middleware('permission:view attendance|manage attendance|view students|manage students')->group(function () {
                Route::get('attendance/overview', [OperationController::class, 'attendanceOverview']);
                Route::get('attendance', [OperationController::class, 'attendances']);
                Route::post('attendance', [OperationController::class, 'storeAttendance']);
                Route::put('attendance/{id}', [OperationController::class, 'updateAttendance']);
                Route::delete('attendance/{id}', [OperationController::class, 'destroyAttendance']);
            });

            Route::middleware('permission:view student affairs|manage student affairs|view students|manage students')->group(function () {
                Route::get('violations', [OperationController::class, 'violations']);
                Route::get('violations/points/{studentId}', [OperationController::class, 'studentViolationPoints']);
                Route::post('violations', [OperationController::class, 'storeViolation']);
                Route::put('violations/{id}', [OperationController::class, 'updateViolation']);
                Route::delete('violations/{id}', [OperationController::class, 'destroyViolation']);

                Route::get('achievements', [OperationController::class, 'achievements']);
                Route::post('achievements', [OperationController::class, 'storeAchievement']);
                Route::put('achievements/{id}', [OperationController::class, 'updateAchievement']);
                Route::delete('achievements/{id}', [OperationController::class, 'destroyAchievement']);

                Route::get('counseling', [OperationController::class, 'counselingRecords']);
                Route::post('counseling', [OperationController::class, 'storeCounselingRecord']);
                Route::put('counseling/{id}', [OperationController::class, 'updateCounselingRecord']);
                Route::delete('counseling/{id}', [OperationController::class, 'destroyCounselingRecord']);
            });

            Route::middleware('permission:view visitors|manage visitors')->group(function () {
                Route::get('visitors', [OperationController::class, 'visitors']);
                Route::post('visitors/check-in', [OperationController::class, 'checkInVisitor']);
                Route::post('visitors/{id}/check-out', [OperationController::class, 'checkOutVisitor']);
            });
        });

        // Sarpras
        Route::middleware('permission:view sarpras|manage sarpras|manage inventory')->group(function () {
            Route::prefix('sarpras')->group(function () {
                Route::get('overview', [FacilityController::class, 'overview']);

                Route::get('land', [FacilityController::class, 'landAssets']);
                Route::post('land', [FacilityController::class, 'storeLand']);
                Route::put('land/{id}', [FacilityController::class, 'updateLand']);

                Route::get('building', [FacilityController::class, 'buildings']);
                Route::post('building', [FacilityController::class, 'storeBuilding']);
                Route::put('building/{id}', [FacilityController::class, 'updateBuilding']);

                Route::get('room', [FacilityController::class, 'rooms']);
                Route::post('room', [FacilityController::class, 'storeRoom']);
                Route::put('room/{id}', [FacilityController::class, 'updateRoom']);

                Route::get('asset', [FacilityController::class, 'assets']);
                Route::get('assets', [FacilityController::class, 'assets']);
                Route::post('assets', [FacilityController::class, 'storeAsset']);
                Route::put('assets/{id}', [FacilityController::class, 'updateAsset']);

                Route::delete('{type}/{id}', [FacilityController::class, 'destroy']);

                Route::get('tickets', [SarprasController::class, 'tickets']);
                Route::post('tickets', [SarprasController::class, 'storeTicket']);
                Route::put('tickets/{id}', [SarprasController::class, 'updateTicket']);
                Route::delete('tickets/{id}', [SarprasController::class, 'destroyTicket']);
            });
        });

        // Finance
        Route::middleware('permission:view school finance|manage school finance')->group(function () {
            Route::prefix('finance')->group(function () {
                Route::get('summary', [FinanceController::class, 'summary']);
                Route::get('fee-types', [FinanceController::class, 'feeTypes']);
                Route::post('fee-types', [FinanceController::class, 'storeFeeType']);
                Route::put('fee-types/{id}', [FinanceController::class, 'updateFeeType']);
                Route::delete('fee-types/{id}', [FinanceController::class, 'destroyFeeType']);

                Route::get('bills', [FinanceController::class, 'bills']);
                Route::post('bills/generate', [FinanceController::class, 'generateBills']);
                Route::post('bills/{id}/pay', [FinanceController::class, 'payBill']);

                Route::get('expenses', [FinanceController::class, 'expenses']);
                Route::post('expenses', [FinanceController::class, 'storeExpense']);
                Route::put('expenses/{id}', [FinanceController::class, 'updateExpense']);
                Route::delete('expenses/{id}', [FinanceController::class, 'destroyExpense']);

                Route::get('budgets', [FinanceController::class, 'budgets']);
                Route::post('budgets', [FinanceController::class, 'storeBudget']);
            });
        });

        // OSIS Management
        Route::middleware('permission:view osis|manage osis')->group(function () {
            Route::prefix('osis')->group(function () {
                Route::get('programs', [OsisController::class, 'programs']);
                Route::post('programs', [OsisController::class, 'storeProgram']);
                Route::put('programs/{id}', [OsisController::class, 'updateProgram']);
                Route::delete('programs/{id}', [OsisController::class, 'destroyProgram']);

                Route::get('members', [OsisController::class, 'members']);
                Route::post('members', [OsisController::class, 'storeMember']);

                Route::get('finances', [OsisController::class, 'finances']);
                Route::post('finances', [OsisController::class, 'storeFinance']);

                Route::get('suggestions', [OsisController::class, 'suggestions']);
                Route::patch('suggestions/{id}', [OsisController::class, 'updateSuggestion']);
            });
        });

        // Admission (PPDB)
        Route::middleware('permission:view admission|manage admission')->group(function () {
            Route::prefix('admission')->group(function () {
                Route::get('enrollments', [AdmissionController::class, 'index']);
                Route::post('enrollments', [AdmissionController::class, 'store']);
                Route::get('enrollments/{id}', [AdmissionController::class, 'show']);
                Route::patch('enrollments/{id}/status', [AdmissionController::class, 'updateStatus']);
                Route::post('enrollments/{id}/admit', [AdmissionController::class, 'admit']);
                Route::patch('documents/{id}/verify', [AdmissionController::class, 'verifyDocument']);
            });
        });

        // Teacher Portal
        Route::middleware('permission:view lms|manage lms|view academic|manage academic|grade assignments|input journal')->group(function () {
            Route::prefix('teacher')->group(function () {
                Route::get('dashboard-stats', [TeacherPortalController::class, 'dashboardStats']);
                Route::get('schedules', [TeacherPortalController::class, 'schedules']);
                Route::get('recent-journals', [TeacherPortalController::class, 'recentJournals']);
            });
        });

        // Advanced HR (Shifts & Leave)
        Route::middleware('permission:view staff|manage staff|manage payroll')->group(function () {
            Route::prefix('hr')->group(function () {
                Route::get('shifts', [StaffAdvancedController::class, 'shifts']);
                Route::post('shifts', [StaffAdvancedController::class, 'storeShift']);
                Route::put('shifts/{id}', [StaffAdvancedController::class, 'updateShift']);
                Route::delete('shifts/{id}', [StaffAdvancedController::class, 'destroyShift']);

                Route::get('leaves', [StaffAdvancedController::class, 'leaveRequests']);
                Route::post('leaves', [StaffAdvancedController::class, 'storeLeaveRequest']);
                Route::patch('leaves/{id}/status', [StaffAdvancedController::class, 'updateLeaveStatus']);

                Route::get('salary-structures', [StaffAdvancedController::class, 'salaryStructures']);
                Route::post('salary-structures', [StaffAdvancedController::class, 'storeSalaryStructure']);

                Route::get('payrolls', [StaffAdvancedController::class, 'payrolls']);
                Route::post('payrolls/generate', [StaffAdvancedController::class, 'generatePayroll']);
            });
        });

        // Reports & Export
        Route::middleware('permission:view students|manage students|view school finance|manage school finance')->group(function () {
            Route::prefix('reports')->group(function () {
                Route::get('students/{id}/pdf', [ReportController::class, 'studentProfile']);
                Route::get('payments/{id}/pdf', [ReportController::class, 'paymentReceipt']);
                Route::get('students/{id}/id-card', [ReportController::class, 'studentIdCard']);
                Route::get('students/{id}/skl', [ReportController::class, 'graduationCertificate']);
            });
        });

        Route::middleware('permission:view school extensions|manage school extensions')->group(function () {
            Route::prefix('extensions')->group(function () {
                Route::get('overview', [AdminExtensionController::class, 'overview']);
                Route::get('logs', [AuditLogController::class, 'index']);
                Route::get('logs/{id}', [AuditLogController::class, 'show']);

                Route::get('library', [AdminExtensionController::class, 'libraryBooks']);
                Route::post('library', [AdminExtensionController::class, 'storeLibraryBook']);
                Route::put('library/{id}', [AdminExtensionController::class, 'updateLibraryBook']);
                Route::delete('library/{id}', [AdminExtensionController::class, 'destroyLibraryBook']);

                Route::get('library-circulations', [AdminExtensionController::class, 'libraryCirculations']);
                Route::post('library-circulations', [AdminExtensionController::class, 'storeLibraryCirculation']);
                Route::put('library-circulations/{id}', [AdminExtensionController::class, 'updateLibraryCirculation']);
                Route::delete('library-circulations/{id}', [AdminExtensionController::class, 'destroyLibraryCirculation']);

                Route::get('uks', [AdminExtensionController::class, 'uksVisits']);
                Route::post('uks', [AdminExtensionController::class, 'storeUksVisit']);
                Route::put('uks/{id}', [AdminExtensionController::class, 'updateUksVisit']);
                Route::delete('uks/{id}', [AdminExtensionController::class, 'destroyUksVisit']);

                Route::get('guest', [AdminExtensionController::class, 'guestLogs']);
                Route::post('guest', [AdminExtensionController::class, 'storeGuestLog']);
                Route::put('guest/{id}', [AdminExtensionController::class, 'updateGuestLog']);
                Route::delete('guest/{id}', [AdminExtensionController::class, 'destroyGuestLog']);

                Route::get('alumni', [AdminExtensionController::class, 'alumni']);
                Route::post('alumni', [AdminExtensionController::class, 'storeAlumni']);
                Route::put('alumni/{id}', [AdminExtensionController::class, 'updateAlumni']);
                Route::delete('alumni/{id}', [AdminExtensionController::class, 'destroyAlumni']);

                Route::get('tracer-studies', [AdminExtensionController::class, 'tracerStudies']);
                Route::post('tracer-studies', [AdminExtensionController::class, 'storeTracerStudy']);
                Route::put('tracer-studies/{id}', [AdminExtensionController::class, 'updateTracerStudy']);
                Route::delete('tracer-studies/{id}', [AdminExtensionController::class, 'destroyTracerStudy']);
            });
        });

        // Logistics: Hostel
        Route::middleware('permission:view sarpras|manage sarpras|manage logistics|manage inventory')->group(function () {
            Route::prefix('logistics/hostel')->group(function () {
                Route::get('blocks', [HostelController::class, 'blocks']);
                Route::post('blocks', [HostelController::class, 'storeBlock']);
                Route::get('blocks/{blockId}/rooms', [HostelController::class, 'rooms']);
                Route::post('blocks/{blockId}/rooms', [HostelController::class, 'storeRoom']);
                Route::get('rooms/{roomId}/beds', [HostelController::class, 'beds']);
                Route::post('allocate', [HostelController::class, 'allocate']);
                Route::post('release/{id}', [HostelController::class, 'release']);
            });

            Route::prefix('logistics/transport')->group(function () {
                Route::get('vehicles', [TransportController::class, 'vehicles']);
                Route::post('vehicles', [TransportController::class, 'storeVehicle']);
                Route::get('routes', [TransportController::class, 'routes']);
                Route::post('routes', [TransportController::class, 'storeRoute']);
                Route::get('registrations', [TransportController::class, 'registrations']);
                Route::post('register', [TransportController::class, 'registerStudent']);
            });

            Route::prefix('logistics/inventory')->group(function () {
                Route::get('categories', [InventoryController::class, 'categories']);
                Route::get('items', [InventoryController::class, 'items']);
                Route::post('items', [InventoryController::class, 'storeItem']);
                Route::get('transactions', [InventoryController::class, 'transactions']);
                Route::post('adjust', [InventoryController::class, 'adjustStock']);
            });

            Route::prefix('logistics/career')->group(function () {
                Route::get('vacancies', [CareerController::class, 'vacancies']);
                Route::post('vacancies', [CareerController::class, 'storeVacancy']);
                Route::get('applications', [CareerController::class, 'applications']);
                Route::post('apply', [CareerController::class, 'apply']);
            });
        });

        // Analytics
        Route::middleware('permission:view analytics|view schools|manage schools|view school finance|manage school finance')->group(function () {
            Route::prefix('analytics')->group(function () {
                Route::get('summary', [AnalyticsController::class, 'status']);
                Route::get('summary-full', [AnalyticsController::class, 'executiveSummary']);
                Route::get('financial-charts', [AnalyticsController::class, 'financialCharts']);
            });
        });
    });

    // Public Verification (No Auth)
    Route::get('public/verify/{hash}', [\Modules\School\Http\Controllers\Api\Admission\PublicVerificationController::class, 'verify']);

    // Student Portal
    Route::prefix('student')->middleware(['auth:sanctum'])->group(function () {
        Route::get('dashboard', [StudentPortalController::class, 'dashboard']);
        Route::get('bills', [StudentPortalController::class, 'bills']);
        Route::get('attendance', [StudentPortalController::class, 'attendance']);
        Route::get('grades', [StudentPortalController::class, 'grades']);
        Route::get('schedule', [StudentPortalController::class, 'schedule']);

        // LMS Student Routes
        Route::prefix('lms')->group(function () {
            Route::get('courses', [LmsController::class, 'courses']);
            Route::get('courses/{id}', [LmsController::class, 'showCourse']);
            Route::post('courses/{courseId}/enroll', [LmsController::class, 'enroll']);
            Route::get('my-enrollment/{courseId}', [LmsController::class, 'myEnrollment']);
            Route::post('enrollments/{enrollmentId}/lessons/{lessonId}/complete', [LmsController::class, 'completeLesson']);
        });
    });
});
