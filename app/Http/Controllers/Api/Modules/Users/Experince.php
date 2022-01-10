<?php

namespace App\Http\Controllers\Api\Modules\Users;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Experince extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "experiences";
    protected $fillable = ['start_date', 'end_date', 'title', 'description', 'company_name', 'user_id'];
    protected $hidden = ['created_at' , 'updated_at' , 'deleted_at'];

    public function users() {
        return $this->belongsTo(User::class);
    }
}
