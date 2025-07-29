<?php

// app/Models/EtatBatterieAssociation5Min.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtatBatterieAssociation5Min extends Model
{
    public $timestamps = false;

    protected $table = 'etat_batterie_association_5_min';

    protected $fillable = [
        'mac_id', 'soc', 'user_id', 'captured_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
    ];



    public function user()
{
    return $this->belongsTo(\App\Models\ValidatedUser::class, 'user_id');
}

}
