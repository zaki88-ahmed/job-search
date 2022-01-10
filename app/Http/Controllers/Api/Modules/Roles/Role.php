<?php

namespace App\Http\Controllers\Api\Modules\Roles;

use App\Http\Controllers\Api\Modules\Permissions\Permission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'roles_permissions');
    }

    public function hasPermission($permission) {
        $userPermissions = auth()->user()->role->permissions->pluck('title')->toArray();
        if (in_array($permission, $userPermissions)) {
            return true;
        }
        return false;
    }
}
