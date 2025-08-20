<?php

namespace App\Http\Controllers\Swaps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Swap;
use App\Models\BatteriesValide;
use App\Models\UsersAgence;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class EtatProprietaireBatterieController extends Controller
{
    public function index(Request $request)
{
    $date = $request->input('date', now()->toDateString()); // 🗓️ date filtrée ou aujourd'hui
    $start = Carbon::parse($date)->startOfDay();
    $end = Carbon::parse($date)->endOfDay();
    $interval = 30;

    $slots = [];
    $period = CarbonPeriod::create($start, "{$interval} minutes", $end);
    foreach ($period as $dt) {
        $slots[] = $dt->format('H:i');
    }

    $batteries = BatteriesValide::all();
    $swaps = Swap::with('swappeur.agence')
                ->whereDate('swap_date', $date)
                ->orderBy('swap_date')
                ->get();

    $data = [];

    foreach ($batteries as $batt) {
        $mac = $batt->mac_id;
        $data[$mac] = [];
        $currentOwner = 'Entrepôt';

        foreach ($slots as $slot) {
            $currentTime = Carbon::parse($date . ' ' . $slot);
            $lastSwap = $swaps->filter(function ($swap) use ($batt, $currentTime) {
                return (
                    ($swap->battery_out_id === $batt->id || $swap->battery_in_id === $batt->id)
                    && Carbon::parse($swap->swap_date) <= $currentTime
                );
            })->last();

            if ($lastSwap) {
                if ($lastSwap->battery_out_id === $batt->id) {
                    $currentOwner = $lastSwap->nom ?? 'Chauffeur inconnu';
                } elseif ($lastSwap->battery_in_id === $batt->id) {
                    $currentOwner = optional(optional($lastSwap->swappeur)->agence)->nom_agence ?? 'Station inconnue';
                }
            }

            $data[$mac][$slot] = $currentOwner;
        }
    }

    return view('swaps.etat_proprietaire_batteries', compact('data', 'slots', 'date'));
}

}
