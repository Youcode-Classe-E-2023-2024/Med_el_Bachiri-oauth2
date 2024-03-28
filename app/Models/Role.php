<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_has_role');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permission');
    }

    public function assignPermissions($permissions)
    {
        if (!is_array($permissions)) {
            return false;
        }

        return $this->permissions()->syncWithoutDetaching($permissions);
    }


    public function updatePermissions($permissions)
    {
        if (!is_array($permissions)) {
            return false;
        }

        $this->permissions()->detach();
        return $this->permissions()->sync($permissions);
    }

    public function hasPermissionTo($permission)
    {
        return $this->permissions()->where('name', $permission)->exists();
    }
}
