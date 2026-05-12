import type { NavItem } from '@/shared/utils/navigation';

export const schoolNavigation: NavItem[] = [
    {
        label: 'Institution', labelKey: 'modules.school.navigation.sections.institution', icon: 'building', context: 'foundation', children: [
            { name: 'foundation.identity', to: '/dash/schools', label: 'Identitas Yayasan', labelKey: 'modules.school.navigation.menu.foundationIdentity', icon: 'fingerprint', permission: 'manage settings' },
            { name: 'schools.index', to: '/dash/schools', label: 'Manajemen Unit', labelKey: 'modules.school.navigation.menu.schoolsIndex', icon: 'layout-grid', permission: 'view schools' },
            { name: 'schools.organization', to: '/dash/schools/organization', label: 'Struktur Organisasi', labelKey: 'modules.school.navigation.menu.schoolsOrganization', icon: 'network', permission: 'manage settings' },
        ]
    },
    {
        label: 'Akademik & Kurikulum', labelKey: 'modules.school.navigation.sections.academic_kurikulum', icon: 'book-open', context: 'unit', children: [
            { name: 'academic.index', to: '/dash/academic', label: 'Manajemen Akademik', labelKey: 'modules.school.navigation.menu.academicIndex', icon: 'graduation-cap', permission: 'manage academic' },
            { name: 'admin-lms-courses', to: '/dash/lms/courses', label: 'E-Learning (LMS)', icon: 'laptop-minimal', permission: 'manage lms' },

        ]
    },
    {
        label: 'Kesiswaan & Ops', labelKey: 'modules.school.navigation.sections.kesiswaan_ops', icon: 'users', context: 'unit', children: [
            { name: 'students.index', to: '/dash/students', label: 'Data Siswa', labelKey: 'modules.school.navigation.menu.studentsIndex', icon: 'users', permission: 'view students' },
            { name: 'attendance.index', to: '/dash/attendance', label: 'Presensi Siswa', labelKey: 'modules.school.navigation.menu.attendanceIndex', icon: 'clipboard-check', permission: 'view attendance' },
            { name: 'student-affairs.index', to: '/dash/student-affairs', label: 'Layanan Kesiswaan', labelKey: 'modules.school.navigation.menu.studentAffairsIndex', icon: 'user-check', permission: 'view student affairs' },
            { name: 'students.graduation', to: '/dash/graduation', label: 'Manajemen Kelulusan', labelKey: 'modules.school.navigation.menu.graduation', icon: 'graduation-cap', permission: 'edit students' },
            { name: 'visitors.index', to: '/dash/visitors', label: 'Buku Tamu', labelKey: 'modules.school.navigation.menu.visitorsIndex', icon: 'contact', permission: 'view staff' },
        ]
    },
    {
        label: 'HR & Kepegawaian', labelKey: 'modules.school.navigation.sections.hr_kepegawaian', icon: 'user-cog', context: 'unit', children: [
            { name: 'staff.index', to: '/dash/staff', label: 'Data Pegawai', labelKey: 'modules.school.navigation.menu.staffIndex', icon: 'briefcase', permission: 'view staff' },
            { name: 'hr.index', to: '/dash/hr', label: 'Manajemen HR (Presensi & Gaji)', labelKey: 'modules.school.navigation.menu.hrIndex', icon: 'user-cog', permission: 'view staff' },
            { name: 'admission.index', to: '/dash/admission', label: 'PPDB (Admission)', labelKey: 'modules.school.navigation.menu.admissionIndex', icon: 'user-plus', permission: 'view admission' },
        ]
    },
    {
        label: 'Logistik & Sarpras', labelKey: 'modules.school.navigation.sections.logistik_sarpras', icon: 'package', context: 'unit', children: [
            { name: 'sarpras.index', to: '/dash/sarpras', label: 'Sarana & Prasarana', labelKey: 'modules.school.navigation.menu.sarprasIndex', icon: 'package', permission: 'view sarpras' },
            { name: 'logistics.index', to: '/dash/logistics', label: 'Ekosistem (Asrama, Bus, Inventaris)', labelKey: 'modules.school.navigation.menu.logisticsIndex', icon: 'box', permission: 'view sarpras' },
        ]
    },
    {
        label: 'Osis & Alumni', labelKey: 'modules.school.navigation.sections.osis_alumni', icon: 'graduation-cap', context: 'unit', children: [
            { name: 'osis.index', to: '/dash/osis', label: 'Manajemen OSIS', labelKey: 'modules.school.navigation.menu.osisIndex', icon: 'shield-check', permission: 'view osis' },
            { name: 'alumni.index', to: '/dash/alumni', label: 'Manajemen Alumni', labelKey: 'modules.school.navigation.menu.alumniIndex', icon: 'users', permission: 'view students' },
        ]
    },
    {
        label: 'Keamanan & Monitoring', labelKey: 'modules.school.navigation.sections.monitoring', icon: 'shield-alert', context: 'both', children: [
            { name: 'settings.logs', to: '/dash/settings/logs', label: 'Audit Log & Keamanan', labelKey: 'modules.school.navigation.menu.settingsLogs', icon: 'shield-alert', permission: 'view logs' },
        ]
    },
    {
        label: 'Akademik', labelKey: 'modules.school.navigation.sections.academic', icon: 'graduation-cap', context: 'unit', children: [
            { name: 'student.dashboard', to: '/dash/student/dashboard', label: 'Portal Siswa', labelKey: 'modules.school.navigation.menu.studentDashboard', icon: 'layout-dashboard' },
            { name: 'student-lms-my-courses', to: '/dash/student/lms/my-courses', label: 'E-Learning (LMS)', icon: 'graduation-cap' },
            { name: 'student-lms-catalog', to: '/dash/student/lms/catalog', label: 'Katalog Kursus', icon: 'book-marked' },
            { name: 'student.graduation', to: '/dash/student/graduation', label: 'Kelulusan', labelKey: 'modules.school.navigation.menu.graduation', icon: 'graduation-cap' },

        ]
    },
];
