<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entrepot extends Model
{
    
    use HasFactory;

    protected $fillable = [
        'entrepot_unique_id',
        'nom_entrepot',
        'nom_proprietaire',
        'ville',
        'quartier',
        'telephone',
        'email',
        'description',
        'logo',
        'password',
    ];

  
    // Relation avec les utilisateurs de l'entrepôt
    public function usersEntrepot()
    {
        return $this->hasMany(UserEntrepot::class, 'id_entrepot');
    }

    // Relation avec les batteries de l'entrepôt
    public function batteryEntrepot()
    {
        return $this->hasMany(BatteryEntrepot::class, 'id_entrepot');
    }

    // Relation avec l'historique des swaps dans l'entrepôt
    public function historiqueEntrepot()
    {
        return $this->hasMany(HistoriqueEntrepot::class, 'id_entrepot');
    }
}
