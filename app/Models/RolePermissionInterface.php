<?php

namespace App\Models;

interface RolePermissionInterface
{
    public function hasRolePermission(string $permission) : bool;
    public function hasRolePermissions(array $permissions) : bool;
}
