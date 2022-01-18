<?php

namespace App\Http\Controllers\Api\Modules\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "skills";
    protected $fillable = ['name', 'years_of_experience', 'justification'];
    protected $hidden = ['user_id' , 'created_at' , 'updated_at' , 'deleted_at'];

//    public function users() {
//        return $this->belongsTo(User::class);
//    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_skill', 'user_id', 'skill_id')
            ->withTimestamps();
    }
}
