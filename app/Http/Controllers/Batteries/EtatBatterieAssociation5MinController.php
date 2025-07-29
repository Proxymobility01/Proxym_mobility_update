<?php

namespace App\Http\Controllers\Batteries;

use App\Http\Controllers\Controller;
use App\Models\EtatBatterieAssociation5Min;
use App\Models\BatteriesValide;
use App\Models\BMSData;
use App\Models\BatteryMotoUserAssociation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\ValidatedUser;

class EtatBatterieAssociation5MinController extends Controller
{


    public function index()
    {
        $etats = EtatBatterieAssociation5Min::with('user')->orderByDesc('captured_at')->paginate(20);
        return view('batteries.etat_batteries', compact('etats'));
    }

    public function enregistrer()
    {
        Log::info('⏱️ Début du scan des batteries (5 min)');

        $batteries = BatteriesValide::all();
        $count = 0;

        foreach ($batteries as $battery) {
            $macId = $battery->mac_id;

            // 🔌 Récupération du dernier état BMS
            $bms = Cache::remember("battery_bms_{$macId}", 60, function () use ($macId) {
                return BMSData::where('mac_id', $macId)->orderByDesc('timestamp')->first();
            });

            $soc = optional(json_decode($bms->state ?? '{}'))->SOC ?? null;

            // 🔗 Récupération de la dernière association batterie → utilisateur
            $association = BatteryMotoUserAssociation::where('battery_id', $battery->id)
                ->whereHas('association', function ($query) {
                    $query->whereNull('deleted_at');
                })
                ->with('association.validatedUser')
                ->latest('created_at')
                ->first();

            $user = $association?->association?->validatedUser;
            $userId = $user?->id;
            $userName = $user ? $user->nom . ' ' . $user->prenom : 'Non associé';

            // 📝 Log
            Log::info("🔋 Batterie {$macId} | SOC: " . ($soc ?? 'N/A') . "% | Utilisateur: {$userName}");

            // 💾 Enregistrement
            EtatBatterieAssociation5Min::create([
                'mac_id' => $macId,
                'soc' => $soc,
                'user_id' => $userId,
                'captured_at' => now(),
            ]);

            $count++;
        }

        Log::info("✅ Enregistrement terminé pour {$count} batteries.");
        return response()->json([
            'status' => 'ok',
            'message' => "Enregistrement effectué pour {$count} batteries.",
        ]);
    }
}
