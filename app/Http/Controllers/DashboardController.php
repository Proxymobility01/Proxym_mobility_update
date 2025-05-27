<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BatteriesValide;
use App\Models\BatteryAgence;
use App\Models\Agence;
use App\Models\BMSData;
use App\Models\Swap;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Récupération des paramètres
            $stations = Agence::all();
            $selectedStation = $request->input('station', 'all');
            $timeFilter = $request->input('time_filter', 'week');
            $periodFilter = $request->input('period_filter', 'current');

            // Récupération des données des batteries
            $batteryData = $this->getAllBatteryBmsData(BatteriesValide::all());
            
            // Statistiques globales
            $globalStats = $this->getGlobalStats($batteryData);
            
            // Statistiques par station
            $stationStats = $this->getStationBatteryStats($batteryData);

            // Sélection des données en fonction de la station choisie
            if ($selectedStation !== 'all') {
                $selectedStationData = $this->getStationData($selectedStation, $batteryData);
                $summaryStats = $selectedStationData['summary'];
                $levelStats = $selectedStationData['levels'];
            } else {
                $summaryStats = $globalStats['summary'];
                $levelStats = $globalStats['levels'];
            }

            return view('dashboard', compact(
                'stations', 'selectedStation', 'timeFilter', 'periodFilter',
                'summaryStats', 'levelStats', 'stationStats'
            ));
        } catch (Exception $e) {
            Log::error('Erreur dans DashboardController@index: ' . $e->getMessage());
            return view('dashboard', ['error' => 'Erreur lors du chargement du dashboard: ' . $e->getMessage()]);
        }
    }

    public function filter(Request $request)
    {
        try {
            // Récupération des paramètres
            $stations = Agence::all();
            $selectedStation = $request->input('station', 'all');
            $timeFilter = $request->input('time_filter', 'week');
            $periodFilter = $request->input('period_filter', 'current');

            // Récupération des données des batteries
            $batteryData = $this->getAllBatteryBmsData(BatteriesValide::all());
            
            // Statistiques globales
            $globalStats = $this->getGlobalStats($batteryData);
            
            // Statistiques par station
            $stationStats = $this->getStationBatteryStats($batteryData);

            // Sélection des données en fonction de la station choisie
            if ($selectedStation !== 'all') {
                $selectedStationData = $this->getStationData($selectedStation, $batteryData);
                $summaryStats = $selectedStationData['summary'];
                $levelStats = $selectedStationData['levels'];
            } else {
                $summaryStats = $globalStats['summary'];
                $levelStats = $globalStats['levels'];
            }

            return view('dashboard', compact(
                'stations', 'selectedStation', 'timeFilter', 'periodFilter',
                'summaryStats', 'levelStats', 'stationStats'
            ));
        } catch (Exception $e) {
            Log::error('Erreur dans DashboardController@filter: ' . $e->getMessage());
            return view('dashboard', ['error' => 'Erreur lors du filtrage: ' . $e->getMessage()]);
        }
    }

    public function filterData(Request $request)
    {
        try {
            $stationId = $request->input('station', 'all');
            $timeFilter = $request->input('time_filter', 'week');
            $periodFilter = $request->input('period_filter', 'current');

            // Récupération des données des batteries
            $batteryData = $this->getAllBatteryBmsData(BatteriesValide::all());
            
            // Statistiques globales
            $globalStats = $this->getGlobalStats($batteryData);
            
            // Statistiques par station
            $stationStats = $this->getStationBatteryStats($batteryData);

            // Sélection des données en fonction de la station choisie
            if ($stationId !== 'all') {
                $selectedStationData = $this->getStationData($stationId, $batteryData);
                $summaryStats = $selectedStationData['summary'];
                $levelStats = $selectedStationData['levels'];
            } else {
                $summaryStats = $globalStats['summary'];
                $levelStats = $globalStats['levels'];
            }

            return response()->json([
                'batterySummary' => $summaryStats,
                'batteryLevels' => $levelStats,
                'stationStats' => $stationStats,
                'globalStats' => $globalStats,
                'lastUpdate' => now()->format('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            Log::error('Erreur dans filterData: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du filtrage: ' . $e->getMessage()], 500);
        }
    }

    public function refreshData(Request $request)
    {
        try {
            $stationId = $request->input('station', 'all');
            
            // Récupération des données des batteries
            $batteryData = $this->getAllBatteryBmsData(BatteriesValide::all());
            
            // Statistiques globales
            $globalStats = $this->getGlobalStats($batteryData);
            
            // Statistiques par station
            $stationStats = $this->getStationBatteryStats($batteryData);

            // Sélection des données en fonction de la station choisie
            if ($stationId !== 'all') {
                $selectedStationData = $this->getStationData($stationId, $batteryData);
                $summaryStats = $selectedStationData['summary'];
                $levelStats = $selectedStationData['levels'];
            } else {
                $summaryStats = $globalStats['summary'];
                $levelStats = $globalStats['levels'];
            }

            return response()->json([
                'batterySummary' => $summaryStats,
                'batteryLevels' => $levelStats,
                'stationStats' => $stationStats,
                'globalStats' => $globalStats,
                'lastUpdate' => now()->format('H:i:s')
            ]);
        } catch (Exception $e) {
            Log::error('Erreur dans refreshData: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de l\'actualisation: ' . $e->getMessage()], 500);
        }
    }

    public function getBatteryDetails($id)
    {
        try {
            $battery = BatteriesValide::findOrFail($id);
            $bmsData = $this->getLastBMSData($battery->mac_id);
            $stations = Agence::whereIn('id', BatteryAgence::where('id_battery_valide', $battery->id)->pluck('id_agence'))->pluck('nom_agence')->implode(', ');
            
            // Obtenir le statut et le niveau de la batterie
            $soc = (int)($bmsData['SOC'] ?? 0);
            $status = $this->getBatteryStatusFromData($bmsData, $soc);
            
            // Récupérer l'historique des swaps récents pour cette batterie
            $swapsHistory = Swap::where('mac_id', $battery->mac_id)
                ->orderBy('swap_date', 'desc')
                ->limit(10)
                ->get()
                ->map(function($swap) {
                    $station = Agence::find($swap->id_agence);
                    return [
                        'date' => Carbon::parse($swap->swap_date)->format('d/m/Y H:i'),
                        'station' => $station ? $station->nom_agence : 'Inconnue',
                        'amount' => $swap->swap_price,
                        'soc_before' => $swap->soc_before ?? 'N/A',
                        'soc_after' => $swap->soc_after ?? 'N/A'
                    ];
                });
            
            // Formatage de la date de dernière communication
            $lastCommunication = isset($bmsData['BMS_DateTime']) 
                ? Carbon::createFromTimestamp($bmsData['BMS_DateTime'])->format('d/m/Y H:i:s')
                : null;
            
            return response()->json([
                'id' => $battery->id,
                'mac_id' => $battery->mac_id,
                'serial_number' => $battery->serial_number ?? 'N/A',
                'stations' => $stations,
                'status' => $status,
                'bms_data' => $bmsData,
                'last_communication' => $lastCommunication,
                'swaps_history' => count($swapsHistory) > 0 ? $swapsHistory : null
            ]);
        } catch (Exception $e) {
            Log::error('Erreur dans getBatteryDetails: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des détails de la batterie: ' . $e->getMessage()], 500);
        }
    }

    public function getInactiveBatteries()
    {
        try {
            // Récupération de toutes les batteries
            $batteryData = $this->getAllBatteryBmsData(BatteriesValide::all());
            $inactiveBatteries = [];
            
            // Filtrer les batteries inactives
            foreach ($batteryData as $batteryId => $data) {
                if ($data['status'] === 'inactive') {
                    // Récupérer les noms des stations associées
                    $stationNames = Agence::whereIn('id', $data['station_ids'])
                        ->pluck('nom_agence')
                        ->implode(', ');
                    
                    // Formatage de la date de dernière communication
                    $lastCommunication = isset($data['bms_data']['BMS_DateTime']) 
                        ? Carbon::createFromTimestamp($data['bms_data']['BMS_DateTime'])->format('d/m/Y H:i:s')
                        : null;
                    
                    $inactiveBatteries[] = [
                        'id' => $batteryId,
                        'mac_id' => $data['battery']->mac_id,
                        'serial_number' => $data['battery']->serial_number ?? 'N/A',
                        'stations' => $stationNames,
                        'last_communication' => $lastCommunication ?? 'Jamais',
                        'last_soc' => $data['soc'] . '%'
                    ];
                }
            }
            
            return response()->json([
                'inactiveBatteries' => $inactiveBatteries,
                'count' => count($inactiveBatteries)
            ]);
        } catch (Exception $e) {
            Log::error('Erreur dans getInactiveBatteries: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des batteries inactives: ' . $e->getMessage()], 500);
        }
    }

    public function swapEvolution(Request $request)
    {
        try {
            $station = $request->input('station', 'all');
            $timeFilter = $request->input('time_filter', 'month');
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', date('Y-m'));
            $start = $request->input('start');
            $end = $request->input('end');

            // Construction de la requête de base
            $query = Swap::query();
            
            // Filtre par station
            if ($station !== 'all') {
                $query->where('id_agence', $station);
            }
            
            // Variables pour stocker les données
            $labels = [];
            $swapData = [];
            $amountData = [];
            
            // Traitement selon le type de filtre temporel
            switch ($timeFilter) {
                case 'day':
                    // Filtrer par jours de la semaine en cours
                    $startOfWeek = Carbon::now()->startOfWeek();
                    $endOfWeek = Carbon::now()->endOfWeek();
                    $query->whereBetween('swap_date', [$startOfWeek, $endOfWeek]);
                    
                    // Générer les labels pour les jours de la semaine
                    $labels = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
                    
                    // Regrouper par jour de la semaine (1 = lundi, 7 = dimanche)
                    $results = $query->selectRaw('DAYOFWEEK(swap_date) as day_num, COUNT(*) as count, SUM(swap_price) as total')
                        ->groupBy('day_num')
                        ->get();
                    
                    // Initialiser les tableaux avec des zéros
                    $swapData = array_fill(0, 7, 0);
                    $amountData = array_fill(0, 7, 0);
                    
                    // Remplir les données
                    foreach ($results as $result) {
                        // Convertir DAYOFWEEK (1=dimanche, 7=samedi) vers notre format (0=lundi, 6=dimanche)
                        $index = ($result->day_num + 5) % 7;
                        $swapData[$index] = (int)$result->count;
                        $amountData[$index] = (float)$result->total;
                    }
                    break;
                
                case 'week':
                    // Extraire l'année et le mois du paramètre
                    list($yearVal, $monthVal) = explode('-', $month);
                    
                    // Calculer le nombre de semaines dans le mois
                    $startDate = Carbon::createFromDate($yearVal, $monthVal, 1);
                    $endDate = Carbon::createFromDate($yearVal, $monthVal, 1)->endOfMonth();
                    
                    // Filtrer par le mois sélectionné
                    $query->whereYear('swap_date', $yearVal)
                        ->whereMonth('swap_date', $monthVal);
                    
                    // Générer les labels pour les semaines
                    $currentDate = clone $startDate;
                    $weekNumber = 1;
                    
                    while ($currentDate <= $endDate) {
                        $weekStart = clone $currentDate->startOfWeek();
                        $weekEnd = clone $currentDate->endOfWeek();
                        
                        if ($weekStart->month != $monthVal && $weekStart < $startDate) {
                            $weekStart = clone $startDate;
                        }
                        
                        if ($weekEnd->month != $monthVal && $weekEnd > $endDate) {
                            $weekEnd = clone $endDate;
                        }
                        
                        $labels[] = "S{$weekNumber} (" . $weekStart->format('d/m') . "-" . $weekEnd->format('d/m') . ")";
                        
                        // Passer à la semaine suivante
                        $currentDate->addWeek();
                        $weekNumber++;
                    }
                    
                    // Initialiser les tableaux avec des zéros
                    $swapData = array_fill(0, count($labels), 0);
                    $amountData = array_fill(0, count($labels), 0);
                    
                    // Pour chaque semaine, compter les swaps
                    for ($i = 0; $i < count($labels); $i++) {
                        $weekStartDate = clone $startDate;
                        $weekStartDate->addWeeks($i)->startOfWeek();
                        
                        if ($weekStartDate->month != $monthVal && $weekStartDate < $startDate) {
                            $weekStartDate = clone $startDate;
                        }
                        
                        $weekEndDate = clone $weekStartDate->endOfWeek();
                        
                        if ($weekEndDate->month != $monthVal && $weekEndDate > $endDate) {
                            $weekEndDate = clone $endDate;
                        }
                        
                        $weeklyCount = clone $query;
                        $weekResults = $weeklyCount->whereBetween('swap_date', [$weekStartDate, $weekEndDate])
                            ->selectRaw('COUNT(*) as count, SUM(swap_price) as total')
                            ->first();
                        
                        $swapData[$i] = $weekResults ? (int)$weekResults->count : 0;
                        $amountData[$i] = $weekResults ? (float)$weekResults->total : 0;
                    }
                    break;
                
                case 'month':
                    // Filtrer par année
                    $query->whereYear('swap_date', $year);
                    
                    // Générer les labels pour les mois
                    $labels = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                    
                    // Regrouper par mois
                    $results = $query->selectRaw('MONTH(swap_date) as month, COUNT(*) as count, SUM(swap_price) as total')
                        ->groupBy('month')
                        ->get();
                    
                    // Initialiser les tableaux avec des zéros
                    $swapData = array_fill(0, 12, 0);
                    $amountData = array_fill(0, 12, 0);
                    
                    // Remplir les données
                    foreach ($results as $result) {
                        $index = $result->month - 1; // Les mois commencent à 1
                        $swapData[$index] = (int)$result->count;
                        $amountData[$index] = (float)$result->total;
                    }
                    break;
                
                case 'year':
                    // Déterminer la plage d'années (5 dernières années jusqu'à l'année en cours)
                    $startYear = date('Y') - 4;
                    $endYear = date('Y');
                    
                    // Générer les labels pour les années
                    $labels = range($startYear, $endYear);
                    
                    // Filtrer par la plage d'années
                    $query->whereYear('swap_date', '>=', $startYear)
                        ->whereYear('swap_date', '<=', $endYear);
                    
                    // Regrouper par année
                    $results = $query->selectRaw('YEAR(swap_date) as year, COUNT(*) as count, SUM(swap_price) as total')
                        ->groupBy('year')
                        ->get();
                    
                    // Initialiser les tableaux avec des zéros
                    $swapData = array_fill(0, count($labels), 0);
                    $amountData = array_fill(0, count($labels), 0);
                    
                    // Remplir les données
                    foreach ($results as $result) {
                        $index = array_search($result->year, $labels);
                        if ($index !== false) {
                            $swapData[$index] = (int)$result->count;
                            $amountData[$index] = (float)$result->total;
                        }
                    }
                    break;
                
                case 'custom':
                    // Vérifier que les dates sont fournies
                    if (!$start || !$end) {
                        return response()->json(['error' => 'Les dates de début et de fin sont requises'], 400);
                    }
                    
                    // Convertir les dates en objets Carbon
                    $startDate = Carbon::parse($start);
                    $endDate = Carbon::parse($end);
                    
                    // Vérifier que les dates sont valides et que la date de fin est après la date de début
                    if ($startDate >= $endDate) {
                        return response()->json(['error' => 'La date de fin doit être après la date de début'], 400);
                    }
                    
                    // Limiter la plage à 31 jours maximum pour éviter les performances lentes
                    $maxEndDate = (clone $startDate)->addDays(30);
                    if ($endDate > $maxEndDate) {
                        $endDate = $maxEndDate;
                    }
                    
                    // Filtrer par la plage de dates
                    $query->whereBetween('swap_date', [$startDate, $endDate]);
                    
                    // Générer les labels pour chaque jour
                    $currentDate = clone $startDate;
                    while ($currentDate <= $endDate) {
                        $labels[] = $currentDate->format('d/m');
                        $currentDate->addDay();
                    }
                    
                    // Regrouper par jour
                    $results = $query->selectRaw('DATE(swap_date) as date, COUNT(*) as count, SUM(swap_price) as total')
                        ->groupBy('date')
                        ->get();
                    
                    // Initialiser les tableaux avec des zéros
                    $swapData = array_fill(0, count($labels), 0);
                    $amountData = array_fill(0, count($labels), 0);
                    
                    // Remplir les données
                    foreach ($results as $result) {
                        $resultDate = Carbon::parse($result->date);
                        $index = $startDate->diffInDays($resultDate);
                        if ($index < count($labels)) {
                            $swapData[$index] = (int)$result->count;
                            $amountData[$index] = (float)$result->total;
                        }
                    }
                    break;
                
                default:
                    return response()->json(['error' => 'Type de filtre temporel non valide'], 400);
            }
            
            // Préparer la réponse
            $datasets = [
                [
                    'label' => 'Nombre de Swaps',
                    'backgroundColor' => 'rgba(220, 219, 50, 0.8)',
                    'borderColor' => 'rgba(220, 219, 50, 1)',
                    'borderWidth' => 1,
                    'data' => $swapData,
                    'yAxisID' => 'y'
                ],
                [
                    'label' => 'Montant (FCFA)',
                    'backgroundColor' => 'rgba(46, 204, 113, 0.6)',
                    'borderColor' => 'rgba(46, 204, 113, 1)',
                    'borderWidth' => 1,
                    'data' => $amountData,
                    'yAxisID' => 'y1',
                    'type' => 'line'
                ]
            ];
            
            return response()->json([
                'labels' => $labels,
                'datasets' => $datasets
            ]);
        } catch (Exception $e) {
            Log::error('Erreur dans swapEvolution: ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des données d\'évolution: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Récupère les données BMS pour toutes les batteries
     */
    private function getAllBatteryBmsData($batteries)
    {
        $result = [];

        foreach ($batteries as $battery) {
            $cacheKey = 'bms_data_' . $battery->mac_id;

            $bmsData = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($battery) {
                return $this->getLastBMSData($battery->mac_id);
            });

            $stationIds = BatteryAgence::where('id_battery_valide', $battery->id)->pluck('id_agence')->toArray();

            $soc = (int)($bmsData['SOC'] ?? 0);
            $status = $this->getBatteryStatusFromData($bmsData, $soc);
            $level = $this->getBatteryLevel($soc);

            $result[$battery->id] = [
                'battery' => $battery,
                'bms_data' => $bmsData ?? [],
                'status' => $status,
                'level' => $level,
                'soc' => $soc,
                'station_ids' => $stationIds,
            ];
        }

        return $result;
    }

    /**
     * Récupère les dernières données BMS pour une batterie
     */
    private function getLastBMSData($mac_id)
    {
        $bms = BMSData::where('mac_id', $mac_id)->latest('timestamp')->first();
        if (!$bms) return [];

        $state = json_decode($bms->state, true) ?? [];
        $setting = json_decode($bms->seting, true) ?? [];
        $data = array_merge($state, $setting);
        $data['BMS_DateTime'] = $data['BMS_DateTime'] ?? strtotime($bms->timestamp);

        return $data;
    }

    /**
     * Détermine le statut d'une batterie à partir des données BMS
     */
    private function getBatteryStatusFromData($bmsData, $soc)
    {
        // Si pas de données BMS ou dernière communication > 10 minutes, considérer comme inactive
        if (empty($bmsData) || !isset($bmsData['BMS_DateTime']) || (time() - (int)$bmsData['BMS_DateTime'] >= 600)) {
            return 'inactive';
        }

        $workStatus = $this->getBatteryStatus($bmsData['WorkStatus'] ?? '');

        // Déterminer le statut selon les critères
        if ($workStatus === 'Charging') {
            return 'charging';
        } elseif ($soc < 30) {
            return 'discharged';
        } elseif ($workStatus === 'Idle' && $soc >= 95) {
            return 'charged';
        } elseif ($workStatus === 'Idle') {
            return 'discharged';  // Idle mais pas complètement chargée, considérer comme déchargée
        } else {
            return 'unknown';
        }
    }

    /**
     * Convertit le code de statut de travail en libellé
     */
    private function getBatteryStatus($code)
    {
        return match ((string)$code) {
            '0' => 'Discharging',
            '1' => 'Charging',
            '2' => 'Idle',
            default => 'Unknown',
        };
    }

    /**
     * Détermine le niveau de charge d'une batterie selon son SOC
     */
    private function getBatteryLevel($soc)
    {
        return match (true) {
            $soc > 90 => 'very_high',
            $soc > 50 => 'high',
            $soc > 30 => 'medium',
            $soc >= 0 => 'low',
            default => 'unknown',
        };
    }

    /**
     * Calcule les statistiques globales pour un ensemble de batteries
     */
    private function getGlobalStats($batteryData)
    {
        // Initialiser les compteurs
        $summary = [
            'charged' => 0,
            'charging' => 0,
            'discharged' => 0,
            'inactive' => 0,
            'unknown' => 0
        ];
        
        $levels = [
            'very_high' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'unknown' => 0
        ];

        // Compter les batteries par statut et niveau
        foreach ($batteryData as $data) {
            $summary[$data['status']]++;
            $levels[$data['level']]++;
        }

        // Calculer les totaux
        $total = count($batteryData);
        $active = $summary['charged'] + $summary['charging'] + $summary['discharged'];

        // Calculer les pourcentages pour les niveaux
        $levelsWithPercentage = [];
        foreach ($levels as $level => $count) {
            $levelsWithPercentage[$level] = [
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100) : 0
            ];
        }

        return [
            'summary' => array_merge($summary, [
                'total' => $total,
                'active_total' => $active
            ]),
            'levels' => array_merge($levelsWithPercentage, ['total' => $total])
        ];
    }

    /**
     * Récupère les statistiques pour chaque station
     */
    private function getStationBatteryStats($batteryData)
    {
        $stationStats = [];
        
        // Pour chaque station
        foreach (Agence::all() as $station) {
            // Filtrer les batteries pour cette station
            $stationBatteries = array_filter($batteryData, function($battery) use ($station) {
                return in_array($station->id, $battery['station_ids']);
            });
            
            // Calculer les statistiques pour cette station
            $stats = $this->getGlobalStats($stationBatteries);
            
            $stationStats[] = [
                'id' => $station->id,
                'name' => $station->nom_agence,
                'stats' => $stats['summary'],
                'levels' => $stats['levels']
            ];
        }
        
        return $stationStats;
    }

    /**
     * Récupère les données pour une station spécifique
     */
    private function getStationData($stationId, $batteryData)
    {
        // Filtrer les batteries pour la station demandée
        $stationBatteries = array_filter($batteryData, function($battery) use ($stationId) {
            return in_array($stationId, $battery['station_ids']);
        });
        
        // Calculer les statistiques pour la station
        return $this->getGlobalStats($stationBatteries);
    }
    
    /**
     * Génère les données de test pour le graphique d'évolution des swaps
     * Cette méthode est temporaire et peut être utilisée pour des tests
     */
    private function getStaticSwapData($timeFilter, $stationId = 'all')
    {
        $labels = match ($timeFilter) {
            'day' => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'],
            'week' => ['Semaine 1', 'Semaine 2', 'Semaine 3', 'Semaine 4'],
            'month' => ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            'year' => ['2020', '2021', '2022', '2023', '2024', '2025'],
            default => []
        };

        $datasets = [
            [
                'label' => 'Nombre de Swaps',
                'data' => array_fill(0, count($labels), rand(10, 100)),
                'backgroundColor' => 'rgba(220, 219, 50, 0.8)',
                'borderColor' => 'rgba(220, 219, 50, 1)',
                'borderWidth' => 1,
                'yAxisID' => 'y'
            ],
            [
                'label' => 'Montant (FCFA)',
                'data' => array_fill(0, count($labels), rand(5000, 50000)),
                'backgroundColor' => 'rgba(46, 204, 113, 0.6)',
                'borderColor' => 'rgba(46, 204, 113, 1)',
                'borderWidth' => 1,
                'yAxisID' => 'y1',
                'type' => 'line'
            ]
        ];

        if ($stationId === 'all') {
            $colors = ['#2ecc71', '#3498db', '#f39c12', '#e74c3c', '#9b59b6', '#1abc9c'];
            
            // Ajouter des données supplémentaires par station pour les tests
            /*
            foreach (Agence::all() as $index => $station) {
                $datasets[] = [
                    'label' => $station->nom_agence,
                    'data' => array_fill(0, count($labels), rand(5, 50)),
                    'backgroundColor' => $colors[$index % count($colors)],
                    'borderColor' => $colors[$index % count($colors)],
                ];
            }
            */
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
            'timeUnitLabel' => $this->getTimeUnitLabel($timeFilter),
        ];
    }

    /**
     * Renvoie le libellé de l'unité de temps en fonction du filtre choisi
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
}