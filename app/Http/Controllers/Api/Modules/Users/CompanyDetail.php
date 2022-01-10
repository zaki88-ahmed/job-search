<?php

namespace App\Http\Controllers\Api\Modules\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "company_details";
    protected $fillable = ['site', 'logo', 'size', 'job_numbers', 'company_id', 'description'];
    protected $hidden = ['company_id', 'created_at' , 'updated_at' , 'deleted_at'];

    public function company() {
        return $this->belongsTo(User::class, 'company_id', 'id');
    }
}
