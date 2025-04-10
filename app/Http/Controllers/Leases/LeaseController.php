<?php

namespace App\Http\Controllers\Leases;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaiementLease;
use App\Models\UsersAgence;
use App\Models\AssociationUserMoto;
use App\Models\MotosValide;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaseController extends Controller
{
    public function index()
    {
        $pageTitle = 'Gestion des Leases';
        
        // Récupérer tous les chauffeurs associés
        $associations = AssociationUserMoto::with(['validatedUser', 'motosValide'])
            ->whereNull('deleted_at')
            ->get();
            
        // Récupérer les leases payés aujourd'hui par défaut
        $paidLeases = PaiementLease::with([
            'moto.users', // Charger la relation entre moto et utilisateur
            'userAgence.agence'
        ])
        ->whereDate('created_at', Carbon::today())
        ->get();
        
        // Préparer les données pour la vue
        $leases = $this->prepareLeaseData($associations, $paidLeases, 'today');
        
        return view('lease.index', compact('leases', 'pageTitle'));
    }
    
    /**
     * Prépare les données de lease pour l'affichage
     */
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

                // Date forcée si fournie
                if ($forcedDate) {
                    $lease->created_at = $forcedDate;
                } else {
                    switch ($timeFilter) {
                        case 'today':
                            $lease->created_at = Carbon::today();
                            break;
                        case 'week':
                            $lease->created_at = Carbon::now()->startOfWeek();
                            break;
                        case 'month':
                            $lease->created_at = Carbon::now()->startOfMonth();
                            break;
                        case 'year':
                            $lease->created_at = Carbon::now()->startOfYear();
                            break;
                        case 'custom':
                            $lease->created_at = Carbon::parse($forcedDate);
                            break;
                        default:
                            $lease->created_at = Carbon::today();
                    }
                }

                $lease->moto = $moto;
                $lease->moto->users = collect([$user]);
                $lease->userAgence = null;
            }

            $leases[] = $lease;
        }

        return collect($leases);
    }

    
    /**
     * API pour filtrer les leases
     */
    public function filterLeases(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', 'all');
        $time = $request->input('time', 'today');
        $customDate = $request->input('customDate', null);
    
        // Déterminer les dates de début et de fin pour le filtre
        if ($time === 'custom' && $customDate) {
            $startDate = Carbon::parse($customDate)->startOfDay();
            $endDate = Carbon::parse($customDate)->endOfDay();
            $timeLabel = 'le ' . Carbon::parse($customDate)->format('d/m/Y');
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
                $paidLeases = PaiementLease::with(['moto.users', 'userAgence.agence'])
                    ->whereDate('created_at', $date->format('Y-m-d'))
                    ->get();
    
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
    
        // Initialiser les variables
        $startDate = null;
        $endDate = Carbon::now();
        $timeLabel = 'historique complet';
    
        // Récupérer tous les chauffeurs associés
        $associations = AssociationUserMoto::whereNull('deleted_at')->get();
        $totalDrivers = $associations->count();
    
        $paidCount = 0;
        $unpaidCount = 0;
    
        // Si custom date, gérer comme un jour unique sans boucle
        if ($timeFilter === 'custom' && $customDate) {
            $date = Carbon::parse($customDate)->format('Y-m-d');
            $timeLabel = 'le ' . Carbon::parse($customDate)->format('d/m/Y');
    
            // Paiements effectués ce jour-là
            $paidMotoIds = PaiementLease::whereDate('created_at', $date)
                ->pluck('id_moto')
                ->toArray();
    
            // Comparer pour chaque association
            foreach ($associations as $association) {
                $motoId = $association->moto_valide_id;
                if (in_array($motoId, $paidMotoIds)) {
                    $paidCount++;
                } else {
                    $unpaidCount++;
                }
            }
        } else {
            // Autres périodes : semaine, mois, année
            switch ($timeFilter) {
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
    
            $endDate = Carbon::now();
            
            if ($startDate) {
                for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                    $paidLeases = PaiementLease::whereDate('created_at', $date->format('Y-m-d'))
                        ->pluck('id_moto')
                        ->toArray();
    
                    foreach ($associations as $association) {
                        $motoId = $association->moto_valide_id;
                        if (in_array($motoId, $paidLeases)) {
                            $paidCount++;
                        } else {
                            $unpaidCount++;
                        }
                    }
                }
            } else {
                // Historique complet (aucune période filtrée)
                $paidMotoIds = PaiementLease::pluck('id_moto')->unique()->toArray();
                $paidCount = count($paidMotoIds);
                $unpaidCount = $totalDrivers - $paidCount;
            }
        }
    
        return response()->json([
            'totalDrivers' => $totalDrivers,
            'paidLeases' => $paidCount,
            'unpaidLeases' => $unpaidCount,
            'timeLabel' => $timeLabel
        ]);
    }
    
}