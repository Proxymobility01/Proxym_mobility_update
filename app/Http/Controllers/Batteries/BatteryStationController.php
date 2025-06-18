<?php

namespace App\Http\Controllers\Batteries;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BatteryAgence;
use App\Models\BatteriesValide;
use App\Models\Agence;
use App\Models\BMSData;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class BatteryStationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $agenceId = $request->query('agence_id');
        $enAgence = $request->query('en_agence'); // "1" ou "0"
        $filtre = $request->query('filtre'); // filtre par statut (charge, décharge faible...)

        $query = BatteriesValide::with(['agences.agence']);

        // Filtrage par localisation (en agence ou hors agence)
        if ($enAgence === "1") {
            $query->whereHas('agences');
        } elseif ($enAgence === "0") {
            $query->whereDoesntHave('agences');
        }

        // Filtrage par agence spécifique
        if ($agenceId) {
            $query->whereHas('agences', function (Builder $q) use ($agenceId) {
                $q->where('id_agence', $agenceId);
            });
        }

        $batteries = $query->get();

        // Filtrage par recherche (MAC ID ou ID batterie)
        $filtered = $batteries->filter(function ($battery) use ($search) {
            if (!$search) return true;
            return str_contains(strtolower($battery->mac_id), strtolower($search)) ||
                   str_contains(strtolower($battery->batterie_unique_id), strtolower($search));
        });

        // Transformation des données avec informations BMS
        $result = $filtered->map(function ($battery) {
            $agence = optional($battery->agences->first())->agence;

            // Récupération des données BMS avec cache
            $bmsData = Cache::remember("battery_bms_{$battery->mac_id}", 60, function () use ($battery) {
                return BMSData::where('mac_id', $battery->mac_id)
                    ->orderByDesc('timestamp')
                    ->first();
            });

            $state = json_decode($bmsData->state ?? '{}', true);
            $seting = json_decode($bmsData->seting ?? '{}', true);

            $soc = $state['SOC'] ?? 0;
            $workStatus = $this->getBatteryStatus($state['WorkStatus'] ?? '3');

            // Vérification du statut en ligne
            $heartTime = $seting['heart_time'] ?? null;
            $isOnline = false;

            if ($heartTime) {
                try {
                    $heartTimestamp = Carbon::createFromTimestamp($heartTime);
                    $isOnline = $heartTimestamp->diffInMinutes(now()) < 5;
                } catch (\Exception $e) {
                    $isOnline = false;
                }
            }

            $onlineStatus = $isOnline ? 'En ligne' : 'Hors ligne';

            return [
                'id' => $battery->id,
                'batterie_unique_id' => $battery->batterie_unique_id,
                'mac_id' => $battery->mac_id,
                'fabriquant' => $battery->fabriquant,
                'soc' => $soc,
                'status' => $workStatus,
                'online_status' => $onlineStatus,
                'agence' => $agence->nom_agence ?? 'Non affectée',
            ];
        });

        // Application du filtre dynamique
        if ($filtre) {
            $result = $result->filter(function ($battery) use ($filtre) {
                return match ($filtre) {
                    'en_charge' => $battery['status'] === 'En charge',
                    'decharge_faible' => $battery['soc'] < 30,
                    'en_veille' => $battery['status'] === 'En veille',
                    'en_ligne' => $battery['online_status'] === 'En ligne',
                    'hors_ligne' => $battery['online_status'] === 'Hors ligne',
                    default => true,
                };
            });
        }

        // Calcul des statistiques
        $stats = [
            'totalBatteries' => $this->countTotalBatteries(),
            'batteriesEnAgence' => $this->countBatteriesEnAgence($batteries),
            'batteriesChargees' => $this->countBatteriesChargees($result),
            'batteriesDechargees' => $this->countBatteriesDechargees($result),
            'batteriesEntre50Et80' => $this->countBatteriesEntre50Et80($result),
            'batteriesEnCharge' => $this->countBatteriesEnCharge($result),
        ];

        // Retourner JSON pour les requêtes AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(array_merge([
                'batteries' => $result->values()->all(),
                'agences' => Agence::all(),
                'selectedAgence' => $agenceId,
                'search' => $search,
                'enAgence' => $enAgence,
                'filtre' => $filtre,
            ], $stats));
        }

        // Retourner la vue pour les requêtes normales
        return view('batteries.station.index', array_merge([
            'batteries' => $result,
            'agences' => Agence::all(),
            'selectedAgence' => $agenceId,
            'search' => $search,
            'enAgence' => $enAgence,
            'filtre' => $filtre,
        ], $stats));
    }

    /**
     * Convertit le code de statut de la batterie en texte lisible
     */
    private function getBatteryStatus($statusCode)
    {
        return [
            '0' => 'En décharge',
            '1' => 'En charge',
            '2' => 'En veille',
            '3' => 'Défaut'
        ][$statusCode] ?? 'Inconnu';
    }

    /**
     * Compte le nombre total de batteries validées
     */
    private function countTotalBatteries()
    {
        return Cache::remember('total_batteries_count', 300, function () {
            return BatteriesValide::count();
        });
    }

    /**
     * Compte les batteries affectées à une agence
     */
    private function countBatteriesEnAgence($batteries)
    {
        return $batteries->filter(fn($b) => $b->agences->isNotEmpty())->count();
    }

    /**
     * Compte les batteries avec SOC > 95%
     */
    private function countBatteriesChargees($result)
    {
        return $result->filter(fn($b) => $b['soc'] > 95)->count();
    }

    /**
     * Compte les batteries avec SOC < 30%
     */
    private function countBatteriesDechargees($result)
    {
        return $result->filter(fn($b) => $b['soc'] < 30)->count();
    }

    /**
     * Compte les batteries avec SOC entre 50% et 80%
     */
    private function countBatteriesEntre50Et80($result)
    {
        return $result->filter(fn($b) => ($b['soc'] ?? 0) >= 50 && $b['soc'] <= 80)->count();
    }

    /**
     * Compte les batteries en cours de charge
     */
    private function countBatteriesEnCharge($result)
    {
        return $result->filter(fn($b) => $b['status'] === 'En charge')->count();
    }

    /**
     * Méthode pour obtenir les détails d'une batterie spécifique
     */
    public function show($id)
    {
        $battery = BatteriesValide::with(['agences.agence'])->findOrFail($id);
        
        // Récupération des données BMS
        $bmsData = BMSData::where('mac_id', $battery->mac_id)
            ->orderByDesc('timestamp')
            ->first();

        $state = json_decode($bmsData->state ?? '{}', true);
        $seting = json_decode($bmsData->seting ?? '{}', true);

        $batteryData = [
            'id' => $battery->id,
            'batterie_unique_id' => $battery->batterie_unique_id,
            'mac_id' => $battery->mac_id,
            'fabriquant' => $battery->fabriquant,
            'soc' => $state['SOC'] ?? 0,
            'status' => $this->getBatteryStatus($state['WorkStatus'] ?? '3'),
            'agence' => optional($battery->agences->first())->agence->nom_agence ?? 'Non affectée',
            'bms_data' => $state,
            'settings' => $seting,
        ];

        return response()->json($batteryData);
    }

    /**
     * Méthode pour exporter les données en CSV
     */
    public function export(Request $request)
    {
        $search = $request->query('search');
        $agenceId = $request->query('agence_id');
        $enAgence = $request->query('en_agence');
        $filtre = $request->query('filtre');

        // Réutiliser la logique de filtrage
        $request->merge([
            'search' => $search,
            'agence_id' => $agenceId,
            'en_agence' => $enAgence,
            'filtre' => $filtre
        ]);

        $response = $this->index($request);
        $batteries = $response->getData()->batteries ?? [];

        $csvData = [];
        $csvData[] = ['ID', 'MAC ID', 'Fabriquant', 'SOC (%)', 'Statut', 'Connexion', 'Agence'];

        foreach ($batteries as $battery) {
            $csvData[] = [
                $battery['batterie_unique_id'],
                $battery['mac_id'],
                $battery['fabriquant'],
                $battery['soc'],
                $battery['status'],
                $battery['online_status'],
                $battery['agence']
            ];
        }

        $filename = 'batteries_station_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Méthode pour obtenir les statistiques en temps réel
     */
    public function getStats()
    {
        $batteries = BatteriesValide::with(['agences.agence'])->get();
        
        $result = $batteries->map(function ($battery) {
            $bmsData = Cache::remember("battery_bms_{$battery->mac_id}", 60, function () use ($battery) {
                return BMSData::where('mac_id', $battery->mac_id)
                    ->orderByDesc('timestamp')
                    ->first();
            });

            $state = json_decode($bmsData->state ?? '{}', true);
            $soc = $state['SOC'] ?? 0;
            $workStatus = $this->getBatteryStatus($state['WorkStatus'] ?? '3');

            return [
                'soc' => $soc,
                'status' => $workStatus,
                'has_agence' => $battery->agences->isNotEmpty()
            ];
        });

        return response()->json([
            'totalBatteries' => $this->countTotalBatteries(),
            'batteriesEnAgence' => $result->filter(fn($b) => $b['has_agence'])->count(),
            'batteriesChargees' => $result->filter(fn($b) => $b['soc'] > 95)->count(),
            'batteriesDechargees' => $result->filter(fn($b) => $b['soc'] < 30)->count(),
            'batteriesEntre50Et80' => $result->filter(fn($b) => $b['soc'] >= 50 && $b['soc'] <= 80)->count(),
            'batteriesEnCharge' => $result->filter(fn($b) => $b['status'] === 'En charge')->count(),
        ]);
    }
}