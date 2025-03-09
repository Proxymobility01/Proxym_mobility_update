<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Moto extends Model 
{
    use HasFactory, SoftDeletes;

    protected $table = 'motos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'vin',
        'moto_unique_id',
        'model',
        'gps_imei',
        'statut',
    ];

    // Attributs qui seront convertis en Carbon (date)
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    // Scope pour filtrer les motos non supprimées
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    // Scope pour les différents statuts
    public function scopeByStatus($query, $status)
    {
        return $query->where('statut', $status);
    }
}