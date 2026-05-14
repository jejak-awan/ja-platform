<?php

namespace Modules\Cms\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CmsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedPermissions();
        $this->seedRoles();

        $this->call([
            JanariThemeSeeder::class,
            MenuLocationStandardizationSeeder::class,
        ]);
    }

    protected function seedPermissions(): void
    {
        $permissions = [
            // Content
            'view content', 'manage content', 'create content', 'edit content', 'delete content', 'publish content', 'approve content', 'view pending content',
            'view content templates', 'create content templates', 'edit content templates', 'delete content templates',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view tags', 'create tags', 'edit tags', 'delete tags',

            // Engagement
            'view comments', 'create comments', 'edit comments', 'delete comments', 'approve comments', 'manage comments',
            'view forms', 'create forms', 'edit forms', 'delete forms', 'manage forms', 'view submissions',
            'view newsletter', 'create newsletter', 'edit newsletter', 'delete newsletter',

            // Appearance
            'view themes', 'upload themes', 'edit themes', 'delete themes', 'manage themes',
            'view menus', 'create menus', 'edit menus', 'delete menus', 'manage menus',
            'view widgets', 'create widgets', 'edit widgets', 'delete widgets', 'manage widgets',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }

    protected function seedRoles(): void
    {
        $cmsAdmin = Role::firstOrCreate(['name' => 'cms:admin', 'guard_name' => 'web']);
        $cmsAdmin->syncPermissions(Permission::whereIn('name', [
            'view content', 'manage content', 'create content', 'edit content', 'delete content', 'publish content', 'approve content', 'view pending content',
            'view content templates', 'create content templates', 'edit content templates', 'delete content templates',
            'view categories', 'create categories', 'edit categories', 'delete categories',
            'view tags', 'create tags', 'edit tags', 'delete tags',
            'view media', 'upload media', 'edit media', 'delete media', 'manage media',
            'view themes', 'upload themes', 'edit themes', 'delete themes', 'manage themes',
            'view menus', 'create menus', 'edit menus', 'delete menus', 'manage menus',
            'view widgets', 'create widgets', 'edit widgets', 'delete widgets', 'manage widgets',
            'view forms', 'create forms', 'edit forms', 'delete forms', 'manage forms', 'view submissions',
            'view comments', 'create comments', 'edit comments', 'delete comments', 'approve comments', 'manage comments',
            'view newsletter', 'create newsletter', 'edit newsletter', 'delete newsletter',
            'view redirects', 'manage redirects',
            'view analytics',
            'manage module access',
        ])->get());

        $cmsEditor = Role::firstOrCreate(['name' => 'cms:editor', 'guard_name' => 'web']);
        $cmsEditor->syncPermissions(Permission::whereIn('name', [
            'view content', 'create content', 'edit content', 'delete content', 'publish content', 'approve content', 'view pending content',
            'view categories', 'view tags', 'view media', 'upload media',
            'view comments', 'approve comments',
            'view analytics',
        ])->get());
    }
}
