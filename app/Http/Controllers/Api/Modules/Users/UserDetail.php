<?php

namespace App\Http\Controllers\Api\Modules\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "user_details";
    protected $appends = ['resume'];
    protected $fillable = ['gender', 'marital_status', 'military_status', 'nationality', 'resume', 'user_id'];
    protected $hidden = ['user_id', 'created_at' , 'updated_at' , 'deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getResumeAttribute()
    {
        return asset('uploads/resumes/' . $this->resume);
    }
}
