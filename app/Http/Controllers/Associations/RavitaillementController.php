<?php

namespace App\Http\Controllers\Associations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Entrepot;
use App\Models\BatteryEntrepot;
use App\Models\BatteriesValide;
use App\Models\BatteryAgence;
use App\Models\HistoriqueEntrepot;
use App\Models\BatteryMotoUserAssociation;
use Carbon\Carbon;

class RavitaillementController extends Controller
{
    // Afficher la page des ravitaillements avec historique et statistiques
public function index()
{
    // Récupérer tous les entrepôts pour le formulaire et les filtres
    $entrepots = Entrepot::all();
    
    // Récupérer les batteries disponibles (ni dans un entrepôt, ni dans une agence)
    $batteries = BatteriesValide::whereDoesntHave('batteryEntrepots')
                           ->whereDoesntHave('batteryAgences')
                           ->whereDoesntHave('batteryMotoUserAssociations')
                           ->get();
    
    // Récupérer l'historique des ravitaillements depuis la table HistoriqueEntrepot (avec pagination)
   // $ravitailles = HistoriqueEntrepot::with(['entrepot', 'batteryValide'])  // Relations possibles à adapter en fonction de la structure
   //                                ->orderBy('created_at', 'desc')
   //                                ->paginate(15);

   $ravitailles = BatteryEntrepot::orderBy('created_at', 'desc')->paginate(15);

                                   
    // Statistiques
    $stats = [
        'total_batteries' => BatteriesValide::count(),
        'batteries_entrepot' => BatteryEntrepot::count(),
        'batteries_disponibles' => BatteriesValide::whereDoesntHave('batteryEntrepots')->whereDoesntHave('batteryAgences')->whereDoesntHave('batteryMotoUserAssociations')->count()
    ];
    
    return view('association.ravitaillement', compact('entrepots', 'batteries', 'ravitailles', 'stats'));
}


// ravitaillement de batterie
public function store(Request $request)
{
    // Validation des entrées
    $request->validate([
        'entrepot_id' => 'required|exists:entrepots,id',
        'batteries' => 'required|array|min:1',
        'batteries.*' => 'exists:batteries_valides,id',
    ]);

    $count = 0;
    $batteriesIgnorees = [];

    foreach ($request->batteries as $battery_id) {
        // Charger la batterie
        $battery = BatteriesValide::find($battery_id);

        $dejaEnAgence = $battery->batteryAgences()->exists();
        $dejaEnEntrepot = $battery->batteryEntrepots()->exists();
        $dejaChezChauffeur = $battery->batteryMotoUserAssociations()->exists();

        if ($dejaEnAgence || $dejaEnEntrepot || $dejaChezChauffeur) {
            $batteriesIgnorees[] = $battery->batterie_unique_id;
            continue;
        }

        // Si la batterie est libre, créer l'association avec l'entrepôt
        BatteryEntrepot::create([
            'id_battery_valide' => $battery_id,
            'id_entrepot' => $request->entrepot_id,
        ]);

      //  HistoriqueEntrepot::create([
    //        'id_entrepot' => $request->entrepot_id,
    //        'id_distributeur' => auth()->id(),
    //        'bat_entrante' => $battery_id,
    //        'type_swap' => 'livraison',
     //   ]);

        $count++;
    }



    // Préparer le message de retour
    $message = "$count batteries ont été ravitaillées avec succès.";
    if (!empty($batteriesIgnorees)) {
        $message .= " Les batteries suivantes ont été ignorées car déjà utilisées : " . implode(', ', $batteriesIgnorees);
        return redirect()->route('ravitailler.batteries.index')->with('success', $message)->with('warning_batteries', $batteriesIgnorees);
    }

    return redirect()->route('ravitailler.batteries.index')->with('success', $message);
}

    
    // API pour les filtres
    public function filter(Request $request)
    {
        $query = BatteryEntrepot::with(['entrepot', 'batteryValide']);
        
        // Filtre par entrepôt
        if ($request->has('entrepot_id') && $request->entrepot_id !== 'all') {
            $query->where('id_entrepot', $request->entrepot_id);
        }
        
        // Filtre par recherche (VIN ou ID de batterie)
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('batteryValide', function($q) use ($search) {
                $q->where('batterie_unique_id', 'like', "%$search%")
                  ->orWhere('mac_id', 'like', "%$search%");
            });
        }
        
        // Filtre par date
        if ($request->has('date_filter')) {
            switch ($request->date_filter) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', Carbon::now()->month)
                          ->whereYear('created_at', Carbon::now()->year);
                    break;
                case 'custom':
                    if ($request->has('custom_date')) {
                        $query->whereDate('created_at', $request->custom_date);
                    }
                    break;
            }
        }
        
        // Récupérer les résultats
        $ravitailles = $query->orderBy('created_at', 'desc')->get();
        
        // Grouper par jour pour l'affichage
        $ravitaillesByDay = [];
        foreach ($ravitailles as $ravitaille) {
            $date = Carbon::parse($ravitaille->created_at)->format('Y-m-d');
            if (!isset($ravitaillesByDay[$date])) {
                $ravitaillesByDay[$date] = [];
            }
            
            $ravitaillesByDay[$date][] = [
                'id' => $ravitaille->id,
                'entrepot' => $ravitaille->entrepot->nom_entrepot,
                'batterie_id' => $ravitaille->batteryValide->batterie_unique_id,
                'batterie_vin' => $ravitaille->batteryValide->mac_id,
                'date' => Carbon::parse($ravitaille->created_at)->format('d/m/Y H:i')
            ];
        }
        
        // Retourner les données formatées
        return response()->json([
            'ravitaillesByDay' => $ravitaillesByDay,
            'timeLabel' => $this->getTimeLabel($request->date_filter, $request->custom_date ?? null)
        ]);
    }
    
    // API pour les statistiques
    public function getStats(Request $request)
    {
        $timeFilter = $request->timeFilter ?? 'today';
        $customDate = $request->customDate ?? null;
        
        $query = BatteryEntrepot::query();
        
        // Appliquer le filtre de temps
        switch ($timeFilter) {
            case 'today':
                $query->whereDate('created_at', Carbon::today());
                break;
            case 'week':
                $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
            case 'custom':
                if ($customDate) {
                    $query->whereDate('created_at', $customDate);
                }
                break;
        }
        
        // Compter les ravitaillements dans la période
        $ravitaillementCount = $query->count();
        
        // Obtenir le nombre total de batteries disponibles
        $availableBatteries = BatteriesValide::whereDoesntHave('batteryEntrepots')
                                          ->whereDoesntHave('batteryAgences')
                                          ->count();
                                          
        // Obtenir le nombre total d'entrepôts
        $entrepotCount = Entrepot::count();
        
        return response()->json([
            'ravitaillementCount' => $ravitaillementCount,
            'availableBatteries' => $availableBatteries,
            'entrepotCount' => $entrepotCount,
            'timeLabel' => $this->getTimeLabel($timeFilter, $customDate)
        ]);
    }
    
    private function getTimeLabel($timeFilter, $customDate = null)
    {
        switch ($timeFilter) {
            case 'today':
                return "aujourd'hui";
            case 'week':
                return "cette semaine";
            case 'month':
                return "ce mois";
            case 'year':
                return "cette année";
            case 'custom':
                if ($customDate) {
                    return "le " . Carbon::parse($customDate)->format('d/m/Y');
                }
                return "date personnalisée";
            default:
                return "tout l'historique";
        }
    }
}