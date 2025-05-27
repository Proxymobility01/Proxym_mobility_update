<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
class HistoriqueEntrepot extends Model
{
    
    use HasFactory;

    protected $fillable = [
        'id_entrepot', 
        'id_distributeur', 
        'bat_sortante', 
        'bat_entrante', 
        'type_swap', 
    ];

    // Relation avec l'entrepôt
    public function entrepot()
    {
        return $this->belongsTo(Entrepot::class, 'id_entrepot');
    }

    // Relation avec le distributeur
    public function distributeur()
    {
        return $this->belongsTo(Distributeur::class, 'id_distributeur');
    }


    public function batteryValide()
{
    return $this->belongsTo(BatteriesValide::class, 'id_battery_valide');
}




    // Relation avec l'utilisateur de l'entrepôt (l'utilisateur qui a effectué la transaction)
    public function userEntrepot()
    {
        return $this->belongsTo(UsersEntrepot::class, 'id_user_entrepot', 'users_entrepot_unique_id');
    }

    // Relation avec la batterie sortante
    public function batteryEntrepot()
    {
        return $this->belongsTo(BatteryValides::class, 'id_battery');
    }
// Assurez-vous que les timestamps sont activés
protected $table = 'historique_entrepots';
public $timestamps = true;
}
