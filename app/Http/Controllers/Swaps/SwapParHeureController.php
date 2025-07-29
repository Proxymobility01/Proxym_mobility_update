<?php

namespace App\Http\Controllers\Swaps;

use App\Http\Controllers\Controller;
use App\Models\Swap;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SwapParHeureController extends Controller
{
    public function index()
    {
        Log::info('🔍 Chargement de la vue des swaps par heure');
        return view('swaps.statistiques_par_heure');
    }

    public function api(Request $request)
    {
        $periode = $request->input('periode', 'today');
        $date = $request->input('date');
        $start = $request->input('start');
        $end = $request->input('end');

        Log::info("📡 [API SWAPS] Requête AJAX période: $periode");

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

        Log::info("⏰ Période analysée : $from ➡️ $to");

        $swapsParHeure = Swap::select(
                DB::raw('HOUR(swap_date) as heure'),
                DB::raw('COUNT(*) as total')
            )
            ->whereBetween('swap_date', [$from, $to])
            ->groupBy(DB::raw('HOUR(swap_date)'))
            ->orderBy('heure')
            ->pluck('total', 'heure');

        $resultats = [];
        for ($i = 0; $i < 24; $i++) {
            $resultats[] = [
                'heure' => sprintf('%02d:00', $i),
                'total' => $swapsParHeure[$i] ?? 0
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $resultats
        ]);
    }
}
