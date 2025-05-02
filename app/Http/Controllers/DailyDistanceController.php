<?php

namespace App\Http\Controllers;

use App\Models\DailyDistance;
use Illuminate\Http\Request;

class DailyDistanceController extends Controller
{
    public function index(Request $request)
    {
        // Optional filter by date
        $date = $request->input('date', now()->toDateString());

        $distances = DailyDistance::with('user')
            ->whereDate('date', $date)
            ->orderByDesc('total_distance_km')
            ->get();

        return view('distances.index', compact('distances', 'date'));
    }
}
