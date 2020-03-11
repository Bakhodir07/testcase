<?php

use App\Permission;
use App\PermissionRole;

function check_permission($table_name, $key, $user)
{
    $permission = Permission::where('key', $key)->where('table_name', $table_name)->first();
    if (!isset($permission->id)) return 0;
    $permission_role = PermissionRole::where('permission_id', $permission->id)->where('role_id', $user->role_id)->first();
    if (!isset($permission_role->id)) return 0;

    return 1;
}
