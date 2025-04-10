<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Distributeur extends Model
{
    use HasFactory;

    protected $fillable = [
        'distributeur_unique_id',
        'nom',
        'prenom',
        'ville',
        'quartier',
        'phone',
        'email',
        'password',
    ];

 
     // Relation avec les batteries de l'agence
     public function batteryAgences()
     {
         return $this->hasMany(BatteryAgences::class, 'id_agence');
     }
     
     // Relation avec les batteries de l'entrepôt
     public function batteryEntrepot()
     {
         return $this->hasMany(BatteryEntrepot::class, 'id_entrepot');
     }
     // Relation avec l'historique des swaps dans l'agence
     public function historiqueAgences()
     {
         return $this->hasMany(HistoriqueAgences::class, 'id_agence');
     }
}
