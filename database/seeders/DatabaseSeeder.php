<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Locale;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed locales
        $locales = [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_active' => true, 'is_default' => true],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'is_active' => true, 'is_default' => false],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'is_active' => true, 'is_default' => false],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'is_active' => true, 'is_default' => false],
            ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano', 'is_active' => false, 'is_default' => false],
            ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português', 'is_active' => false, 'is_default' => false],
        ];

        foreach ($locales as $locale) {
            Locale::create($locale);
        }

        // Seed permissions
        $permissions = [
            ['name' => 'manage-settings', 'display_name' => 'Manage Settings', 'description' => 'Can manage application settings'],
            ['name' => 'manage-users', 'display_name' => 'Manage Users', 'description' => 'Can manage users'],
            ['name' => 'manage-roles', 'display_name' => 'Manage Roles', 'description' => 'Can manage roles and permissions'],
            ['name' => 'edit-any-thread', 'display_name' => 'Edit Any Thread', 'description' => 'Can edit any thread'],
            ['name' => 'delete-any-thread', 'display_name' => 'Delete Any Thread', 'description' => 'Can delete any thread'],
            ['name' => 'edit-any-message', 'display_name' => 'Edit Any Message', 'description' => 'Can edit any message'],
            ['name' => 'delete-any-message', 'display_name' => 'Delete Any Message', 'description' => 'Can delete any message'],
            ['name' => 'assign-threads', 'display_name' => 'Assign Threads', 'description' => 'Can assign threads to users or departments'],
            ['name' => 'view-reports', 'display_name' => 'View Reports', 'description' => 'Can view reports and analytics'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Seed roles
        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Full system access',
        ]);

        $agentRole = Role::create([
            'name' => 'agent',
            'display_name' => 'Support Agent',
            'description' => 'Can handle support tickets',
        ]);

        // Assign permissions to agent role
        $agentPermissions = Permission::whereIn('name', [
            'edit-any-message',
            'assign-threads',
            'view-reports',
        ])->get();

        $agentRole->permissions()->attach($agentPermissions);
    }
}
