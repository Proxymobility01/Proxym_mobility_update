<?php

namespace App\Http\Controllers\Swaps;

use App\Http\Controllers\Controller;
use App\Models\Swap;
use App\Models\Agence;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SwapParHeureController extends Controller
{
    public function index()
    {
        Log::info('📊 Chargement de la vue des swaps par heure et agence');
        $agences = Agence::orderBy('nom_agence')->get();
        return view('swaps.statistiques_par_heure', compact('agences'));
    }

    public function api(Request $request)
    {
        $periode = $request->input('periode', 'today');
        $date = $request->input('date');
        $start = $request->input('start');
        $end = $request->input('end');

        Log::info("📡 [API SWAPS] Période: $periode");

        switch ($periode) {
            case 'week':
                $from = Carbon::now()->startOfWeek();
                $to = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $from = Carbon::now()->startOfMonth();
                $to = Carbon::now()->endOfMonth();
                break;
            case 'year':
                $from = Carbon::now()->startOfYear();
                $to = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $from = $date ? Carbon::parse($date)->startOfDay() : Carbon::today();
                $to = $date ? Carbon::parse($date)->endOfDay() : Carbon::tomorrow();
                break;
            case 'range':
                $from = $start ? Carbon::parse($start)->startOfDay() : Carbon::today();
                $to = $end ? Carbon::parse($end)->endOfDay() : Carbon::tomorrow();
                break;
            default:
                $from = Carbon::today();
                $to = Carbon::tomorrow();
                break;
        }

        Log::info("⏰ Période : $from ➡️ $to");

        $agences = Agence::orderBy('nom_agence')->get();
        $data = [];

        for ($h = 0; $h < 24; $h++) {
            $ligne = ['heure' => sprintf('%02d:00', $h)];
            foreach ($agences as $agence) {
                $count = Swap::where('id_agence', $agence->id)
                    ->whereBetween('swap_date', [$from, $to])
                    ->where(DB::raw('HOUR(swap_date)'), $h)
                    ->count();

                $ligne[$agence->id] = $count;
            }
            $data[] = $ligne;
        }

        return response()->json([
            'status' => 'success',
            'agences' => $agences->map(fn($a) => ['id' => $a->id, 'nom' => $a->nom_agence]),
            'data' => $data,
        ]);
    }
}
