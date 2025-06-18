<?php

namespace App\Http\Controllers\Associations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Swap;
use App\Models\Agence;
 use App\Models\AssociationUserMoto;
use App\Models\ValidatedUser;
use Carbon\Carbon;

class SwapChauffeurController extends Controller
{
    public function index()
    {
        $pageTitle = 'Swaps par Chauffeur';
        $agences = Agence::all();

        $today = Carbon::today();

        $swaps = Swap::with(['batteryMotoUserAssociation.association.validatedUser'])
            ->whereDate('swap_date', $today)
            ->get();

        $totalSwaps = $swaps->count();
        $totalChauffeurs = $swaps->pluck('batteryMotoUserAssociation.association.validatedUser.id')->unique()->count();

        return view('association.swap-chauffeur', compact(
            'pageTitle', 
            'agences', 
            'totalSwaps', 
            'totalChauffeurs'
        ));
    }

   


public function filterSwaps(Request $request)
{
    try {
        $search = strtolower($request->input('search', ''));
        $time = $request->input('time', 'today');
        $customDate = $request->input('customDate');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        // Déterminer période
        $filterStartDate = null;
        $filterEndDate = Carbon::now();
        $timeLabel = 'tout l\'historique';

        if ($time === 'custom' && $customDate) {
            $filterStartDate = Carbon::parse($customDate)->startOfDay();
            $filterEndDate = Carbon::parse($customDate)->endOfDay();
            $timeLabel = 'le ' . $filterStartDate->format('d/m/Y');
        } elseif ($time === 'daterange' && $startDate && $endDate) {
            $filterStartDate = Carbon::parse($startDate)->startOfDay();
            $filterEndDate = Carbon::parse($endDate)->endOfDay();
            $timeLabel = 'du ' . Carbon::parse($startDate)->format('d/m/Y') . ' au ' . Carbon::parse($endDate)->format('d/m/Y');
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

        // Récupérer tous les chauffeurs (ayant une moto)
        $associations = AssociationUserMoto::with('validatedUser')->get();
        $chauffeurs = $associations->pluck('validatedUser')->filter();

        // Générer tous les jours dans l’intervalle
        $days = collect();
        for ($date = $filterStartDate->copy(); $date->lte($filterEndDate); $date->addDay()) {
            $days->push($date->copy());
        }

        // Récupérer tous les swaps dans la période
        $swaps = Swap::with(['batteryMotoUserAssociation.association.validatedUser'])
            ->whereBetween('swap_date', [$filterStartDate, $filterEndDate])
            ->get();

        // Regrouper swaps par date et chauffeur
        $groupedSwaps = [];
        foreach ($swaps as $swap) {
            $user = optional(optional($swap->batteryMotoUserAssociation)->association)->validatedUser;
            if (!$user) continue;

            $day = Carbon::parse($swap->swap_date)->format('Y-m-d');
            $chauffeurId = $user->id;

            if (!isset($groupedSwaps[$day][$chauffeurId])) {
                $groupedSwaps[$day][$chauffeurId] = [
                    'chauffeur_id' => $chauffeurId,
                    'chauffeur_name' => trim(($user->nom ?? '') . ' ' . ($user->prenom ?? '')),
                    'station' => '-',
                    'swap_count' => 0,
                    'total_amount' => 0
                ];
            }

            $groupedSwaps[$day][$chauffeurId]['swap_count']++;
            $groupedSwaps[$day][$chauffeurId]['total_amount'] += $swap->swap_price;
        }

        // Construire la structure finale avec tous les chauffeurs pour chaque jour
        $swapsByDay = [];

        foreach ($days as $day) {
            $dateKey = $day->format('Y-m-d');
            $swapsByDay[$dateKey] = [];

            foreach ($chauffeurs as $user) {
                if (!$user) continue;

                $chauffeurId = $user->id;
                $data = $groupedSwaps[$dateKey][$chauffeurId] ?? [
                    'chauffeur_id' => $chauffeurId,
                    'chauffeur_name' => trim(($user->nom ?? '') . ' ' . ($user->prenom ?? '')),
                    'station' => '-',
                    'swap_count' => 0,
                    'total_amount' => 0
                ];

                // Recherche (sur nom ou id)
                if ($search && !str_contains(strtolower($data['chauffeur_name']), $search) && !str_contains((string)$data['chauffeur_id'], $search)) {
                    continue;
                }

                $swapsByDay[$dateKey][] = $data;
            }

            // Trier les chauffeurs par nombre de swaps décroissant
            usort($swapsByDay[$dateKey], fn($a, $b) => $b['swap_count'] <=> $a['swap_count']);
        }

        $totalSwaps = $swaps->count();
        $totalChauffeurs = $chauffeurs->count();
        $totalAmount = $swaps->sum('swap_price');

        return response()->json([
            'swapsByDay' => $swapsByDay,
            'timeLabel' => $timeLabel,
            'totalSwaps' => $totalSwaps,
            'totalChauffeurs' => $totalChauffeurs,
            'totalAmount' => $totalAmount
        ]);
    } catch (\Exception $e) {
        Log::error('Erreur filterSwapsChauffeur : ' . $e->getMessage());
        return response()->json([
            'error' => 'Erreur : ' . $e->getMessage(),
            'swapsByDay' => [],
            'timeLabel' => 'erreur',
            'totalSwaps' => 0,
            'totalChauffeurs' => 0,
            'totalAmount' => 0
        ], 500);
    }
}


    public function getStats(Request $request)
    {
        try {
            $time = $request->input('timeFilter', 'today');
            $agence = $request->input('agence', 'all');
            $customDate = $request->input('customDate');
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');

            // Déterminer la période (même logique que filterSwaps)
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

            $query = Swap::with(['batteryMotoUserAssociation.association.validatedUser']);

            if ($filterStartDate) {
                $query->whereBetween('swap_date', [$filterStartDate, $filterEndDate]);
            }

            if ($agence !== 'all') {
                $query->whereHas('batteryMotoUserAssociation.association.validatedUser', function ($q) use ($agence) {
                    $q->whereHas('agence', function ($subQ) use ($agence) {
                        $subQ->where('id', $agence);
                    });
                });
            }

            $swaps = $query->get();

            $totalSwaps = $swaps->count();
            $totalChauffeurs = $swaps->pluck('batteryMotoUserAssociation.association.validatedUser.id')->unique()->count();
            $totalAmount = $swaps->sum('swap_price');

            return response()->json([
                'totalSwaps' => $totalSwaps,
                'totalChauffeurs' => $totalChauffeurs,
                'totalAmount' => $totalAmount,
                'timeLabel' => $timeLabel
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur dans getStatsChauffeur: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Une erreur est survenue: ' . $e->getMessage(),
                'totalSwaps' => 0,
                'totalChauffeurs' => 0,
                'totalAmount' => 0,
                'timeLabel' => 'erreur'
            ], 500);
        }
    }
}