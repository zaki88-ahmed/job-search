<?php

namespace App\Http\Controllers\Api\Modules\Jobs;


use App\Http\Controllers\Api\Modules\Categories\Category;
use App\Http\Controllers\Api\Modules\JobTypes\JobType;
use App\Http\Controllers\Api\Modules\Locations\Location;
use App\Http\Controllers\Api\Modules\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'salary_range', 'requirements', 'description', 'years_of_experience', 'is_published',
        'category_id', 'company_id', 'location_id', 'job_type_id'
    ];

    protected $hidden = ['category_id', 'company_id', 'location_id', 'job_type_id', 'created_at', 'updated_at', 'deleted_at'];

    public function company()
    {
        return $this->belongsTo(User::class, 'company_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(JobType::class, 'job_type_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'job_applicants', 'job_id', 'user_id')
            ->withPivot('resume', 'status')
            ->withTimestamps();
    }


}
