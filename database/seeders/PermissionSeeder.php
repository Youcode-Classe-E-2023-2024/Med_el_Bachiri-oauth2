<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'view-users',
            'manage-users',
            'view-roles',
            'manage-roles',
            'view-permissions'
        ];

        $permissionIds = [];
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);
            $permissionIds[] = $permission->id;
        }

        $adminRole = Role::firstOrCreate(['name' => 'SuperAdmin']);
        $adminRole->permissions()->sync($permissionIds);

        $userRole = Role::firstOrCreate(['name' => 'User']);
        $userRole->permissions()->sync([1, 3, 5]);
    }
}
