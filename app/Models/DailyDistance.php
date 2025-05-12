<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyDistance extends Model
{
    use HasFactory;

    protected $table = 'daily_distances'; // si tu veux être explicite

    protected $fillable = [
        'user_id', // clé étrangère vers ValidatedUser
        'date',
        'total_distance_km',
        'last_location',
        'last_updated',
    ];

    // Active timestamps (Laravel le fait déjà par défaut, mais on le précise ici)
    public $timestamps = true;

    /**
     * Relation avec l'utilisateur validé.ùoto
     */
    public function user()
    {
        return $this->belongsTo(ValidatedUser::class, 'user_id');
    }
}
