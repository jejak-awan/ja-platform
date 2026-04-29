/**
 * Navigation groups configuration for admin sidebar
 * Reorganized for better UX and logical grouping
 */

export interface NavItem {
    name?: string;
    to?: string;
    label?: string;
    labelKey?: string;
    icon?: string;
    permission?: string;
    role?: string | string[];
    type?: 'item' | 'divider';
    children?: NavItem[];
}

export const navigationGroups: Record<string, NavItem[]> = {
    // CMS Module - Content, Marketing, Appearance
    cms: [
        {
            label: 'Content', labelKey: 'common.navigation.sections.content_studio', icon: 'layers', children: [
                { name: 'studio', to: '/dash/studio', label: 'Contents', labelKey: 'common.navigation.menu.studio', permission: 'view content' },
                { name: 'media', to: '/dash/media', label: 'Media Library', labelKey: 'common.navigation.menu.mediaLibrary', permission: 'view media' },
                { name: 'file-manager', to: '/dash/file-manager', label: 'File Manager', labelKey: 'common.navigation.menu.fileManager', permission: 'view files' },
                { name: 'comments', to: '/dash/comments', label: 'Comments', labelKey: 'common.navigation.menu.comments', permission: 'view comments' },
            ]
        },
        {
            label: 'Marketing & Insights', labelKey: 'common.navigation.sections.marketing_insights', icon: 'megaphone', children: [
                { name: 'analytics', to: '/dash/analytics', label: 'Analytics', labelKey: 'common.navigation.menu.analytics', permission: 'view analytics' },
                { name: 'forms', to: '/dash/forms', label: 'Forms', labelKey: 'common.navigation.menu.forms', permission: 'view forms' },
                { name: 'newsletter', to: '/dash/newsletter', label: 'Newsletter', labelKey: 'common.navigation.menu.newsletter', permission: 'view newsletter' },
                { name: 'email-templates', to: '/dash/email-templates', label: 'Email Templates', labelKey: 'common.navigation.menu.emailTemplates', permission: 'manage settings' },
            ]
        },
        {
            label: 'SEO & Optimization', labelKey: 'common.navigation.sections.seo_optimization', icon: 'search', children: [
                { name: 'seo', to: '/dash/seo', label: 'SEO Tools', labelKey: 'common.navigation.menu.seoTools', permission: 'manage settings' },
                { name: 'redirects', to: '/dash/redirects', label: 'Redirects', labelKey: 'common.navigation.menu.redirects', permission: 'view redirects' },
            ]
        },
        {
            label: 'Design & Configuration', labelKey: 'common.navigation.sections.design_config', icon: 'palette', children: [
                { name: 'themes', to: '/dash/themes', label: 'Themes', labelKey: 'common.navigation.menu.themes', permission: 'view themes' },
                { name: 'menus', to: '/dash/menus', label: 'Menus', labelKey: 'common.navigation.menu.menus', permission: 'view menus' },
                { name: 'widgets', to: '/dash/widgets', label: 'Widgets', labelKey: 'common.navigation.menu.widgets', permission: 'view widgets' },
                { name: 'cms-settings', to: '/dash/cms/settings', label: 'CMS Settings', labelKey: 'common.navigation.menu.cmsSettings', permission: 'manage settings' },
            ]
        },
        { type: 'divider', label: 'advanced', labelKey: 'common.navigation.sections.advanced' },
        { name: 'custom-fields', to: '/dash/custom-fields', label: 'Custom Fields', labelKey: 'common.navigation.menu.customFields', icon: 'custom-fields', permission: 'manage content' },
    ],

    // School Module - Reorganized for Category-based structure
    school: [
        {
            label: 'Institution', labelKey: 'common.navigation.sections.institution', icon: 'building', children: [
                { name: 'schools.dashboard', to: '/dash/school-dashboard', label: 'Dasbor Utama', labelKey: 'common.navigation.menu.schoolsDashboard', icon: 'activity', permission: 'view schools' },
                { name: 'schools.index', to: '/dash/schools', label: 'Identitas Lembaga', labelKey: 'common.navigation.menu.schoolsIndex', icon: 'building', permission: 'manage settings' },
                { name: 'schools.organization', to: '/dash/schools/organization', label: 'Struktur Organisasi', labelKey: 'common.navigation.menu.schoolsOrganization', icon: 'network', permission: 'manage settings' },
            ]
        },
        {
            label: 'Akademik & Kurikulum', labelKey: 'common.navigation.sections.academic_kurikulum', icon: 'book-open', children: [
                { name: 'academic.index', to: '/dash/academic', label: 'Manajemen Akademik', labelKey: 'common.navigation.menu.academicIndex', icon: 'graduation-cap', permission: 'manage academic' },
                { name: 'lms.index', to: '/dash/lms', label: 'LMS (Materi & Tugas)', labelKey: 'common.navigation.menu.lmsIndex', icon: 'book-open', permission: 'view lms' },
                { name: 'admin.lms.question-bank', to: '/dash/lms/question-bank', label: 'Bank Soal', labelKey: 'common.navigation.menu.adminQuestionBank', icon: 'database', permission: 'view lms' },
                { name: 'admin.cbt.manage', to: '/dash/cbt/manage', label: 'CBT (Ujian Formal)', labelKey: 'common.navigation.menu.adminCbtManage', icon: 'monitor', permission: 'view lms' },
            ]
        },
        {
            label: 'Kesiswaan & Ops', labelKey: 'common.navigation.sections.kesiswaan_ops', icon: 'users', children: [
                { name: 'students.index', to: '/dash/students', label: 'Data Siswa', labelKey: 'common.navigation.menu.studentsIndex', icon: 'users', permission: 'view students' },
                { name: 'attendance.index', to: '/dash/attendance', label: 'Presensi Siswa', labelKey: 'common.navigation.menu.attendanceIndex', icon: 'clipboard-check', permission: 'view attendance' },
                { name: 'student-affairs.index', to: '/dash/student-affairs', label: 'Layanan Kesiswaan', labelKey: 'common.navigation.menu.studentAffairsIndex', icon: 'user-check', permission: 'view student affairs' },
                { name: 'students.graduation', to: '/dash/graduation', label: 'Manajemen Kelulusan', labelKey: 'common.navigation.menu.graduation', icon: 'graduation-cap', permission: 'edit students' },
                { name: 'visitors.index', to: '/dash/visitors', label: 'Buku Tamu', labelKey: 'common.navigation.menu.visitorsIndex', icon: 'contact', permission: 'view staff' },
            ]
        },
        {
            label: 'HR & Kepegawaian', labelKey: 'common.navigation.sections.hr_kepegawaian', icon: 'user-cog', children: [
                { name: 'staff.index', to: '/dash/staff', label: 'Data Pegawai', labelKey: 'common.navigation.menu.staffIndex', icon: 'briefcase', permission: 'view staff' },
                { name: 'hr.index', to: '/dash/hr', label: 'Manajemen HR (Presensi & Gaji)', labelKey: 'common.navigation.menu.hrIndex', icon: 'user-cog', permission: 'view staff' },
                { name: 'admission.index', to: '/dash/admission', label: 'PPDB (Admission)', labelKey: 'common.navigation.menu.admissionIndex', icon: 'user-plus', permission: 'view admission' },
            ]
        },
        {
            label: 'Logistik & Sarpras', labelKey: 'common.navigation.sections.logistik_sarpras', icon: 'package', children: [
                { name: 'sarpras.index', to: '/dash/sarpras', label: 'Sarana & Prasarana', labelKey: 'common.navigation.menu.sarprasIndex', icon: 'package', permission: 'view sarpras' },
                { name: 'logistics.index', to: '/dash/logistics', label: 'Ekosistem (Asrama, Bus, Inventaris)', labelKey: 'common.navigation.menu.logisticsIndex', icon: 'box', permission: 'view sarpras' },
            ]
        },
        {
            label: 'Osis & Alumni', labelKey: 'common.navigation.sections.osis_alumni', icon: 'graduation-cap', children: [
                { name: 'osis.index', to: '/dash/osis', label: 'Manajemen OSIS', labelKey: 'common.navigation.menu.osisIndex', icon: 'shield-check', permission: 'view osis' },
                { name: 'alumni.index', to: '/dash/alumni', label: 'Manajemen Alumni', labelKey: 'common.navigation.menu.alumniIndex', icon: 'users', permission: 'view students' },
            ]
        },
        {
            label: 'Keuangan & Monitoring', labelKey: 'common.navigation.sections.keuangan_monitoring', icon: 'banknote', children: [
                { name: 'finance.index', to: '/dash/finance', label: 'Keuangan Sekolah', labelKey: 'common.navigation.menu.financeIndex', icon: 'banknote', permission: 'view school finance' },
                { name: 'settings.logs', to: '/dash/settings/logs', label: 'Audit Log & Keamanan', labelKey: 'common.navigation.menu.settingsLogs', icon: 'shield-alert', permission: 'view logs' },
            ]
        },
        {
            label: 'Portal Siswa', labelKey: 'common.navigation.sections.student_portal', icon: 'user', role: 'siswa', children: [
                { name: 'student.dashboard', to: '/dash/student/dashboard', label: 'Dashboard', labelKey: 'common.navigation.menu.dashboard', icon: 'layout-dashboard' },
                { name: 'student.graduation', to: '/dash/student/graduation', label: 'Kelulusan', labelKey: 'common.navigation.menu.graduation', icon: 'graduation-cap' },
            ]
        },
    ],

    // Core Module - Users, Monitoring, Infrastructure
    core: [
        {
            label: 'Users & Access', labelKey: 'common.navigation.sections.users_access', icon: 'users', children: [
                // Route name must match vue-router record (`users.index`)
                { name: 'users.index', to: '/dash/users', label: 'Users', labelKey: 'common.navigation.menu.users', permission: 'view users' },
                { name: 'roles', to: '/dash/roles', label: 'Roles & Permissions', labelKey: 'common.navigation.menu.roles', permission: 'view roles' },
            ]
        },
        {
            label: 'Monitoring', labelKey: 'common.navigation.sections.monitoring', icon: 'activity', children: [
                { name: 'journal-dashboard', to: '/dash/journal-dashboard', label: 'Journal Dashboard', labelKey: 'common.navigation.menu.journalDashboard', permission: 'view logs' },
                { name: 'activity-journal', to: '/dash/activity-journal', label: 'Activity Journal', labelKey: 'common.navigation.menu.activityJournal', permission: 'view activity logs' },
                { name: 'security-journal', to: '/dash/security-journal', label: 'Security Journal', labelKey: 'common.navigation.menu.securityJournal', permission: 'view security logs' },
                { name: 'system-journal', to: '/dash/system-journal', label: 'System Journal', labelKey: 'common.navigation.menu.systemJournal', permission: 'view system' },
                { name: 'access-journal', to: '/dash/access-journal', label: 'Access History', labelKey: 'common.navigation.menu.accessJournal', permission: 'view users' },
            ]
        },
        {
            label: 'Infrastructure', labelKey: 'common.navigation.sections.infrastructure', icon: 'settings', children: [
                { name: 'system', to: '/dash/system', label: 'System Info', labelKey: 'common.navigation.menu.systemInfo', permission: 'view system' },
                { name: 'settings', to: '/dash/settings', label: 'System Settings', labelKey: 'common.navigation.menu.settings', permission: 'view settings' },
                { name: 'system-notifications', to: '/dash/system/notifications', label: 'Notifications', labelKey: 'common.navigation.menu.systemNotifications', permission: 'manage system' },
                { name: 'backups', to: '/dash/backups', label: 'Backups', labelKey: 'common.navigation.menu.backups', permission: 'view backups' },
                { name: 'redis', to: '/dash/redis', label: 'Redis Cache', labelKey: 'common.navigation.menu.redis', permission: 'manage settings' },
                { name: 'scheduled-tasks', to: '/dash/scheduled-tasks', label: 'Scheduled Tasks', labelKey: 'common.navigation.menu.scheduledTasks', permission: 'view scheduled tasks' },
                { name: 'languages', to: '/dash/languages', label: 'Languages', labelKey: 'common.navigation.menu.languages', permission: 'view settings' },
            ]
        },
        {
            label: 'Developer', labelKey: 'common.navigation.sections.developer', icon: 'code', children: [
                { name: 'webhooks', to: '/dash/webhooks', label: 'Webhooks', labelKey: 'common.navigation.menu.webhooks', permission: 'manage settings' },
                { name: 'plugins', to: '/dash/plugins', label: 'Plugins', labelKey: 'common.navigation.menu.plugins', permission: 'view plugins' },
            ]
        },
    ],
};
