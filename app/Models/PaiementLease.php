<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiementLease extends Model
{
    protected $table = 'paiement_leases';

    protected $fillable = [
        'id_moto',
        'montant_lease',
        'montant_battery',
        'total_lease',
        'statut',
        'id_user_agence'
    ];

    public function moto()
    {
        return $this->belongsTo(MotosValide::class, 'id_moto');
    }

    public function userAgence()
    {
        return $this->belongsTo(UsersAgence::class, 'id_user_agence');
    }
}
