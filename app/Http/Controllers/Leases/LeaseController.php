<?php

namespace App\Http\Controllers\Leases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaiementLease;
use App\Models\UsersAgence;
use App\Models\AssociationUserMoto;
use App\Models\MotosValide;
use App\Models\Agence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LeaseController extends Controller
{
    public function index()
    {
        $pageTitle = 'Gestion des Leases';

        $associations = AssociationUserMoto::with(['validatedUser', 'motosValide'])
            ->whereNull('deleted_at')
            ->get();
<<<<<<< HEAD
            
        // Récupérer toutes les agences et tous les swappeurs pour les filtres
        $agences = Agence::all();
        $swappeurs = UsersAgence::all();
        
        // Récupérer les leases payés aujourd'hui par défaut
=======

        $agences = Agence::all();
        $swappeurs = UsersAgence::all();

>>>>>>> bdcca81 (version stable avec authentifiacation et ravitaillement et gestion efficasse des stations)
        $paidLeases = PaiementLease::with([
            'moto.users',
            'userAgence.agence'
        ])
        ->whereDate('created_at', Carbon::today())
        ->get();

        $leases = $this->prepareLeaseData($associations, $paidLeases, 'today');
<<<<<<< HEAD
        
        // Calculer les statistiques initiales
        $totalDrivers = $associations->count();
        
        // Compter les leases payés pour aujourd'hui
        $paidMotoIds = $paidLeases->pluck('id_moto')->unique()->toArray();
        $paidLeasesCount = count($paidMotoIds);
        $unpaidLeasesCount = $totalDrivers - $paidLeasesCount;
        
        // Calculer le montant total des leases
        $totalAmount = $paidLeases->sum('total_lease');
        
=======

        $totalDrivers = $associations->count();
        $uniquePaidLeases = $paidLeases->unique('id_moto');
        $paidLeasesCount = $uniquePaidLeases->count();
        $unpaidLeasesCount = $totalDrivers - $paidLeasesCount;

        $totalAmount = $uniquePaidLeases->sum('total_lease');

>>>>>>> bdcca81 (version stable avec authentifiacation et ravitaillement et gestion efficasse des stations)
        return view('lease.index', compact(
            'leases', 
            'pageTitle', 
            'agences', 
            'swappeurs', 
            'totalDrivers', 
            'paidLeasesCount', 
            'unpaidLeasesCount',
            'totalAmount'
        ));
    }

    public function filterLeases(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $status = $request->input('status', 'all');
            $time = $request->input('time', 'today');
            $customDate = $request->input('customDate');
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');
            $agence = $request->input('agence', 'all');
            $swappeur = $request->input('swappeur', 'all');

            $filterStartDate = null;
            $filterEndDate = Carbon::now();
            $timeLabel = 'historique complet';

            if ($time === 'custom' && $customDate) {
                $filterStartDate = Carbon::parse($customDate)->startOfDay();
                $filterEndDate = Carbon::parse($customDate)->endOfDay();
                $timeLabel = 'le ' . $filterStartDate->format('d/m/Y');
            } elseif ($time === 'daterange' && $startDate && $endDate) {
                $filterStartDate = Carbon::parse($startDate)->startOfDay();
                $filterEndDate = Carbon::parse($endDate)->endOfDay();
                $timeLabel = 'du ' . $filterStartDate->format('d/m/Y') . ' au ' . $filterEndDate->format('d/m/Y');
            } else {
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

            $query = PaiementLease::with(['moto.users', 'userAgence.agence']);

            if ($filterStartDate && $filterEndDate) {
                $query->whereBetween('created_at', [$filterStartDate, $filterEndDate]);
            }

            if ($agence !== 'all') {
                $query->whereHas('userAgence.agence', function ($q) use ($agence) {
                    $q->where('id', $agence);
                });
            }

            if ($swappeur !== 'all') {
                $query->where('id_user_agence', $swappeur);
            }

            $paidLeases = $query->get();

            $associations = AssociationUserMoto::with(['validatedUser', 'motosValide'])
                ->whereNull('deleted_at')
                ->get();

            $leases = $this->prepareLeaseData($associations, $paidLeases, $time);

            $leasesByDay = [];
            foreach ($leases as $lease) {
                $dateKey = Carbon::parse($lease->created_at)->format('Y-m-d');
                $leasesByDay[$dateKey][] = [
                    'id' => $lease->id,
                    'driver_id' => $lease->moto->users->first()->user_unique_id ?? 'Non défini',
                    'driver_name' => ($lease->moto->users->first()->nom ?? '') . ' ' . ($lease->moto->users->first()->prenom ?? ''),
                    'moto_id' => $lease->moto->moto_unique_id ?? 'Non défini',
                    'moto_vin' => $lease->moto->vin ?? 'VIN non défini',
                    'montant_lease' => $lease->is_paid ? number_format($lease->montant_lease, 2) : '0.00',
                    'montant_battery' => $lease->is_paid ? number_format($lease->montant_battery, 2) : '0.00',
                    'total_lease' => $lease->is_paid ? number_format($lease->total_lease, 2) : '0.00',
                    'station' => $lease->is_paid ? ($lease->userAgence->agence->nom_agence ?? 'Non défini') : 'Non applicable',
                    'status' => $lease->is_paid ? 'paid' : 'unpaid',
                    'swappeur' => $lease->is_paid ? ($lease->userAgence->nom ?? 'Non défini') : 'Non applicable',
                    'date' => Carbon::parse($lease->created_at)->format('d/m/Y'),
                ];
            }

            return response()->json([
                'leasesByDay' => $leasesByDay,
                'timeLabel' => $timeLabel
            ]);

        } catch (\Throwable $e) {
            Log::error('Erreur filtre leases : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur lors du filtrage des données'], 500);
        }
    }

    private function prepareLeaseData($associations, $paidLeases, $timeFilter, $forcedDate = null)
    {
        $leases = [];

        foreach ($associations as $association) {
            $moto = $association->motosValide;
            $user = $association->validatedUser;

            if (!$moto || !$user) continue;

            $payment = $paidLeases->where('id_moto', $moto->id)->first();

            if ($payment) {
                $lease = $payment;
                $lease->is_paid = true;
            } else {
                $lease = new \stdClass();
                $lease->id = 'unpaid-' . $moto->id;
                $lease->id_moto = $moto->id;
                $lease->montant_lease = 0;
                $lease->montant_battery = 0;
                $lease->total_lease = 0;
                $lease->is_paid = false;

                $lease->created_at = match ($timeFilter) {
                    'week' => Carbon::now()->startOfWeek(),
                    'month' => Carbon::now()->startOfMonth(),
                    'year' => Carbon::now()->startOfYear(),
                    'custom' => $forcedDate ? Carbon::parse($forcedDate) : Carbon::today(),
                    default => Carbon::today()
                };

                $lease->moto = $moto;
                $lease->moto->users = collect([$user]);
                $lease->userAgence = null;
            }

            $leases[] = $lease;
        }

        return collect($leases);
    }

<<<<<<< HEAD
    
    /**
     * API pour filtrer les leases
     */
    public function filterLeases(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', 'all');
        $time = $request->input('time', 'today');
        $customDate = $request->input('customDate', null);
        $startDate = $request->input('startDate', null);
        $endDate = $request->input('endDate', null);
        $agence = $request->input('agence', 'all');
        $swappeur = $request->input('swappeur', 'all');
    
        // Déterminer les dates de début et de fin pour le filtre
        if ($time === 'custom' && $customDate) {
            $startDate = Carbon::parse($customDate)->startOfDay();
            $endDate = Carbon::parse($customDate)->endOfDay();
            $timeLabel = 'le ' . Carbon::parse($customDate)->format('d/m/Y');
        } elseif ($time === 'daterange' && $startDate && $endDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
            $timeLabel = 'du ' . Carbon::parse($startDate)->format('d/m/Y') . ' au ' . Carbon::parse($endDate)->format('d/m/Y');
        } else {
            $startDate = null;
            $endDate = Carbon::now();
            $timeLabel = 'tout l\'historique';
            
            switch ($time) {
                case 'today':
                    $startDate = Carbon::today();
                    $timeLabel = 'aujourd\'hui';
                    break;
                case 'week':
                    $startDate = Carbon::now()->startOfWeek();
                    $timeLabel = 'cette semaine';
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth();
                    $timeLabel = 'ce mois';
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear();
                    $timeLabel = 'cette année';
                    break;
            }
        }
    
        $associations = AssociationUserMoto::with(['validatedUser', 'motosValide'])
            ->whereNull('deleted_at')
            ->get();
    
        // Liste finale des leases groupés par jour
        $leasesByDay = [];
    
        if ($startDate) {
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // Construire la requête pour les paiements
                $query = PaiementLease::with(['moto.users', 'userAgence.agence'])
                    ->whereDate('created_at', $date->format('Y-m-d'));
                
                // Filtrer par agence
                if ($agence !== 'all') {
                    $query->whereHas('userAgence.agence', function ($q) use ($agence) {
                        $q->where('id', $agence);
                    });
                }
                
                // Filtrer par swappeur
                if ($swappeur !== 'all') {
                    $query->where('id_user_agence', $swappeur);
                }
                
                $paidLeases = $query->get();
    
                $leases = $this->prepareLeaseData($associations, $paidLeases, 'custom', $date);
    
                // Filtrer selon le statut
                switch ($status) {
                    case 'paid':
                        $leases = $leases->where('is_paid', true);
                        break;
                    case 'unpaid':
                        $leases = $leases->where('is_paid', false);
                        break;
                    default:
                        break;
                }
    
                // Filtrer par mot-clé
                if (!empty($search)) {
                    $leases = $leases->filter(function($lease) use ($search) {
                        $searchTerm = strtolower($search);
                        $motoMatches = isset($lease->moto) && (
                            stripos($lease->moto->moto_unique_id ?? '', $searchTerm) !== false ||
                            stripos($lease->moto->vin ?? '', $searchTerm) !== false
                        );
    
                        $userMatches = isset($lease->moto->users) && $lease->moto->users->isNotEmpty() &&
                            (
                                stripos($lease->moto->users->first()->nom ?? '', $searchTerm) !== false ||
                                stripos($lease->moto->users->first()->prenom ?? '', $searchTerm) !== false ||
                                stripos($lease->moto->users->first()->user_unique_id ?? '', $searchTerm) !== false
                            );
    
                        $agencyMatches = isset($lease->userAgence->agence) &&
                            stripos($lease->userAgence->agence->nom_agence ?? '', $searchTerm) !== false;
    
                        $swapperMatches = isset($lease->userAgence) &&
                            stripos($lease->userAgence->nom ?? '', $searchTerm) !== false;
    
                        return $motoMatches || $userMatches || $agencyMatches || $swapperMatches;
                    });
                }
    
                // Formatter pour la réponse
                $formatted = $leases->map(function ($lease) use ($date) {
                    return [
                        'id' => $lease->id,
                        'driver_id' => $lease->moto->users->first()->user_unique_id ?? 'Non défini',
                        'driver_name' => ($lease->moto->users->first()->nom ?? '') . ' ' .
                                         ($lease->moto->users->first()->prenom ?? ''),
                        'moto_id' => $lease->moto->moto_unique_id ?? 'Non défini',
                        'moto_vin' => $lease->moto->vin ?? 'VIN non défini',
                        'montant_lease' => $lease->is_paid ? number_format($lease->montant_lease, 2) : '0.00',
                        'montant_battery' => $lease->is_paid ? number_format($lease->montant_battery, 2) : '0.00',
                        'total_lease' => $lease->is_paid ? number_format($lease->total_lease, 2) : '0.00',
                        'station' => $lease->is_paid ? ($lease->userAgence->agence->nom_agence ?? 'Non défini') : 'Non applicable',
                        'status' => $lease->is_paid ? 'paid' : 'unpaid',
                        'swappeur' => $lease->is_paid ? ($lease->userAgence->nom  ?? 'Non défini')  : 'Non applicable',
                        'date' => $date->format('d/m/Y')
                    ];
                });
    
                $leasesByDay[$date->format('Y-m-d')] = $formatted->values();
            }
        }
    
        return response()->json([
            'leasesByDay' => $leasesByDay,
            'timeLabel' => $timeLabel
        ]);
    }
    
    
    /**
     * API pour récupérer les statistiques
     */
    public function getStats(Request $request)
    {
        $timeFilter = $request->input('timeFilter', 'today');
        $customDate = $request->input('customDate', null);
        $startDate = $request->input('startDate', null);
        $endDate = $request->input('endDate', null);
        $agence = $request->input('agence', 'all');
        $swappeur = $request->input('swappeur', 'all');
    
        // Initialiser les variables
        $filterStartDate = null;
        $filterEndDate = Carbon::now();
        $timeLabel = 'historique complet';
    
        // Récupérer tous les chauffeurs associés
        $associations = AssociationUserMoto::whereNull('deleted_at')->get();
        $totalDrivers = $associations->count();
    
        // Déterminer la période
        if ($timeFilter === 'custom' && $customDate) {
            $filterStartDate = Carbon::parse($customDate)->startOfDay();
            $filterEndDate = Carbon::parse($customDate)->endOfDay();
            $timeLabel = 'le ' . Carbon::parse($customDate)->format('d/m/Y');
        } elseif ($timeFilter === 'daterange' && $startDate && $endDate) {
            $filterStartDate = Carbon::parse($startDate)->startOfDay();
            $filterEndDate = Carbon::parse($endDate)->endOfDay();
            $timeLabel = 'du ' . Carbon::parse($startDate)->format('d/m/Y') . ' au ' . Carbon::parse($endDate)->format('d/m/Y');
=======
    public function getStats(Request $request)
    {
        $timeFilter = $request->input('timeFilter', 'today');
        $customDate = $request->input('customDate');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $agence = $request->input('agence', 'all');
        $swappeur = $request->input('swappeur', 'all');
        $typeLease = $request->input('typeLease', 'all');
        $chauffeurId = $request->input('chauffeur');

        $filterStartDate = null;
        $filterEndDate = Carbon::now();
        $timeLabel = 'historique complet';

        if ($timeFilter === 'custom' && $customDate) {
            $filterStartDate = Carbon::parse($customDate)->startOfDay();
            $filterEndDate = Carbon::parse($customDate)->endOfDay();
            $timeLabel = 'le ' . $filterStartDate->format('d/m/Y');
        } elseif ($timeFilter === 'daterange' && $startDate && $endDate) {
            $filterStartDate = Carbon::parse($startDate)->startOfDay();
            $filterEndDate = Carbon::parse($endDate)->endOfDay();
            $timeLabel = 'du ' . $filterStartDate->format('d/m/Y') . ' au ' . $filterEndDate->format('d/m/Y');
>>>>>>> bdcca81 (version stable avec authentifiacation et ravitaillement et gestion efficasse des stations)
        } else {
            switch ($timeFilter) {
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
<<<<<<< HEAD
    
        // Requête de base pour les leases
        $query = PaiementLease::query();
    
        // Filtrer par période
        if ($filterStartDate && $filterEndDate) {
            $query->whereBetween('created_at', [$filterStartDate, $filterEndDate]);
        }
    
        // Filtrer par agence si spécifié
=======

        $associations = AssociationUserMoto::whereNull('deleted_at')->get();
        $totalDrivers = $associations->count();

        $query = PaiementLease::with(['moto.users', 'userAgence.agence']);

        if ($filterStartDate && $filterEndDate) {
            $query->whereBetween('created_at', [$filterStartDate, $filterEndDate]);
        }

>>>>>>> bdcca81 (version stable avec authentifiacation et ravitaillement et gestion efficasse des stations)
        if ($agence !== 'all') {
            $query->whereHas('userAgence.agence', function ($q) use ($agence) {
                $q->where('id', $agence);
            });
        }
<<<<<<< HEAD
    
        // Filtrer par swappeur si spécifié
        if ($swappeur !== 'all') {
            $query->where('id_user_agence', $swappeur);
        }
    
        // Récupérer les leases payés pendant la période
        $paidLeases = $query->get();
        
        // Calculer les statistiques
        $paidMotoIds = $paidLeases->pluck('id_moto')->unique();
        $paidCount = $paidMotoIds->count();
        $unpaidCount = $totalDrivers - $paidCount;
        
        // Calculer le montant total en additionnant les montants réels
        $totalAmount = $paidLeases->sum('total_lease');
    
=======

        if ($swappeur !== 'all') {
            $query->where('id_user_agence', $swappeur);
        }

        if ($typeLease !== 'all') {
            $query->where('type_lease', $typeLease);
        }

        if ($chauffeurId) {
            $query->whereHas('moto.users', function ($q) use ($chauffeurId) {
                $q->where('user_unique_id', $chauffeurId);
            });
        }

        $paidLeases = $query->get();

        $uniquePaidLeases = $paidLeases->unique('id_moto');
        $paidCount = $uniquePaidLeases->count();
        $unpaidCount = $totalDrivers - $paidCount;
        $totalAmount = $uniquePaidLeases->sum('total_lease');

>>>>>>> bdcca81 (version stable avec authentifiacation et ravitaillement et gestion efficasse des stations)
        return response()->json([
            'totalDrivers' => $totalDrivers,
            'paidLeases' => $paidCount,
            'unpaidLeases' => $unpaidCount,
            'totalAmount' => number_format($totalAmount, 2),
            'timeLabel' => $timeLabel
        ]);
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> bdcca81 (version stable avec authentifiacation et ravitaillement et gestion efficasse des stations)
