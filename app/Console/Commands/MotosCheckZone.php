<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MotosCheckZone extends Command
{
    // Nom de la commande
    protected $signature = 'motos:check-zone';

    // Description de la commande
    protected $description = 'Vérifie si les motos sont dans la zone';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
            \Log::info("Début de l'exécution de la commande motos:check-zone");

        try {
            // Appeler une méthode du contrôleur pour mettre à jour les caches et les zones
            app(\App\Http\Controllers\Gps\LocalisationController::class)->updateCacheMotosEtZones();
            \Log::info("Commande motos:check-zone exécutée avec succès.");
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'exécution de la commande motos:check-zone : " . $e->getMessage());
        }

    }
}
