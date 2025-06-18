<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PowerReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'agence_id', 'kw1', 'kw2', 'kw3', 'kw4',
        'created_at', 'charged_batteries', 'low_batteries'
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(UsersAgence::class, 'user_id');
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id');
    }
}
