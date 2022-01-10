<?php

namespace App\Http\Controllers\Api\Modules\JobTypes;

use App\Http\Controllers\Api\Modules\Jobs\Job;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class JobType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title'
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function jobs()
    {
        return $this->hasMany(Job::class, 'job_id', 'id');
    }
}
