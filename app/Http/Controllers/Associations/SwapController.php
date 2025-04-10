<?php

namespace App\Http\Controllers\Associations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Swap;
use App\Models\BatteryMotoUserAssociation;
use App\Models\BatteriesValide;
use App\Models\MotosValide;
use App\Models\BMSData;
use App\Models\AssociationUserMoto;
use App\Models\UsersAgence;
use App\Models\Agence;
use Carbon\Carbon;

class SwapController extends Controller
{
    /**
     * Affiche la page principale des swaps
     */
    public function index()
    {
        $pageTitle = 'Gestion des Swaps';
        
        // Récupérer les swaps avec toutes les relations
        $swaps = Swap::with([
            'batteryOut',
            'batteryIn',
            'swappeur',
            'swappeur.agence',
            'batteryMotoUserAssociation.association.validatedUser',
            'batteryMotoUserAssociation.association.motosValide'
        ])->get();
        
        // Récupérer toutes les agences et tous les swappeurs pour les filtres
        $agences = Agence::all();
        $swappeurs = UsersAgence::all();
        
        // Calculer les statistiques
        $totalSwaps = $swaps->count();
        $totalAmount = $swaps->sum('swap_price');
        
        // Préparer les données pour les graphiques
        $chartData = $this->prepareChartData('today');
        
        // Logs pour débogage
        Log::info('Nombre de swaps récupérés: ' . count($swaps));
        
        return view('association.swap', compact('swaps', 'pageTitle', 'agences', 'swappeurs', 'totalSwaps', 'totalAmount', 'chartData'));
    }
    
    /**
     * API pour filtrer les swaps
     */
    public function filterSwaps(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $agence = $request->input('agence', 'all');
            $swappeur = $request->input('swappeur', 'all');
            $time = $request->input('time', 'today');
            $customDate = $request->input('customDate', null);
            $startDate = $request->input('startDate', null);
            $endDate = $request->input('endDate', null);
            
            // Logs pour débogage
            Log::info('filterSwaps - Paramètres: ' . json_encode([
                'search' => $search,
                'agence' => $agence,
                'swappeur' => $swappeur,
                'time' => $time,
                'customDate' => $customDate,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]));
    
            // Déterminer la période
            if ($time === 'custom' && $customDate) {
                $filterStartDate = Carbon::parse($customDate)->startOfDay();
                $filterEndDate = Carbon::parse($customDate)->endOfDay();
                $timeLabel = 'le ' . Carbon::parse($customDate)->format('d/m/Y');
            } elseif ($time === 'daterange' && $startDate && $endDate) {
                $filterStartDate = Carbon::parse($startDate)->startOfDay();
                $filterEndDate = Carbon::parse($endDate)->endOfDay();
                $timeLabel = 'du ' . Carbon::parse($startDate)->format('d/m/Y') . ' au ' . Carbon::parse($endDate)->format('d/m/Y');
            } else {
                $filterStartDate = null;
                $filterEndDate = Carbon::now();
                $timeLabel = 'tout l\'historique';
    
                switch ($time) {
                    case 'today':
                        $filterStartDate = Carbon::today();
                        $timeLabel = 'aujourd\'hui';
                        break;
                    case 'week':
                        $filterStartDate = Carbon::now()->startOfWeek();
                        $timeLabel = 'cette semaine';
                        break;
                    case 'month':
                        $filterStartDate = Carbon::now()->startOfMonth();
                        $timeLabel = 'ce mois';
                        break;
                    case 'year':
                        $filterStartDate = Carbon::now()->startOfYear();
                        $timeLabel = 'cette année';
                        break;
                }
            }
    
            // Requête avec relations
            $query = Swap::with([
                'batteryOut',
                'batteryIn',
                'swappeur.agence',
                'batteryMotoUserAssociation.association.validatedUser',
                'batteryMotoUserAssociation.association.motosValide'
            ]);
    
            // Dates
            if ($filterStartDate) {
                $query->whereBetween('swap_date', [$filterStartDate, $filterEndDate]);
            }
    
            // Filtre agence
            if ($agence !== 'all') {
                $query->whereHas('swappeur.agence', function ($q) use ($agence) {
                    $q->where('id', $agence);
                });
            }
    
            // Filtre swappeur - Utiliser agent_user_id
            if ($swappeur !== 'all') {
                $query->where('agent_user_id', $swappeur);
            }
    
            $swaps = $query->get();
            
            Log::info('filterSwaps - Nombre de swaps trouvés: ' . count($swaps));
    
            // Filtrage texte
            if (!empty($search)) {
                $searchTerm = strtolower($search);
    
                $swaps = $swaps->filter(function ($swap) use ($searchTerm) {
                    $user = optional(optional($swap->batteryMotoUserAssociation)->association)->validatedUser;
                    $moto = optional(optional($swap->batteryMotoUserAssociation)->association)->motosValide;
                    $batteryOut = $swap->batteryOut;
                    $batteryIn = $swap->batteryIn;
                    $swappeur = $swap->swappeur;
                    $station = optional($swappeur)->agence;
    
                    return 
                        stripos($user->nom ?? '', $searchTerm) !== false ||
                        stripos($user->prenom ?? '', $searchTerm) !== false ||
                        stripos($user->user_unique_id ?? '', $searchTerm) !== false ||
                        stripos($moto->moto_unique_id ?? '', $searchTerm) !== false ||
                        stripos($moto->model ?? '', $searchTerm) !== false ||
                        stripos($batteryOut->mac_id ?? '', $searchTerm) !== false ||
                        stripos($batteryIn->mac_id ?? '', $searchTerm) !== false ||
                        stripos($station->nom_agence ?? '', $searchTerm) !== false ||
                        stripos($swappeur->nom ?? '', $searchTerm) !== false ||
                        stripos($swappeur->prenom ?? '', $searchTerm) !== false;
                });
            }
    
            // Structurer les swaps par jour
            $swapsByDay = [];
    
            foreach ($swaps as $swap) {
                $date = Carbon::parse($swap->swap_date)->format('Y-m-d');
    
                $user = optional(optional($swap->batteryMotoUserAssociation)->association)->validatedUser;
                $moto = optional(optional($swap->batteryMotoUserAssociation)->association)->motosValide;
                $batteryOut = $swap->batteryOut;
                $batteryIn = $swap->batteryIn;
                $swappeur = $swap->swappeur;
                $station = optional($swappeur)->agence;
    
                if (!isset($swapsByDay[$date])) {
                    $swapsByDay[$date] = [];
                }
    
                $swapsByDay[$date][] = [
                    'id' => $swap->id,
                    'user_id' => $user->user_unique_id ?? 'Non défini',
                    'user_name' => ($user->nom ?? 'Nom non défini') . ' ' . ($user->prenom ?? ''),
                    'moto_id' => $moto->moto_unique_id ?? 'Non défini',
                    'moto_model' => $moto->model ?? 'Modèle non défini',
                    'battery_out' => $batteryOut->mac_id ?? 'Non défini',
                    'battery_out_soc' => $swap->battery_out_soc . '%',
                    'battery_in' => $batteryIn->mac_id ?? 'Non défini',
                    'battery_in_soc' => $swap->battery_in_soc . '%',
                    'swap_price' => number_format($swap->swap_price, 2) . ' FCFA',
                    'station' => $station->nom_agence ?? 'Non défini',
                    'swappeur_name' => ($swappeur->nom ?? '') . ' ' . ($swappeur->prenom ?? ''),
                    'swap_date' => Carbon::parse($swap->swap_date)->format('d/m/Y H:i')
                ];
            }
    
            // Statistiques
            $totalSwaps = $swaps->count();
            $totalAmount = $swaps->sum('swap_price');
    
            // Données pour graphiques
            $chartData = $this->prepareChartData($time, $filterStartDate, $filterEndDate, $agence, $swappeur);
    
            // Log pour débogage
            Log::info('filterSwaps - swapsByDay: ' . json_encode(array_keys($swapsByDay)));
    
            return response()->json([
                'swapsByDay' => $swapsByDay,
                'timeLabel' => $timeLabel,
                'totalSwaps' => $totalSwaps,
                'totalAmount' => number_format($totalAmount, 2),
                'chartData' => $chartData
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans filterSwaps: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'error' => 'Une erreur est survenue lors du filtrage des swaps: ' . $e->getMessage(),
                'swapsByDay' => [],
                'timeLabel' => 'erreur',
                'totalSwaps' => 0,
                'totalAmount' => '0',
                'chartData' => [
                    'labels' => [],
                    'swapsCount' => [],
                    'swapsAmount' => []
                ]
            ], 500);
        }
    }
    
    /**
     * API pour récupérer les statistiques
     */
    public function getStats(Request $request)
    {
        try {
            $time = $request->input('timeFilter', 'today');
            $agence = $request->input('agence', 'all');
            $swappeur = $request->input('swappeur', 'all');
            $customDate = $request->input('customDate', null);
            $startDate = $request->input('startDate', null);
            $endDate = $request->input('endDate', null);
    
            // Déterminer la période
            if ($time === 'custom' && $customDate) {
                $filterStartDate = Carbon::parse($customDate)->startOfDay();
                $filterEndDate = Carbon::parse($customDate)->endOfDay();
                $timeLabel = 'le ' . Carbon::parse($customDate)->format('d/m/Y');
            } elseif ($time === 'daterange' && $startDate && $endDate) {
                $filterStartDate = Carbon::parse($startDate)->startOfDay();
                $filterEndDate = Carbon::parse($endDate)->endOfDay();
                $timeLabel = 'du ' . Carbon::parse($startDate)->format('d/m/Y') . ' au ' . Carbon::parse($endDate)->format('d/m/Y');
            } else {
                $filterStartDate = null;
                $filterEndDate = Carbon::now();
                $timeLabel = 'tout l\'historique';
    
                switch ($time) {
                    case 'today':
                        $filterStartDate = Carbon::today();
                        $timeLabel = 'aujourd\'hui';
                        break;
                    case 'week':
                        $filterStartDate = Carbon::now()->startOfWeek();
                        $timeLabel = 'cette semaine';
                        break;
                    case 'month':
                        $filterStartDate = Carbon::now()->startOfMonth();
                        $timeLabel = 'ce mois';
                        break;
                    case 'year':
                        $filterStartDate = Carbon::now()->startOfYear();
                        $timeLabel = 'cette année';
                        break;
                }
            }
    
            // Requête de base pour les swaps
            $query = Swap::query();
    
            // Filtrage par date
            if ($filterStartDate) {
                $query->whereBetween('swap_date', [$filterStartDate, $filterEndDate]);
            }
    
            // Filtrage par agence
            if ($agence !== 'all') {
                $query->whereHas('swappeur.agence', function ($q) use ($agence) {
                    $q->where('id', $agence);
                });
            }
    
            // Filtrage par swappeur - Utiliser agent_user_id
            if ($swappeur !== 'all') {
                $query->where('agent_user_id', $swappeur);
            }
    
            $swaps = $query->get();
    
            // Calcul des statistiques
            $totalSwaps = $swaps->count();
            $totalAmount = $swaps->sum('swap_price');
    
            // Données pour les graphiques
            $chartData = $this->prepareChartData($time, $filterStartDate, $filterEndDate, $agence, $swappeur);
    
            return response()->json([
                'totalSwaps' => $totalSwaps,
                'totalAmount' => number_format($totalAmount, 2),
                'timeLabel' => $timeLabel,
                'chartData' => $chartData
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans getStats: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'error' => 'Une erreur est survenue lors du calcul des statistiques: ' . $e->getMessage(),
                'totalSwaps' => 0,
                'totalAmount' => '0',
                'timeLabel' => 'erreur',
                'chartData' => [
                    'labels' => [],
                    'swapsCount' => [],
                    'swapsAmount' => []
                ]
            ], 500);
        }
    }
    
    /**
     * Traiter un swap
     */
    public function handleSwap(Request $request)
    {
        try {
            // Validation des données d'entrée
            $request->validate([
                'moto_unique_id' => 'required|exists:motos_valides,moto_unique_id',
                'battery_out' => 'required',
                'battery_in' => 'required',
                'swappeur_id' => 'required|exists:users_agences,id'
            ]);
            
            // Recherche de la moto via `moto_unique_id` dans la table `motos_valides`
            $moto = MotosValide::where('moto_unique_id', $request->moto_unique_id)->first();
            
            if (!$moto) {
                return redirect()->back()->with('error', 'Moto non trouvée.');
            }
            
            // Recherche de l'association utilisateur-moto
            $motoAssociation = AssociationUserMoto::where('moto_valide_id', $moto->id)->first();
            
            if (!$motoAssociation) {
                return redirect()->back()->with('error', 'Association utilisateur-moto non trouvée.');
            }
            
            // Recherche de la batterie sortante
            if (is_numeric($request->battery_out)) {
                $batteryOut = BatteriesValide::where('mac_id', $request->battery_out)->first();
            } else {
                $batteryOut = BatteriesValide::where('batterie_unique_id', $request->battery_out)->first();
            }
            
            if (!$batteryOut) {
                return redirect()->back()->with('error', 'Batterie sortante non trouvée.');
            }
            
            // Vérification si la batterie sortante est bien associée à cette moto
            $batteryAssociation = BatteryMotoUserAssociation::where('association_user_moto_id', $motoAssociation->id)
                                                           ->where('battery_id', $batteryOut->id)
                                                           ->first();
            
            if (!$batteryAssociation) {
                return redirect()->back()->with('error', 'La batterie sortante n\'est pas actuellement associée à cette moto.');
            }
            
            // Recherche de la batterie entrante
            if (is_numeric($request->battery_in)) {
                $batteryIn = BatteriesValide::where('mac_id', $request->battery_in)->first();
            } else {
                $batteryIn = BatteriesValide::where('batterie_unique_id', $request->battery_in)->first();
            }
            
            if (!$batteryIn) {
                return redirect()->back()->with('error', 'Batterie entrante non trouvée.');
            }
            
            // Vérification que les batteries sont différentes
            if ($batteryOut->id === $batteryIn->id) {
                return redirect()->back()->with('error', 'La batterie sortante et la batterie entrante sont identiques.');
            }
            
            // Récupérer le SOC des deux batteries
            $batteryOutSOC = $this->getBatterySOC($batteryOut);
            $batteryInSOC = $this->getBatterySOC($batteryIn);
            
            // Calcul du pourcentage consommé
            $socDifference = $this->calculateBatterySOCDifference($batteryOutSOC, $batteryInSOC);
            
            // Calcul du prix
            $swapPrice = $this->calculateSwapPrice($socDifference);
            
            // Récupérer le swappeur
            $swappeur = UsersAgence::findOrFail($request->swappeur_id);
            
            // Création de l'enregistrement du swap
            $swap = Swap::create([
                'battery_moto_user_association_id' => $batteryAssociation->id,
                'battery_in_id' => $batteryIn->id,
                'battery_out_id' => $batteryOut->id,
                'agent_user_id' => $swappeur->id,  // Utilisation du champ correct
                'swap_date' => now(),
                'battery_out_soc' => $batteryOutSOC ?? 0,
                'battery_in_soc' => $batteryInSOC ?? 0,
                'swap_price' => $swapPrice,
                // Ajout des champs nom, prenom, phone si nécessaire
                'nom' => $swappeur->nom ?? 'Non défini',
                'prenom' => $swappeur->prenom ?? 'Non défini',
                'phone' => $swappeur->phone ?? 'Non défini',
            ]);
            
            // Mise à jour de l'association batterie-moto-utilisateur
            $batteryAssociation->update([
                'battery_id' => $batteryIn->id
            ]);
            
            return redirect()->back()->with('success', 'Le swap a été effectué avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur dans handleSwap: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return redirect()->back()->with('error', 'Une erreur est survenue lors du traitement du swap: ' . $e->getMessage());
        }
    }
    
    /**
     * Méthode pour récupérer le SOC d'une batterie
     */
    private function getBatterySOC($battery)
    {
        // Récupérer les données BMS associées à la batterie en fonction du mac_id
        $bmsData = BMSData::where('mac_id', $battery->mac_id)
                          ->orderBy('timestamp', 'desc') // Trier par la colonne 'timestamp'
                          ->first(); // Récupérer la dernière entrée
    
        // Retourner le SOC, ou 0 si non disponible
        return $bmsData ? json_decode($bmsData->state, true)['SOC'] ?? 0 : 0;
    }
    
    /**
     * Calculer la différence de pourcentage entre la batterie sortante et la batterie entrante
     */
    private function calculateBatterySOCDifference($batteryOutSOC, $batteryInSOC)
    {
        // S'assurer que les deux SOC sont des nombres
        $batteryOutSOC = floatval($batteryOutSOC);
        $batteryInSOC = floatval($batteryInSOC);
    
        // Calculer la différence de pourcentage et s'assurer que le résultat est positif
        $difference = abs($batteryOutSOC - $batteryInSOC);
    
        return $difference;
    }
    
    /**
     * Calculer le prix d'un swap en fonction de la différence de SOC
     */
    private function calculateSwapPrice($socDifference)
    {
        // Vérifier que la différence est un nombre et qu'elle est positive
        $socDifference = max(0, floatval($socDifference));
    
        // Calculer le prix en fonction du SOC
        $pricePerPercent = 2000/93;  // 1% = 21.5 FCFA environ
        $swapPrice = $socDifference * $pricePerPercent;
    
        return $swapPrice;
    }
    
    /**
     * Prépare les données pour les graphiques
     */
    private function prepareChartData($time, $startDate = null, $endDate = null, $agence = 'all', $swappeur = 'all')
    {
        if (!$startDate || !$endDate) {
            switch ($time) {
                case 'today':
                    $startDate = Carbon::today();
                    $endDate = Carbon::now();
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now();
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now();
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now();
                    break;
                default:
                $startDate = Carbon::now()->subDays(30); // Par défaut, les 30 derniers jours
                $endDate = Carbon::now();
        }
    }

    // Déterminer l'intervalle approprié
    $interval = $this->determineChartInterval($time, $startDate, $endDate);

    // Construire la requête
    $query = Swap::query();

    // Filtrer par période
    $query->whereBetween('swap_date', [$startDate, $endDate]);

    // Filtrer par agence si spécifié
    if ($agence !== 'all') {
        $query->whereHas('swappeur.agence', function ($q) use ($agence) {
            $q->where('id', $agence);
        });
    }

    // Filtrer par swappeur si spécifié
    if ($swappeur !== 'all') {
        $query->where('agent_user_id', $swappeur);
    }

    // Récupérer les swaps
    $swaps = $query->get();

    // Grouper les données selon l'intervalle
    $grouped = [];
    foreach ($swaps as $swap) {
        $date = Carbon::parse($swap->swap_date);
        
        // Définir la clé de regroupement selon l'intervalle
        switch ($interval) {
            case 'day':
                $key = $date->format('Y-m-d');
                break;
            case 'week':
                $key = $date->format('o-\WW'); // Année-Semaine
                break;
            case 'month':
                $key = $date->format('Y-m'); // Année-Mois
                break;
        }

        // Initialiser le groupe si nécessaire
        if (!isset($grouped[$key])) {
            $grouped[$key] = ['count' => 0, 'amount' => 0];
        }

        // Incrémenter les compteurs
        $grouped[$key]['count']++;
        $grouped[$key]['amount'] += $swap->swap_price;
    }

    // Trier les clés
    ksort($grouped);

    // S'assurer qu'il y a au moins une entrée pour éviter les erreurs
    if (empty($grouped)) {
        return [
            'labels' => [],
            'swapsCount' => [],
            'swapsAmount' => []
        ];
    }

    // Préparer les données pour les graphiques
    $labels = array_keys($grouped);
    $swapsCount = array_map(function($g) { return $g['count']; }, $grouped);
    $swapsAmount = array_map(function($g) { return round($g['amount'], 2); }, $grouped);

    return [
        'labels' => $labels,
        'swapsCount' => $swapsCount,
        'swapsAmount' => $swapsAmount
    ];
}

/**
 * Déterminer l'intervalle approprié pour le graphique en fonction de la période sélectionnée
 */
private function determineChartInterval($timeFilter, $startDate, $endDate)
{
    $daysDifference = $startDate->diffInDays($endDate);
    
    if ($daysDifference <= 31) {
        return 'day'; // Afficher par jour si la période est <= 31 jours
    } elseif ($daysDifference <= 90) {
        return 'week'; // Afficher par semaine si la période est <= 90 jours
    } else {
        return 'month'; // Afficher par mois pour les périodes plus longues
    }
}
}