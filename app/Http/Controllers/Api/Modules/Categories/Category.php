<?php

namespace App\Http\Controllers\Api\Modules\Categories;


use App\Http\Controllers\Api\Modules\Jobs\Job;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'parent_id'
    ];

    protected $hidden = ['parent_id', 'created_at', 'updated_at', 'deleted_at'];

    public function parentCategory()
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function jobs()
    {
        return $this->hasMany(Job::class, 'job_id', 'id');
    }
}
