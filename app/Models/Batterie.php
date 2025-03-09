<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batterie extends Model
{
    use HasFactory;
    use SoftDeletes ;

    protected $table = 'batteries';
    protected $primaryKey = 'id'; 

    protected $fillable = [
        'batterie_unique_id',
        'mac_id',
         'date_production',
         'fabriquant', 
         'gps', 
         'statut',
        ];


}
