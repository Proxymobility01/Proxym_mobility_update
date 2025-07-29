<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DisplayDashboardService;

class RefreshDashboardCache extends Command
{
    protected $signature = 'dashboard:refresh-cache';
    protected $description = 'Rafraîchit le cache des données du dashboard';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(DisplayDashboardService $dashboardService)
    {
        // Rafraîchir le cache du dashboard
        $this->info('Rafraîchissement du cache du dashboard...');
        
        $dashboardService->refreshDashboardCache();
        
        // Message dans la console pour indiquer que la tâche est terminée
        $this->info('Cache du dashboard rafraîchi avec succès');
    }
}
