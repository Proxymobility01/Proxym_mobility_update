<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyDistance extends Model
{
    use HasFactory;

    protected $fillable = [
        'validated_user_id',
        'date',
        'total_distance_km',
    ];

    public function user()
    {
        return $this->belongsTo(ValidatedUser::class, 'validated_user_id');
    }


}
