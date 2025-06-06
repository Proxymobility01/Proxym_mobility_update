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

        $agences = Agence::all();
        $swappeurs = UsersAgence::all();

        $paidLeases = PaiementLease::with([
            'moto.users',
            'userAgence.agence'
        ])
        ->whereDate('created_at', Carbon::today())
        ->get();

        $leases = $this->prepareLeaseData($associations, $paidLeases, 'today');

        $totalDrivers = $associations->count();
        $uniquePaidLeases = $paidLeases->unique('id_moto');
        $paidLeasesCount = $uniquePaidLeases->count();
        $unpaidLeasesCount = $totalDrivers - $paidLeasesCount;

        $totalAmount = $uniquePaidLeases->sum('total_lease');

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

        $associations = AssociationUserMoto::whereNull('deleted_at')->get();
        $totalDrivers = $associations->count();

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

        return response()->json([
            'totalDrivers' => $totalDrivers,
            'paidLeases' => $paidCount,
            'unpaidLeases' => $unpaidCount,
            'totalAmount' => number_format($totalAmount, 2),
            'timeLabel' => $timeLabel
        ]);
    }
}
