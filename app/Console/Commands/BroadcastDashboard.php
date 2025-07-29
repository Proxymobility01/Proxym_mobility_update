<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DisplayDashboardService;
use App\Events\DashboardUpdated;


class BroadcastDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:broadcast-dashboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $this->info("🚀 Récupération des données...");

        $service = new DisplayDashboardService();

        $batteries = $service->getAllBatteryBmsData(
            $service->getAllBatteriesValide(),
            $service->getAllLatestBms()
        );

        $agences = $service->getAllAgences();

        $data = $service->getFullDashboardData($batteries, $agences);

        event(new DashboardUpdated($data));

        $this->info("📡 Données envoyées via WebSocket avec succès !");
    }
}
