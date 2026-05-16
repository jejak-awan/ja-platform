<?php

namespace Modules\School\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\System\Models\Role;
use Modules\System\Models\Permission;

class SchoolRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Institution
            'view schools', 'manage schools', 'manage organization',
            
            // Academic & LMS
            'view academic', 'manage academic', 'manage curriculum', 'manage schedule',
            'grade assignments', 'input journal', 'manage lms',
            
            // Students
            'view students', 'manage students', 'edit students', 'view attendance', 'manage attendance',
            'view student affairs', 'manage student affairs', 'view admission', 'manage admission',
            
            // HR
            'view staff', 'manage staff', 'manage payroll',
            
            // Logistics
            'view sarpras', 'manage sarpras', 'manage inventory', 'manage logistics',
            
            // Others
            'view osis', 'manage osis', 'view visitors', 'manage visitors', 'view analytics',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // --- GLOBAL LEVEL ---
        $super = Role::findOrCreate('super', 'web');
        $super->givePermissionTo(Permission::all());

        // --- FOUNDATION LEVEL ---
        $adminYayasan = Role::findOrCreate('admin-yayasan', 'web');
        $adminYayasan->syncPermissions($permissions); // All school permissions

        $operatorYayasan = Role::findOrCreate('operator-yayasan', 'web');
        $operatorYayasan->syncPermissions(['view schools', 'view academic', 'view students', 'view staff', 'view sarpras', 'view analytics']);

        // --- UNIT LEVEL ---
        $adminUnit = Role::findOrCreate('admin-unit', 'web');
        $adminUnit->syncPermissions($permissions); // All unit permissions

        $operatorUnit = Role::findOrCreate('operator-unit', 'web');
        $operatorUnit->syncPermissions([
            'view academic', 'manage schedule', 'input journal',
            'view students', 'manage students', 'view attendance', 'manage attendance',
            'view staff', 'view visitors', 'manage visitors'
        ]);

        // --- SPECIALIZED UNIT ROLES (Level 9 RBAC tests) ---
        // Sarpras: logistics/inventory only (must NOT access operations routes)
        $adminSarpras = Role::findOrCreate('admin-sarpras', 'web');
        $adminSarpras->syncPermissions([
            'view sarpras', 'manage sarpras',
            'manage inventory', 'manage logistics',
        ]);

        // Kesiswaan: student affairs + attendance (must NOT access visitors)
        $adminKesiswaan = Role::findOrCreate('admin-kesiswaan', 'web');
        $adminKesiswaan->syncPermissions([
            'view students', 'manage students',
            'view attendance', 'manage attendance',
            'view student affairs', 'manage student affairs',
        ]);

        // --- ACADEMIC & STAFF LEVEL ---
        $guru = Role::findOrCreate('guru', 'web');
        $guru->syncPermissions(['grade assignments', 'input journal', 'view students', 'view attendance', 'manage attendance']);

        $nonEditingGuru = Role::findOrCreate('non-editing-teacher', 'web');
        $nonEditingGuru->syncPermissions(['grade assignments', 'view students', 'view attendance']);

        $waliKelas = Role::findOrCreate('wali-kelas', 'web');
        $waliKelas->syncPermissions(array_merge($guru->permissions->pluck('name')->toArray(), [
            'view analytics', 'manage students'
        ]));

        $pembinaEkskul = Role::findOrCreate('pembina-ekskul', 'web');
        $pembinaEkskul->syncPermissions(['view students', 'view osis', 'manage osis']);

        $staffTU = Role::findOrCreate('staff-tu', 'web');
        $staffTU->syncPermissions(['view students', 'manage students', 'view attendance', 'manage attendance', 'view visitors', 'manage visitors']);

        // --- PERSONAL LEVEL ---
        $siswa = Role::findOrCreate('siswa', 'web');
        $siswa->syncPermissions(['view attendance', 'view academic']);

        $orangTua = Role::findOrCreate('orang-tua', 'web');
        $orangTua->syncPermissions(['view students', 'view attendance', 'view academic']);

        $this->command->info('School roles and permissions synchronized to Level 9 Standard!');
    }
}
