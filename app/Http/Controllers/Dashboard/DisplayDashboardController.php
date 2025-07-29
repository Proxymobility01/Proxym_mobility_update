<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Agence;
use App\Models\BatteriesValide;
use App\Models\AssociationUserMoto;
use App\Models\Swap;
use App\Models\PowerReading;
use App\Models\DailyDistance;
use App\Models\BMSData;
use App\Models\MotosValide;
use App\Models\GpsLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class DisplayDashboardController extends Controller
{
    public function index(Request $request)
    {
        \Log::info("=== CHARGEMENT DASHBOARD DISPLAY ===");

        $batteries = $this->getAllBatteryBmsData(BatteriesValide::all());
        $summaryStats = $this->getSummaryStats($batteries);
        $levelStats = $this->getLevelStats($batteries);
        $stationStats = $this->getStationStats($batteries);
        $chauffeursCount = $this->getChauffeursCount();
        $monthlySwapCount = $this->getMonthlySwapCount();
        $totalDistance = $this->getTotalDistance();
        $stations = $this->getStationsWithBatteryDetails($batteries);
        $stationsMap = $this->getStationsForMap();
        $batteriesWithLocation = $this->getBatteryLocations();
        $motosWithLocation = $this->getMotosMapData();

        return view('dashboard.display', [
            'summaryStats' => $summaryStats,
            'levelStats' => $levelStats,
            'stationStats' => $stationStats,
            'stations' => $stations,
            'stationsMap' => $stationsMap,
            'chauffeursCount' => $chauffeursCount,
            'monthlySwapCount' => $monthlySwapCount,
            'totalDistance' => $totalDistance,
            'batteriesWithLocation' => $batteriesWithLocation,
            'motosWithLocation' => $motosWithLocation,
        ]);
    }

    private function getAllBatteryBmsData($batteries)
    {
        return $batteries->map(function ($battery) {
            \Log::info("Batterie ID: {$battery->id}, MAC: {$battery->mac_id}");
            $bms = Cache::remember("battery_bms_{$battery->mac_id}", 60, function () use ($battery) {
                return BMSData::where('mac_id', $battery->mac_id)->latest('timestamp')->first();
            });

            $state = json_decode($bms->state ?? '{}', true);
            $seting = json_decode($bms->seting ?? '{}', true);
            $soc = $state['SOC'] ?? 0;
            $workStatus = $state['WorkStatus'] ?? '3';
            $status = $this->getBatteryStatus($workStatus);
            $online = false;

            if (isset($seting['heart_time'])) {
                try {
                    $online = Carbon::createFromTimestamp($seting['heart_time'])->diffInMinutes(now()) < 5;
                } catch (\Exception $e) {
                    $online = false;
                }
            }

            $stationIds = [];
            $stationNames = [];

            if ($battery->agences) {
                $stationIds = $battery->agences->map(fn($a) => $a->id_agence ?: $a->id)->toArray();
                $stationNames = $battery->agences->pluck('nom_agence')->toArray();
            }

            return [
                'id' => $battery->id,
                'mac_id' => $battery->mac_id,
                'station_ids' => $stationIds,
                'station_names' => $stationNames,
                'soc' => $soc,
                'status' => $status,
                'work_status_code' => $workStatus,
                'online' => $online
            ];
        });
    }

    private function getBatteryStatus($code)
    {
        return [
            '0' => 'En décharge',
            '1' => 'En charge',
            '2' => 'En veille',
            '3' => 'Défaut'
        ][$code] ?? 'Inconnu';
    }

    private function getSummaryStats($batteries)
    {
        $total = $batteries->count();
        $inactive = $batteries->where('online', false)->count();
        $active = $total - $inactive;
        $charged = $batteries->where('soc', '>=', 95)->count();
        $charging = $batteries->where('work_status_code', '1')->count();
        $discharged = $batteries->where('soc', '<', 30)->count();
        $notCharging = $batteries->where('online', true)->where('work_status_code', '!=', '1')->count();

        return compact('total', 'active', 'inactive', 'charged', 'charging', 'discharged', 'notCharging');
    }

    private function getLevelStats($batteries)
    {
        $total = $batteries->count();
        $levels = [
            'very_high' => $batteries->whereBetween('soc', [90, 100]),
            'high' => $batteries->whereBetween('soc', [70, 89]),
            'medium' => $batteries->whereBetween('soc', [40, 69]),
            'low' => $batteries->whereBetween('soc', [10, 39]),
        ];

        $result = [];
        foreach ($levels as $key => $group) {
            $count = $group->count();
            $charging = $group->where('work_status_code', '1')->count();
            $result[$key] = [
                'count' => $count,
                'charging' => $charging,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0
            ];
        }

        return $result;
    }

    private function getStationStats($batteries)
    {
        $stations = Agence::all();
        return $stations->map(function ($station) use ($batteries) {
            $stationId = $station->id_agence ?: $station->id;
            $stationBatteries = $batteries->filter(fn($b) => in_array($stationId, $b['station_ids']));

            return [
                'id' => $stationId,
                'name' => $station->nom_agence,
                'total' => $stationBatteries->count(),
                'charged' => $stationBatteries->where('soc', '>=', 95)->count(),
                'charging' => $stationBatteries->where('work_status_code', '1')->count(),
                'discharged' => $stationBatteries->where('soc', '<', 30)->count(),
                'inactive' => $stationBatteries->where('online', false)->count(),
                'paliers' => [
                    '90-100' => $stationBatteries->whereBetween('soc', [90, 100])->count(),
                    '70-90' => $stationBatteries->whereBetween('soc', [70, 89])->count(),
                    '40-70' => $stationBatteries->whereBetween('soc', [40, 69])->count(),
                    '10-40' => $stationBatteries->whereBetween('soc', [10, 39])->count()
                ]
            ];
        });
    }

    private function getStationsWithBatteryDetails($batteries)
    {
        return Agence::all()->map(function ($station) use ($batteries) {
            $stationId = $station->id_agence ?: $station->id;
            $stationBatteries = $batteries->filter(fn($b) => in_array($stationId, $b['station_ids']));

            $latestReading = PowerReading::where('agence_id', $stationId)->latest()->first();
            $compteurs = $latestReading ? [
                $latestReading->kw1,
                $latestReading->kw2,
                $latestReading->kw3,
                $latestReading->kw4
            ] : ['N/A', 'N/A', 'N/A', 'N/A'];

            return [
                'nom' => $station->nom_agence,
                'ville' => $station->ville ?? 'Douala',
                'latitude' => $station->latitude,
                'longitude' => $station->longitude,
                'batteries_total' => $stationBatteries->count(),
                'batteries_en_charge' => $stationBatteries->where('work_status_code', '1')->count(),
                'energy' => $station->energy,
                'levels' => [
                    'very_high' => [
                        'count' => $stationBatteries->whereBetween('soc', [90, 100])->count(),
                        'charging' => $stationBatteries->whereBetween('soc', [90, 100])->where('work_status_code', '1')->count()
                    ],
                    'high' => [
                        'count' => $stationBatteries->whereBetween('soc', [70, 89])->count(),
                        'charging' => $stationBatteries->whereBetween('soc', [70, 89])->where('work_status_code', '1')->count()
                    ],
                    'medium' => [
                        'count' => $stationBatteries->whereBetween('soc', [40, 69])->count(),
                        'charging' => $stationBatteries->whereBetween('soc', [40, 69])->where('work_status_code', '1')->count()
                    ],
                    'low' => [
                        'count' => $stationBatteries->whereBetween('soc', [10, 39])->count(),
                        'charging' => $stationBatteries->whereBetween('soc', [10, 39])->where('work_status_code', '1')->count()
                    ],
                    'critical' => [
                        'count' => $stationBatteries->where('soc', '<', 10)->count(),
                        'charging' => $stationBatteries->where('soc', '<', 10)->where('work_status_code', '1')->count()
                    ]
                ],
                'compteurs' => $compteurs
            ];
        });
    }

    private function getStationsForMap()
    {
        return Agence::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['nom_agence as nom', 'latitude', 'longitude']);
    }

  private function getBatteryLocations()
{
    return BMSData::select('mac_id', 'latitude', 'longitude')
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->orderByDesc('timestamp')
        ->get()
        ->map(function ($item) {
            return [
                'mac_id' => $item->mac_id,
                'latitude' => $item->longitude,
                'longitude' => $item->latitude,
            ];
        })
        ->unique('mac_id')
        ->values();
}

    private function getChauffeursCount()
    {
        return AssociationUserMoto::count();
    }

    private function getMonthlySwapCount()
    {
        return Swap::whereMonth('swap_date', now()->month)->whereYear('swap_date', now()->year)->count();
    }

    private function getTotalDistance()
    {
        return (int) DailyDistance::sum('total_distance_km');
    }




      private function getMotosMapData()
    {
        try {
            return Cache::remember('motos_map_data', 120, function () {
                $motos = MotosValide::all();
                $result = [];

                foreach ($motos as $moto) {
                    $last = GpsLocation::where('macid', $moto->gps_imei)->latest('server_time')->first();
                    if (!$last || !$last->latitude || !$last->longitude) continue;

                    $association = AssociationUserMoto::where('moto_valide_id', $moto->id)->latest()->first();
                    $driver = $association && $association->validatedUser ? $association->validatedUser : null;

                    $result[] = [
                        'vin' => $moto->vin,
                        'macid' => $moto->gps_imei,
                        'driverInfo' => $driver ? $driver->nom . ' ' . $driver->prenom . ' - ' . $driver->phone : 'Non associé',
                        'driverName' => $driver ? $driver->nom . ' ' . $driver->prenom : null,
                        'latitude' => (float) $last->latitude,
                        'longitude' => (float) $last->longitude,
                        'lastUpdate' => $last->server_time,
                        'batteryLevel' => $last->battery_level ?? null,
                    ];
                }

                return $result;
            });
        } catch (Exception $e) {
            Log::error("Erreur récupération motos: " . $e->getMessage());
            return [];
        }
    }


//mettre a jour les donnée sur le front_end
   public function getFullDashboardData()
{
    try {
        $batteries = $this->getAllBatteryBmsData(BatteriesValide::all());

        return response()->json([
            'summaryStats' => $this->getSummaryStats($batteries),
            'levelStats' => $this->getLevelStats($batteries),
            'stationStats' => $this->getStationStats($batteries),
            'stations' => $this->getStationsWithBatteryDetails($batteries), // ✅ Ajouté pour la sidebar
            'chauffeursCount' => $this->getChauffeursCount(),
            'monthlySwapCount' => $this->getMonthlySwapCount(),
            'totalDistance' => $this->getTotalDistance(),
            'stationsMap' => $this->getStationsForMap(),
            'batteriesWithLocation' => $this->getBatteryLocations(),
            'motosWithLocation' => $this->getMotosMapData(),
            'timestamp' => now()->toISOString() // ✅ Pour le debug
        ]);
    } catch (\Throwable $e) {
        Log::error("Erreur dashboard JSON : " . $e->getMessage());
        return response()->json(['error' => 'Erreur serveur'], 500);
    }
}

}     