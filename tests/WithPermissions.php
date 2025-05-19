<?php

namespace Tests;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait WithPermissions
{
    /**
     * Assign a role with permissions to a user
     *
     * @param \App\Models\User $user
     * @param string $roleName
     * @param array $permissions
     * @return void
     */
    protected function assignRoleWithPermissions($user, string $roleName, array $permissions = []): void
    {
        $role = Role::firstOrCreate(['name' => $roleName]);
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        $role->syncPermissions($permissions);
        $user->assignRole($role);
    }
    
    /**
     * Assign permissions directly to a user
     *
     * @param \App\Models\User $user
     * @param array $permissions
     * @return void
     */
    protected function assignPermissions($user, array $permissions = []): void
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        $user->syncPermissions($permissions);
    }
}
