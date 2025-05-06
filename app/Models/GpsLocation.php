<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;   // ✅ correction ici

class GpsLocation extends Model
{
    use HasFactory ;

    protected $table = 'device_locations';
    protected $primaryKey = 'id';

    protected $fillable = [
        'device_id',
        'macid',
        'longitude',
        'latitude',
        'speed',
        'status',
        'update_time',
        'server_time',
    ];

   
}
