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

        if ($enAgence === "1") {
            $query->whereHas('agences');
        } elseif ($enAgence === "0") {
            $query->whereDoesntHave('agences');
        }

        if ($agenceId) {
            $query->whereHas('agences', function (Builder $q) use ($agenceId) {
                $q->where('id_agence', $agenceId);
            });
        }

        $batteries = $query->get();

        $filtered = $batteries->filter(function ($battery) use ($search) {
            if (!$search) return true;
            return str_contains($battery->mac_id, $search) ||
                   str_contains($battery->batterie_unique_id, $search);
        });

        $result = $filtered->map(function ($battery) {
            $agence = optional($battery->agences->first())->agence;

            $bmsData = Cache::remember("battery_bms_{$battery->mac_id}", 60, function () use ($battery) {
                return BMSData::where('mac_id', $battery->mac_id)
                    ->orderByDesc('timestamp')
                    ->first();
            });

            $state = json_decode($bmsData->state ?? '{}', true);
            $seting = json_decode($bmsData->seting ?? '{}', true);

            $soc = $state['SOC'] ?? 0;
            $workStatus = $this->getBatteryStatus($state['WorkStatus'] ?? '3');

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

        // ➕ Appliquer le filtre dynamique
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

        return view('batteries.station.index', [
            'batteries' => $result,
            'agences' => Agence::all(),
            'selectedAgence' => $agenceId,
            'search' => $search,
            'enAgence' => $enAgence,
            'filtre' => $filtre,
            'totalBatteries' => $this->countTotalBatteries(),
            'batteriesEnAgence' => $this->countBatteriesEnAgence($batteries),
            'batteriesChargees' => $this->countBatteriesChargees($result),
            'batteriesDechargees' => $this->countBatteriesDechargees($result),
            'batteriesEntre50Et80' => $this->countBatteriesEntre50Et80($result),
            'batteriesEnCharge' => $this->countBatteriesEnCharge($result),
        ]);
    }

    private function getBatteryStatus($statusCode)
    {
        return [
            '0' => 'En décharge',
            '1' => 'En charge',
            '2' => 'En veille',
            '3' => 'Défaut'
        ][$statusCode] ?? 'Inconnu';
    }

    private function countTotalBatteries()
    {
        return BatteriesValide::count();
    }

    private function countBatteriesEnAgence($batteries)
    {
        return $batteries->filter(fn($b) => $b->agences->isNotEmpty())->count();
    }

    private function countBatteriesChargees($result)
    {
        return $result->filter(fn($b) => $b['soc'] > 95)->count();
    }

    private function countBatteriesDechargees($result)
    {
        return $result->filter(fn($b) => $b['soc'] < 30)->count();
    }

    private function countBatteriesEntre50Et80($result)
    {
        return $result->filter(fn($b) => ($b['soc'] ?? 0) > 50 && $b['soc'] < 80)->count();
    }

    private function countBatteriesEnCharge($result)
    {
        return $result->filter(fn($b) => $b['status'] === 'En charge')->count();
    }
}
