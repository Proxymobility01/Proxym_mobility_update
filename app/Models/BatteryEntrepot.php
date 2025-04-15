<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatteryEntrepot extends Model
{
    
    use HasFactory;

    protected $fillable = [
        'id_battery_valide', 'id_entrepot',
    ];

    // Relation avec la batterie validée
    public function batteryValide()
    {
        return $this->belongsTo(BatteriesValide::class, 'id_battery_valide');
    }

    // Relation avec l'entrepôt
    public function entrepot()
    {
        return $this->belongsTo(Entrepot::class, 'id_entrepot');
    }


    // Assurez-vous que les timestamps sont activés
protected $table = 'battery_entrepots';
public $timestamps = true; // Pour enregistrer created_at et updated_at
}
