<?php
namespace App\Http\Controllers;

use App\Models\Agence;
use App\Models\BatteriesValide;
use App\Models\Swap;
use App\Models\BMSData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $stationId = $request->get('station', 'all');
            $timeFilter = $request->get('time_filter', 'month');
            $periodFilter = $request->get('period_filter', 'current');

            // Debug: afficher les informations de base
            \Log::info("=== DEBUT DEBUG DASHBOARD ===");
            \Log::info("Nombre total de batteries: " . BatteriesValide::count());
            \Log::info("Nombre total d'agences: " . Agence::count());

            // Récupération des données batteries avec BMS
            $batteries = $this->getAllBatteryBmsData(BatteriesValide::all());
            
            // Debug: afficher un échantillon des données batteries
            \Log::info("Premier échantillon de batterie: " . json_encode($batteries->first()));
            
            // Statistiques globales
            $summaryStats = $this->getSummaryStats($batteries);
            \Log::info("Statistiques globales: " . json_encode($summaryStats));
            
            // Statistiques par paliers de charge
            $levelStats = $this->getLevelStats($batteries);
            
            // Statistiques par station
            $stationStats = $this->getStationStats($batteries);
            \Log::info("Statistiques par station: " . json_encode($stationStats));
            
            // Données filtées selon la station sélectionnée
            $filteredData = $this->getFilteredData($batteries, $stationId);
            
            // Données pour le graphique des swaps
            $swapChart = $this->getSwapChartData($timeFilter, $stationId);
            
            \Log::info("=== FIN DEBUG DASHBOARD ===");

            return view('dashboard', [
                'summaryStats' => $summaryStats,
                'levelStats' => $levelStats,
                'stationStats' => $stationStats,
                'filteredData' => $filteredData,
                'swapChart' => $swapChart,
                'stations' => Agence::all(),
                'selectedStation' => $stationId,
                'timeFilter' => $timeFilter,
                'periodFilter' => $periodFilter
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dashboard: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return view('dashboard.index', [
                'error' => 'Une erreur est survenue lors du chargement du dashboard: ' . $e->getMessage(),
                'summaryStats' => $this->getDefaultStats(),
                'levelStats' => $this->getDefaultLevelStats(),
                'stationStats' => [],
                'filteredData' => null,
                'swapChart' => $this->getDefaultSwapChart(),
                'stations' => Agence::all(),
                'selectedStation' => 'all',
                'timeFilter' => 'month',
                'periodFilter' => 'current'
            ]);
        }
    }

    /**
     * Récupère toutes les données BMS des batteries
     */
    private function getAllBatteryBmsData($batteries)
    {
        return $batteries->map(function ($battery) {
            // Debug: afficher les informations de base de la batterie
            \Log::info("Traitement batterie ID: {$battery->id}, MAC: {$battery->mac_id}");
            
            $bms = Cache::remember("battery_bms_{$battery->mac_id}", 60, function () use ($battery) {
                return BMSData::where('mac_id', $battery->mac_id)->latest('timestamp')->first();
            });

            $state = json_decode($bms->state ?? '{}', true);
            $seting = json_decode($bms->seting ?? '{}', true);
            $soc = $state['SOC'] ?? 0;
            $workStatus = $state['WorkStatus'] ?? '3';
            $status = $this->getBatteryStatus($workStatus);

            // Vérification si la batterie est en ligne
            $online = false;
            if (isset($seting['heart_time'])) {
                try {
                    $online = Carbon::createFromTimestamp($seting['heart_time'])->diffInMinutes(now()) < 5;
                } catch (\Exception $e) {
                    $online = false;
                }
            }

            // Récupération des stations - CORRECTION: utiliser l'ID au lieu de id_agence
            $stationIds = [];
            $stationNames = [];
            
            // Debug: afficher les relations
            if ($battery->agences) {
                $agences = $battery->agences;
                \Log::info("Batterie {$battery->id} - Nombre d'agences: " . $agences->count());
                
                // CORRECTION: Utiliser 'id' au lieu de 'id_agence' si id_agence est null
                $stationIds = $agences->map(function($agence) {
                    return $agence->id_agence ?: $agence->id; // Utiliser id si id_agence est null
                })->toArray();
                
                $stationNames = $agences->pluck('nom_agence')->toArray();
                
                \Log::info("Batterie {$battery->id} - Station IDs: " . json_encode($stationIds));
                \Log::info("Batterie {$battery->id} - Station Names: " . json_encode($stationNames));
            } else {
                \Log::warning("Batterie {$battery->id} - Aucune relation agences trouvée");
                
                // Alternative: vérifier s'il y a une colonne station_id directe
                if (isset($battery->station_id)) {
                    $stationIds = [$battery->station_id];
                    $station = Agence::find($battery->station_id);
                    $stationNames = $station ? [$station->nom_agence] : [];
                } elseif (isset($battery->id_agence)) {
                    $stationIds = [$battery->id_agence];
                    $station = Agence::find($battery->id_agence);
                    $stationNames = $station ? [$station->nom_agence] : [];
                }
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
                'last_communication' => isset($seting['heart_time']) ? 
                    Carbon::createFromTimestamp($seting['heart_time'])->format('Y-m-d H:i:s') : null,
                'bms_data' => $bms
            ];
        });
    }

    /**
     * Détermine le statut de la batterie selon le code
     */
    private function getBatteryStatus($code)
    {
        return [
            '0' => 'En décharge',
            '1' => 'En charge',
            '2' => 'En veille',
            '3' => 'Défaut'
        ][$code] ?? 'Inconnu';
    }

    /**
     * Calcule les statistiques générales
     */
    private function getSummaryStats($batteries)
    {
        $total = $batteries->count();
        $inactive = $batteries->where('online', false)->count();
        $active = $total - $inactive;
        
        $charged = $batteries->where('soc', '>=', 95)->count();
        $charging = $batteries->where('work_status_code', '1')->count();
        $discharged = $batteries->where('soc', '<', 30)->count();
        
        // Batteries pas en charge = actives mais pas en charge
        $notCharging = $batteries->where('online', true)
                               ->where('work_status_code', '!=', '1')
                               ->count();

        return [
            'total' => $total,
            'active_total' => $active,
            'inactive' => $inactive,
            'charged' => $charged,
            'charging' => $charging,
            'discharged' => $discharged,
            'not_charging' => $notCharging
        ];
    }

    /**
     * Calcule les statistiques par paliers de charge
     */
    private function getLevelStats($batteries)
    {
        $total = $batteries->count();
        
        $levels = [
            'very_high' => $batteries->whereBetween('soc', [90, 100]),
            'high' => $batteries->whereBetween('soc', [70, 89]),
            'medium' => $batteries->whereBetween('soc', [40, 69]),
            'low' => $batteries->whereBetween('soc', [10, 39]),
            'unknown' => $batteries->where('soc', 0)
        ];

        $levelStats = [];
        foreach ($levels as $key => $levelBatteries) {
            $count = $levelBatteries->count();
            $charging = $levelBatteries->where('work_status_code', '1')->count();
            $notCharging = $count - $charging;
            
            $levelStats[$key] = [
                'count' => $count,
                'charging' => $charging,
                'not_charging' => $notCharging,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0
            ];
        }
        
        $levelStats['total'] = $total;
        
        return $levelStats;
    }

    /**
     * Calcule les statistiques par station
     */
    private function getStationStats($batteries)
    {
        $stations = Agence::all();
        $stationStats = [];

        foreach ($stations as $station) {
            // CORRECTION: Utiliser 'id' au lieu de 'id_agence' si id_agence est null
            $stationIdentifier = $station->id_agence ?: $station->id;
            
            // Debug: vérifier les IDs des stations
            \Log::info("Station ID: {$stationIdentifier}, Nom: {$station->nom_agence}");
            
            $stationBatteries = $batteries->filter(function ($battery) use ($stationIdentifier) {
                // Debug: afficher les station_ids de chaque batterie
                $hasStation = in_array($stationIdentifier, $battery['station_ids']);
                if ($hasStation) {
                    \Log::info("Batterie trouvée pour station {$stationIdentifier}: " . json_encode($battery['station_ids']));
                }
                return $hasStation;
            });

            // Debug: compter les batteries trouvées
            $batteryCount = $stationBatteries->count();
            \Log::info("Station {$station->nom_agence}: {$batteryCount} batteries trouvées");

            $total = $batteryCount;
            $charged = $stationBatteries->where('soc', '>=', 95)->count();
            $charging = $stationBatteries->where('work_status_code', '1')->count();
            $discharged = $stationBatteries->where('soc', '<', 30)->count();
            $inactive = $stationBatteries->where('online', false)->count();

            // Paliers par station avec debug
            $paliers = [
                '90-100' => $stationBatteries->filter(function($b) { return $b['soc'] >= 90 && $b['soc'] <= 100; })->count(),
                '70-90' => $stationBatteries->filter(function($b) { return $b['soc'] >= 70 && $b['soc'] < 90; })->count(),
                '40-70' => $stationBatteries->filter(function($b) { return $b['soc'] >= 40 && $b['soc'] < 70; })->count(),
                '10-40' => $stationBatteries->filter(function($b) { return $b['soc'] >= 10 && $b['soc'] < 40; })->count()
            ];

            \Log::info("Station {$station->nom_agence} - Paliers: " . json_encode($paliers));

            $stationStats[] = [
                'id' => $stationIdentifier,
                'name' => $station->nom_agence,
                'stats' => [
                    'total' => $total,
                    'charged' => $charged,
                    'charging' => $charging,
                    'discharged' => $discharged,
                    'inactive' => $inactive,
                    'active' => $total - $inactive
                ],
                'paliers' => $paliers
            ];
        }

        return $stationStats;
    }

    /**
     * Récupère les données filtrées selon la station
     */
    private function getFilteredData($batteries, $stationId)
    {
        if ($stationId === 'all') {
            return null; // Utiliser les données globales
        }

        $station = Agence::find($stationId);
        if (!$station) {
            return null;
        }

        $filteredBatteries = $batteries->filter(function ($battery) use ($stationId) {
            return in_array($stationId, $battery['station_ids']);
        });

        return [
            'station_name' => $station->nom_agence,
            'summary_stats' => $this->getSummaryStats($filteredBatteries),
            'level_stats' => $this->getLevelStats($filteredBatteries)
        ];
    }

    /**
     * Récupère les données pour le graphique des swaps
     */
    private function getSwapChartData($timeFilter, $stationId = 'all')
    {
        try {
            $query = Swap::query();
            
            // Filtrer par station si nécessaire
            if ($stationId !== 'all') {
                $query->where('station_id', $stationId);
            }

            // Définir la période selon le filtre
            $startDate = match ($timeFilter) {
                'day' => now()->startOfWeek(),
                'week' => now()->startOfMonth(),
                'month' => now()->startOfYear(),
                'year' => now()->subYears(5)->startOfYear(),
                default => now()->startOfYear()
            };

            $swaps = $query->where('created_at', '>=', $startDate)
                          ->orderBy('created_at')
                          ->get();

            return $this->formatSwapChartData($swaps, $timeFilter);

        } catch (\Exception $e) {
            \Log::error('Erreur graphique swaps: ' . $e->getMessage());
            return $this->getStaticSwapData($timeFilter, $stationId);
        }
    }

    /**
     * Formate les données du graphique des swaps
     */
    private function formatSwapChartData($swaps, $timeFilter)
    {
        $labels = $this->getChartLabels($timeFilter);
        $swapCounts = array_fill(0, count($labels), 0);
        $amounts = array_fill(0, count($labels), 0);

        foreach ($swaps as $swap) {
            $index = $this->getSwapIndex($swap->created_at, $timeFilter);
            if ($index !== null && $index < count($labels)) {
                $swapCounts[$index]++;
                $amounts[$index] += $swap->amount ?? 0;
            }
        }

        return [
            'labels' => $labels,
            'swaps' => $swapCounts,
            'amounts' => $amounts,
            'timeUnitLabel' => $this->getTimeUnitLabel($timeFilter)
        ];
    }

    /**
     * Génère les labels pour le graphique selon le filtre
     */
    private function getChartLabels($timeFilter)
    {
        return match ($timeFilter) {
            'day' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'],
            'week' => ['Semaine 1', 'Semaine 2', 'Semaine 3', 'Semaine 4'],
            'month' => ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'],
            'year' => ['2020', '2021', '2022', '2023', '2024', '2025'],
            default => []
        };
    }

    /**
     * Détermine l'index d'un swap dans le graphique
     */
    private function getSwapIndex($date, $timeFilter)
    {
        $carbonDate = Carbon::parse($date);
        
        return match ($timeFilter) {
            'day' => $carbonDate->dayOfWeek - 1,
            'week' => ceil($carbonDate->day / 7) - 1,
            'month' => $carbonDate->month - 1,
            'year' => $carbonDate->year - 2020,
            default => null
        };
    }

    /**
     * API endpoint pour les données des batteries inactives
     */
    public function getInactiveBatteries(Request $request)
    {
        try {
            $stationId = $request->get('station', 'all');
            $batteries = $this->getAllBatteryBmsData(BatteriesValide::all());
            
            $inactiveBatteries = $batteries->filter(function ($battery) use ($stationId) {
                $matchesStation = $stationId === 'all' || in_array($stationId, $battery['station_ids']);
                return !$battery['online'] && $matchesStation;
            });

            $result = $inactiveBatteries->map(function ($battery) {
                return [
                    'id' => $battery['id'],
                    'mac_id' => $battery['mac_id'],
                    'stations' => implode(', ', $battery['station_names']),
                    'last_communication' => $battery['last_communication'],
                    'last_soc' => $battery['soc'],
                    'status' => $battery['status']
                ];
            })->values();

            return response()->json([
                'success' => true,
                'count' => $result->count(),
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des batteries inactives'
            ], 500);
        }
    }

    /**
     * Données par défaut en cas d'erreur
     */
    private function getDefaultStats()
    {
        return [
            'total' => 0,
            'active_total' => 0,
            'inactive' => 0,
            'charged' => 0,
            'charging' => 0,
            'discharged' => 0,
            'not_charging' => 0
        ];
    }

    private function getDefaultLevelStats()
    {
        return [
            'very_high' => ['count' => 0, 'charging' => 0, 'not_charging' => 0, 'percentage' => 0],
            'high' => ['count' => 0, 'charging' => 0, 'not_charging' => 0, 'percentage' => 0],
            'medium' => ['count' => 0, 'charging' => 0, 'not_charging' => 0, 'percentage' => 0],
            'low' => ['count' => 0, 'charging' => 0, 'not_charging' => 0, 'percentage' => 0],
            'unknown' => ['count' => 0, 'charging' => 0, 'not_charging' => 0, 'percentage' => 0],
            'total' => 0
        ];
    }

    private function getDefaultSwapChart()
    {
        return [
            'labels' => [],
            'swaps' => [],
            'amounts' => [],
            'timeUnitLabel' => 'Aucune donnée'
        ];
    }

    /**
     * Génère les données de test pour le graphique d'évolution des swaps
     */
    private function getStaticSwapData($timeFilter, $stationId = 'all')
    {
        $labels = $this->getChartLabels($timeFilter);
        
        return [
            'labels' => $labels,
            'swaps' => array_fill(0, count($labels), rand(10, 100)),
            'amounts' => array_fill(0, count($labels), rand(5000, 50000)),
            'timeUnitLabel' => $this->getTimeUnitLabel($timeFilter)
        ];
    }

    /**
     * Renvoie le libellé de l'unité de temps
     */
    private function getTimeUnitLabel($filter)
    {
        return match ($filter) {
            'day' => 'Jours de la semaine',
            'week' => 'Semaines du mois en cours',
            'month' => 'Mois de l\'année',
            'year' => 'Années',
            'custom' => 'Période personnalisée',
            default => 'Période',
        };
    }

    /**
     * Route pour le filtrage AJAX
     */
    public function filter(Request $request)
    {
        $stationId = $request->get('station', 'all');
        $timeFilter = $request->get('time_filter', 'month');
        $periodFilter = $request->get('period_filter', 'current');

        return redirect()->route('dashboard.index', [
            'station' => $stationId,
            'time_filter' => $timeFilter,
            'period_filter' => $periodFilter
        ]);
    }
}