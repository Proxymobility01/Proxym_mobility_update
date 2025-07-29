<?php

namespace App\Services;

use App\Models\BMSData;
use App\Models\Agence;
use App\Models\BatteriesValide;
use App\Models\PowerReading;
use App\Models\Swap;
use App\Models\DailyDistance;
use App\Models\MotosValide;
use App\Models\GpsLocation;
use App\Models\AssociationUserMoto;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class DisplayDashboardService
{
    public function refreshDashboardCache()
{
    // Rafraîchir toutes les données du dashboard et les mettre en cache
    $agences = $this->getAllAgences();
    $batteriesAll = $this->getAllBatteriesValide();
    $latestBms = $this->getAllLatestBms();

    // Rafraîchir les batteries avec les données BMS
    $batteries = $this->getAllBatteryBmsData($batteriesAll, $latestBms);

    $data = $this->getFullDashboardData($batteries, $agences);

    Cache::put('dashboard_full_data', $data, 600);

    Log::info('♻️ Mise à jour du cache dashboard_full_data en cours...');
    Log::info('✅ Données enregistrées dans le cache : ', $data);
}


    public function getFullDashboardData($batteries, $agences)
    {
        return [
            'summaryStats' => $this->getSummaryStats($batteries),
            'levelStats' => $this->getLevelStats($batteries),  // Appel à la méthode getLevelStats
            'stationStats' => $this->getStationStats($batteries, $agences),
            'stations' => $this->getStationsWithBatteryDetails($batteries, $agences),
            'chauffeursCount' => $this->getChauffeursCount(),
            'monthlySwapCount' => $this->getMonthlySwapCount(),
            'totalDistance' => $this->getTotalDistance(),
            'stationsMap' => $this->getStationsForMap($agences),
            'batteriesWithLocation' => $this->getBatteryLocations(),
            'motosWithLocation' => $this->getMotosMapData(),
            'timestamp' => now()->toISOString(),
        ];
    }

    public function getAllAgences()
    {
        return Cache::remember('agences_all', 10, function () {
            return Agence::all()->keyBy(fn($a) => $a->id_agence ?: $a->id);
        });
    }

    public function getAllBatteriesValide()
    {
        return Cache::remember('batteries_valide_all', 600, function () {
            return BatteriesValide::with('agences')->get();
        });
    }

    public function getAllLatestBms()
    {
        return Cache::remember('latest_bms_data', 60, function () {
            return BMSData::select('bms_data.*')
                ->join(DB::raw('(SELECT mac_id, MAX(timestamp) as max_ts FROM bms_data GROUP BY mac_id) as sub'),
                    function ($join) {
                        $join->on('bms_data.mac_id', '=', 'sub.mac_id');
                        $join->on('bms_data.timestamp', '=', 'sub.max_ts');
                    })
                ->get()
                ->keyBy('mac_id');
        });
    }

    public function getAllBatteryBmsData($batteries, $latestBms)
    {
        return $batteries->map(function ($battery) use ($latestBms) {
            return Cache::remember("battery_bms_data_{$battery->mac_id}", 60, function () use ($battery, $latestBms) {
                $bms = $latestBms->get($battery->mac_id);

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
                    'online' => $online,
                ];
            });
        });
    }

    public function getBatteryStatus($code)
    {
        return [
            '0' => 'En décharge',
            '1' => 'En charge',
            '2' => 'En veille',
            '3' => 'Défaut',
        ][$code] ?? 'Inconnu';
    }

    public function getSummaryStats($batteries)
    {
        $total = $batteries->count();
        $inactive = $batteries->where('online', false)->count();
        $active = $total - $inactive;
        $charged = $batteries->where('soc', '>=', 95)->count();
        $charging = $batteries->where('work_status_code', '1')->count();
        $discharged = $batteries->where('soc', '<', 30)->count();
        $notCharging = $batteries->where('online', true)->where('work_status_code', '!=', '1')->count();

        return compact(
            'total',
            'active',
            'inactive',
            'charged',
            'charging',
            'discharged',
            'notCharging'
        );
    }

    public function getLevelStats($batteries)
    {
        // Plages de SOC (State of Charge)
        $ranges = [
            'very_high' => [90, 100],
            'high' => [70, 89],
            'medium' => [40, 69],
            'low' => [10, 39],
        ];

        return $this->computeLevelStats($batteries, $ranges);
    }

    public function computeLevelStats($batteries, $ranges)
    {
        $total = $batteries->count();
        $result = [];

        foreach ($ranges as $name => [$min, $max]) {
            $group = $batteries->filter(fn($b) => $b['soc'] >= $min && $b['soc'] <= $max);
            $count = $group->count();
            $charging = $group->where('work_status_code', '1')->count();

            $result[$name] = [
                'count' => $count,
                'charging' => $charging,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
            ];
        }

        return $result;
    }

    public function getStationStats($batteries, $agences)
    {
        return $agences->values()->map(function ($station) use ($batteries) {
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
                    '10-40' => $stationBatteries->whereBetween('soc', [10, 39])->count(),
                ],
            ];
        });
    }

    public function getStationsWithBatteryDetails($batteries, $agences)
    {
        return $agences->values()->map(function ($station) use ($batteries) {
            $stationId = $station->id_agence ?: $station->id;

            $stationBatteries = $batteries->filter(fn($b) => in_array($stationId, $b['station_ids']));

            $latestReading = Cache::remember("station_reading_$stationId", 10, function () use ($stationId) {
                return PowerReading::where('agence_id', $stationId)->latest()->first();
            });

            $compteurs = $latestReading
                ? [
                    $latestReading->kw1,
                    $latestReading->kw2,
                    $latestReading->kw3,
                    $latestReading->kw4,
                ]
                : ['N/A', 'N/A', 'N/A', 'N/A'];

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

    public function getStationsForMap($agences)
    {
        return $agences
            ->filter(fn($a) => $a->latitude && $a->longitude)
            ->values()
            ->map(fn($a) => [
                'nom' => $a->nom_agence,
                'latitude' => $a->latitude,
                'longitude' => $a->longitude,
            ]);
    }

    public function getBatteryLocations()
    {
        return Cache::remember('batteries_with_location', 60, function () {
            return BMSData::select('mac_id', 'latitude', 'longitude')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderByDesc('timestamp')
                ->get()
                ->unique('mac_id')
                ->values()
                ->map(fn($item) => [
                    'mac_id' => $item->mac_id,
                    'latitude' => $item->longitude,
                    'longitude' => $item->latitude,
                ]);
        });
    }

    public function getChauffeursCount()
    {
        return Cache::remember('chauffeurs_count', 3600, function () {
            return AssociationUserMoto::count();
        });
    }

    public function getMonthlySwapCount()
    {
        return Cache::remember('monthly_swap_count', 30, function () {
            return Swap::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        });
    }

    public function getTotalDistance()
    {
        return Cache::remember('dashboard_total_distance', 3600, function () {
            return (int) DailyDistance::sum('total_distance_km');
        });
    }

    public function getMotosMapData()
    {
        return Cache::remember('motos_map_data', 120, function () {
            $motos = MotosValide::all();

            $locations = GpsLocation::select(DB::raw('DISTINCT macid, latitude, longitude, server_time'))
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderBy('macid')
                ->orderByDesc('server_time')
                ->get()
                ->keyBy('macid');

            $result = [];

            foreach ($motos as $moto) {
                $last = $locations->get($moto->gps_imei);
                if (!$last) {
                    continue;
                }

                $association = AssociationUserMoto::where('moto_valide_id', $moto->id)->latest()->first();
                $driver = $association && $association->validatedUser ? $association->validatedUser : null;

                $result[] = [
                    'vin' => $moto->vin,
                    'macid' => $moto->gps_imei,
                    'driverInfo' => $driver
                        ? $driver->nom . ' ' . $driver->prenom . ' - ' . $driver->phone
                        : 'Non associé',
                    'driverName' => $driver ? $driver->nom . ' ' . $driver->prenom : null,
                    'latitude' => (float) $last->latitude,
                    'longitude' => (float) $last->longitude,
                    'lastUpdate' => $last->server_time,
                    'batteryLevel' => $last->battery_level ?? null,
                ];
            }

            return $result;
        });
    }
}
