<?php

namespace App\Http\Controllers\Compteur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\WhatsAppService;

class CompteurController extends Controller
{
    // app/Http/Controllers/Compteur/CompteurController.php

public function index(Request $request, WhatsAppService $whatsAppService)
{
    $query = \App\Models\PowerReading::with(['user', 'agence'])->orderByDesc('created_at');

    if ($request->filled('agence_id') && $request->agence_id !== 'all') {
        $query->where('agence_id', $request->agence_id);
    }

    if ($request->filled('user_id') && $request->user_id !== 'all') {
        $query->where('user_id', $request->user_id);
    }

    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
    }

    $readings = $query->get();

    foreach ($readings as $reading) {
        $total_kw = $reading->kw1 + $reading->kw2 + $reading->kw3 + $reading->kw4;

        if ($total_kw < 65) {
            $message = "⚠️ Le compteur de la station « {$reading->agence->nom_agence} » est à $total_kw W. Vérifiez immédiatement.";
            $whatsAppService->sendMessage('696098576', $message);
        }
    }

    $agences = \App\Models\Agence::all();
    $swappeurs = \App\Models\UsersAgence::all();

    return view('compteur.index', compact('readings', 'agences', 'swappeurs'));
}


   
}
