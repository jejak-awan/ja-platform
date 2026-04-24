import type { RouteRecordRaw } from 'vue-router';

const schoolRoutes: RouteRecordRaw[] = [
    {
        path: 'school-dashboard',
        name: 'schools.dashboard',
        component: () => import('@/modules/School/views/admin/Dashboard.vue'),
        meta: { permission: 'view schools' },
    },
    {
        path: 'schools',
        name: 'schools.index',
        component: () => import('@/modules/School/views/admin/institution/InstitutionTabs.vue'),
        meta: { permission: 'view schools' },
    },
    {
        path: 'schools/create',
        name: 'schools.create',
        component: () => import('@/modules/School/views/admin/institution/InstitutionCreate.vue'),
        meta: { permission: 'create schools', requiresSuperAdmin: true },
    },
    {
        path: 'schools/:id/edit',
        name: 'schools.edit',
        component: () => import('@/modules/School/views/admin/institution/InstitutionEdit.vue'),
        meta: { permission: 'edit schools', requiresSuperAdmin: true },
    },

    {
        path: 'schools/organization',
        name: 'schools.organization',
        component: () => import('@/modules/School/views/admin/institution/OrganizationStructure.vue'),
        meta: { permission: 'view schools' },
    },
    {
        path: 'students',
        name: 'students.index',
        component: () => import('@/modules/School/views/admin/students/Index.vue'),
        meta: { permission: 'view students' },
    },
    {
        path: 'students/create',
        name: 'students.create',
        component: () => import('@/modules/School/views/admin/students/Create.vue'),
        meta: { permission: 'create students' },
    },
    {
        path: 'students/:id/edit',
        name: 'students.edit',
        component: () => import('@/modules/School/views/admin/students/Edit.vue'),
        meta: { permission: 'edit students' },
    },
    {
        path: 'students/:id',
        name: 'students.show',
        component: () => import('@/modules/School/views/admin/students/Show.vue'),
        meta: { permission: 'view students' },
    },
    {
        path: 'staff',
        name: 'staff.index',
        component: () => import('@/modules/School/views/admin/hr/staff/Index.vue'),
        meta: { permission: 'view staff' },
    },
    {
        path: 'staff/create',
        name: 'staff.create',
        component: () => import('@/modules/School/views/admin/hr/staff/Create.vue'),
        meta: { permission: 'create staff' },
    },
    {
        path: 'staff/:id/edit',
        name: 'staff.edit',
        component: () => import('@/modules/School/views/admin/hr/staff/Edit.vue'),
        meta: { permission: 'edit staff' },
    },
    {
        path: 'staff/:id',
        name: 'staff.show',
        component: () => import('@/modules/School/views/admin/hr/staff/Show.vue'),
        meta: { permission: 'view staff' },
    },
    {
        path: 'sarpras',
        name: 'sarpras.index',
        component: () => import('@/modules/School/views/admin/logistics/assets/Index.vue'),
        meta: { permission: 'view sarpras' },
    },
    {
        path: 'academic',
        name: 'academic.index',
        component: () => import('@/modules/School/views/admin/academic/Index.vue'),
        meta: { permission: 'view academic' },
    },
    {
        path: 'attendance',
        name: 'attendance.index',
        component: () => import('@/modules/School/views/admin/students/operations/Attendance.vue'),
        meta: { permission: 'view attendance' },
    },
    {
        path: 'visitors',
        name: 'visitors.index',
        component: () => import('@/modules/School/views/admin/students/operations/VisitorLog.vue'),
        meta: { permission: 'view visitors' },
    },
    {
        path: 'student-affairs',
        name: 'student-affairs.index',
        component: () => import('@/modules/School/views/admin/students/operations/StudentAffairs.vue'),
        meta: { permission: 'view student affairs' },
    },
    {
        path: 'lms',
        name: 'lms.index',
        component: () => import('@/modules/School/views/admin/lms/management/CourseManager.vue'),
        meta: { permission: 'view lms' },
    },
    {
        path: 'lms/question-bank',
        name: 'admin.lms.question-bank',
        component: () => import('@/modules/School/views/admin/lms/QuestionBankManager.vue'),
        meta: { permission: 'view lms' },
    },
    {
        path: 'lms/manage',
        name: 'admin.lms.manage',
        redirect: { name: 'lms.index' }
    },
    {
        path: 'lms/manage/editor/:id?',
        name: 'admin.lms.manage.editor',
        component: () => import('@/modules/School/views/admin/lms/management/CourseEditor.vue'),
        meta: { permission: 'view lms' },
    },
    {
        path: 'lms/manage/:id/monitoring',
        name: 'admin.lms.manage.monitoring',
        component: () => import('@/modules/School/views/admin/lms/management/EnrollmentMonitor.vue'),
        meta: { permission: 'view lms' },
    },
    {
        path: 'cbt/manage',
        name: 'admin.cbt.manage',
        component: () => import('@/modules/School/views/admin/cbt/SessionManager.vue'),
        meta: { permission: 'view lms' },
    },
    {
        path: 'extensions',
        name: 'extensions.index',
        component: () => import('@/modules/School/views/admin/extensions/Index.vue'),
        meta: { permission: 'view school extensions' },
    },
    {
        path: 'finance',
        name: 'finance.index',
        component: () => import('@/modules/School/views/admin/finance/Index.vue'),
        meta: { title: 'Keuangan Sekolah', permission: 'view school finance' }
      },
      {
        path: 'admission',
        name: 'admission.index',
        component: () => import('@/modules/School/views/admin/admission/Index.vue'),
        meta: { title: 'PPDB Dashboard', permission: 'view admission' }
      },
      {
        path: 'admission/:id',
        name: 'admission.show',
        component: () => import('@/modules/School/views/admin/admission/Show.vue'),
        meta: { title: 'Detail Pendaftar', permission: 'view admission' }
      },
      {
        path: 'hr',
        name: 'hr.index',
        component: () => import('@/modules/School/views/admin/hr/Index.vue'),
        meta: { title: 'Manajemen HR', permission: 'view staff' }
      },
      {
        path: 'settings/logs',
        name: 'settings.logs',
        component: () => import('@/modules/School/views/admin/settings/AuditLogs.vue'),
        meta: { title: 'Audit Logs', permission: 'view logs' }
      },
      {
        path: 'public/verify/:hash',
        name: 'schools.verify',
        component: () => import('@/modules/School/views/public/Verification.vue'),
        meta: { public: true }
      },
      {
        path: 'logistics',
        name: 'logistics.index',
        component: () => import('@/modules/School/views/admin/logistics/LogisticsTabs.vue'),
        meta: { title: 'Logistik Sekolah', permission: 'view sarpras' }
      },
      {
        path: 'osis',
        name: 'osis.index',
        component: () => import('@/modules/School/views/admin/osis/Index.vue'),
        meta: { title: 'Manajemen OSIS', permission: 'view osis' }
      },
      {
        path: 'alumni',
        name: 'alumni.index',
        component: () => import('@/modules/School/views/admin/alumni/Index.vue'),
        meta: { title: 'Manajemen Alumni', permission: 'view students' }
      },
      
      // Student Portal
      {
        path: 'student/dashboard',
        name: 'student.dashboard',
        component: () => import('@/modules/School/views/student/Dashboard.vue'),
        meta: { title: 'Dashboard Siswa', requiresAuth: true }
      },
      {
        path: 'student/bills',
        name: 'student.bills',
        component: () => import('@/modules/School/views/student/Bills.vue'),
        meta: { title: 'Tagihan & Pembayaran', requiresAuth: true }
      },
      {
        path: 'student/grades',
        name: 'student.grades',
        component: () => import('@/modules/School/views/student/Grades.vue'),
        meta: { title: 'Nilai & E-Rapor', requiresAuth: true }
      },
      {
        path: 'student/schedule',
        name: 'student.schedule',
        component: () => import('@/modules/School/views/student/Schedule.vue'),
        meta: { title: 'Jadwal Mingguan', requiresAuth: true }
      },
      {
        path: 'student/lms',
        name: 'student.lms.index',
        component: () => import('@/modules/School/views/student/Lms/CourseIndex.vue'),
        meta: { title: 'Learning Center', requiresAuth: true }
      },
      {
        path: 'student/lms/:id',
        name: 'student.lms.course',
        component: () => import('@/modules/School/views/student/Lms/CourseDetail.vue'),
        meta: { title: 'Course Details', requiresAuth: true }
      },
      {
        path: 'student/lms/:courseId/lessons/:lessonId',
        name: 'student.lms.player',
        component: () => import('@/modules/School/views/student/Lms/LessonPlayer.vue'),
        meta: { title: 'Lesson Player', requiresAuth: true, layout: 'empty' }
      },

      // Teacher Portal
      {
        path: 'teacher/dashboard',
        name: 'teacher.dashboard',
        component: () => import('@/modules/School/views/teacher/Dashboard.vue'),
        meta: { title: 'Dashboard Guru', permission: 'view teacher portal' }
      },
      {
        path: 'teacher/schedule',
        name: 'teacher.schedule',
        component: () => import('@/modules/School/views/teacher/Schedule.vue'),
        meta: { title: 'Jadwal Mengajar', permission: 'view teacher portal' }
      },
      {
        path: 'teacher/grades',
        name: 'teacher.grades',
        component: () => import('@/modules/School/views/teacher/GradesInput.vue'),
        meta: { title: 'Input Nilai (E-Rapor)', permission: 'view teacher portal' }
      },
];

export default schoolRoutes;
