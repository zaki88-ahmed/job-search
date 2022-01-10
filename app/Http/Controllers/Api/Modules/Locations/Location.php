<?php

namespace App\Http\Controllers\Api\Modules\Locations;


use App\Http\Controllers\Api\Modules\Jobs\Job;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'country', 'city'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function jobs()
    {
        return $this->hasMany(Job::class, 'location_id', 'id');
    }
}
