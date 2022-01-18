<?php

namespace App\Http\Controllers\Api\Modules\Users;

use App\Http\Controllers\Api\Modules\Jobs\Job;
use App\Http\Controllers\Api\Modules\Roles\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 * required={"password"},
 * @OA\Xml(name="User"),
 * @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 * @OA\Property(property="email", type="string", readOnly="true", format="email", description="User unique email address", example="user@gmail.com"),
 * )
 */

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id', 'password', 'remember_token', 'created_at', 'updated_at', 'deleted_at', 'email_verified_at', 'role_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function roleName($role) {
        if ($role) {
            return $this->role->name == $role;
        }
        return !! $this->role->name;
    }

    public function jobs() {
        return $this->belongsToMany(Job::class, 'job_applicants', 'user_id', 'job_id')
            ->withPivot('resume', 'status')
            ->withTimestamps();
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'user_skill', 'skill_id', 'user_id')
            ->withTimestamps();
    }

    public function userDetail() {
        return $this->hasOne(UserDetail::class, 'user_id');
    }

    public function experinces() {
        return $this->hasMany(Experince::class);
    }

    public function educations() {
        return $this->hasMany(Education::class);
    }

//    public function skills() {
//        return $this->hasMany(Skill::class);
//    }

    public function companyDetail() {
        return $this->hasOne(CompanyDetail::class, 'company_id');
    }
}
