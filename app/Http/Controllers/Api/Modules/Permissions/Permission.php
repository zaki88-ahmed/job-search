<?php

namespace App\Http\Controllers\Api\Modules\Permissions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "permissions";
    protected $fillable = ['title'];
    protected $hidden = ['pivot' , 'created_at' , 'updated_at' , 'deleted_at'];
}
