<?php

namespace Modules\School\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SchoolRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define Permissions
        $permissions = [
            // Institution
            'view schools',
            'create schools',
            'edit schools',
            'delete schools',
            'manage schools',
            'manage organization',

            // Academic & LMS
            'view academic',
            'manage academic',
            'manage curriculum',
            'manage schedule',
            'grade assignments',
            'input journal',

            
            // Student & Attendance
            'view students',
            'create students',
            'edit students',
            'delete students',
            'manage students',
            'view attendance',
            'manage attendance',
            'view student affairs',
            'manage student affairs',
            
            // HR & Staff
            'view staff',
            'create staff',
            'edit staff',
            'delete staff',
            'manage staff',
            'manage payroll',
            
            // Logistics & Sarpras
            'view sarpras',
            'manage sarpras',
            'manage inventory',
            'manage logistics',
            
            // Finance

            
            // Community & OSIS
            'view osis',
            'manage osis',
            'view admission',
            'manage admission',
            'view alumni',
            'view visitors',
            'manage visitors',
            'view analytics',

            // Extensions
            'view school extensions',
            'manage school extensions',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 2. Define Roles and Assign Relevant Permissions
        
        // Leadership
        $kepalaSekolah = Role::findOrCreate('kepala-sekolah', 'web');
        $kepalaSekolah->syncPermissions($permissions); // All permissions

        $adminSekolah = Role::findOrCreate('admin-sekolah', 'web');
        $adminSekolah->syncPermissions($permissions);

        // Specialized Admins
        $adminKurikulum = Role::findOrCreate('admin-kurikulum', 'web');
        $adminKurikulum->syncPermissions(['view academic', 'manage academic', 'manage curriculum', 'manage schedule', 'view students', 'view staff']);

        $adminKesiswaan = Role::findOrCreate('admin-kesiswaan', 'web');
        $adminKesiswaan->syncPermissions(['view students', 'manage students', 'view attendance', 'manage attendance', 'view student affairs', 'manage student affairs', 'view osis', 'manage osis', 'view admission', 'manage admission']);

        $adminSarpras = Role::findOrCreate('admin-sarpras', 'web');
        $adminSarpras->syncPermissions(['view sarpras', 'manage sarpras', 'manage inventory', 'manage logistics']);

        $adminBK = Role::findOrCreate('admin-bk', 'web');
        $adminBK->syncPermissions(['view students', 'view attendance', 'view student affairs', 'manage student affairs']);

        // Staff
        $guru = Role::findOrCreate('guru', 'web');
        $guru->syncPermissions(['grade assignments', 'input journal', 'view students', 'view attendance', 'manage attendance']);

        $waliKelas = Role::findOrCreate('wali-kelas', 'web');
        $waliKelas->syncPermissions($guru->permissions); // Inherit guru
        $waliKelas->givePermissionTo(['view analytics', 'manage students']);

        // Student & Community
        $siswa = Role::findOrCreate('siswa', 'web');
        $siswa->syncPermissions(['view attendance']);

        $orangTua = Role::findOrCreate('orang-tua', 'web');
        $orangTua->syncPermissions(['view students', 'view attendance']);

        $adminOsis = Role::findOrCreate('admin-osis', 'web');
        $adminOsis->syncPermissions(['view osis', 'manage osis', 'view students']);
        
        $this->command->info('School roles and permissions seeded successfully!');
    }
}
