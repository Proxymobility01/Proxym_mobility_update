<?php

namespace App\Http\Controllers\Dashboard;

use App\Services\DisplayDashboardService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Routing\Controller; // ✅ Ceci est l'import manquant

class DisplayDashboardController extends Controller
{
    public function index()
    {
        $service = new DisplayDashboardService();

        $batteries = $service->getAllBatteryBmsData(
            $service->getAllBatteriesValide(),
            $service->getAllLatestBms()
        );

        $agences = $service->getAllAgences();

        $data = Cache::remember('dashboard_full_data', 600, function () use ($service, $batteries, $agences) {
            return $service->getFullDashboardData($batteries, $agences);
        });

        return view('dashboard.display')
            ->with('summaryStats', $data['summaryStats'])
            ->with('levelStats', $data['levelStats'])
            ->with('stationStats', $data['stationStats'])
            ->with('stations', $data['stations'])
            ->with('chauffeursCount', $data['chauffeursCount'])
            ->with('monthlySwapCount', $data['monthlySwapCount'])
            ->with('totalDistance', $data['totalDistance'])
            ->with('stationsMap', $data['stationsMap'])
            ->with('batteriesWithLocation', $data['batteriesWithLocation'])
            ->with('motosWithLocation', $data['motosWithLocation'])
            ->with('timestamp', $data['timestamp']);
    }
}
