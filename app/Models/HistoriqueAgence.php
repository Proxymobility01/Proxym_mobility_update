<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriqueAgence extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_agence', 'id_distributeur','id_entrepot', 'bat_sortante', 'bat_entrante', 'type_swap', 'date_time',
    ];

    // Relation avec l'agence
    public function agence()
    {
        return $this->belongsTo(Agences::class, 'id_agence');
    }

    // Relation avec le distributeur
    public function distributeur()
    {
        return $this->belongsTo(Distributeurs::class, 'id_distributeur');
    }

    // Relation avec la batterie sortante
    public function batteryAgence()
    {
        return $this->belongsTo(BatteryValides::class, 'id_battery');
    }

   
}
