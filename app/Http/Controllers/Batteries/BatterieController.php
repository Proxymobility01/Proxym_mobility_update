<?php
namespace App\Http\Controllers\Batteries;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Batterie;
use App\Models\BatteriesValide;
use App\Models\BMSData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class BatterieController extends Controller
{
    // Liste des batteries (support AJAX)
    public function index(Request $request)
{
    try {
        $search = $request->search;
        $status = $request->status;
        $batteries = collect();

        if ($request->filled('status')) {
            if ($status == 'validé') {
                // Batteries validées
                $batteries = BatteriesValide::when($search, function($query, $search) {
                    return $query->where(function($q) use ($search) {
                        $q->where('fabriquant', 'LIKE', '%' . $search . '%')
                          ->orWhere('mac_id', 'LIKE', '%' . $search . '%')
                          ->orWhere('gps', 'LIKE', '%' . $search . '%');
                    });
                })->get()->map(function($batterie) {
                    $batterie->statut = 'validé';
                    return $batterie;
                });
            } else {
                // Batteries dans la table 'batteries'
                $batteries = Batterie::where('statut', $status)
                    ->when($search, function($query, $search) {
                        return $query->where(function($q) use ($search) {
                            $q->where('fabriquant', 'LIKE', '%' . $search . '%')
                              ->orWhere('mac_id', 'LIKE', '%' . $search . '%')
                              ->orWhere('gps', 'LIKE', '%' . $search . '%');
                        });
                    })->get();
            }
        } else {
            // Tous statuts confondus
            $batteries = Batterie::when($search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('fabriquant', 'LIKE', '%' . $search . '%')
                      ->orWhere('mac_id', 'LIKE', '%' . $search . '%')
                      ->orWhere('gps', 'LIKE', '%' . $search . '%');
                });
            })->get();
            $batteriesValidees = BatteriesValide::when($search, function($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('fabriquant', 'LIKE', '%' . $search . '%')
                      ->orWhere('mac_id', 'LIKE', '%' . $search . '%')
                      ->orWhere('gps', 'LIKE', '%' . $search . '%');
                });
            })->get()->map(function($batterie) {
                $batterie->statut = 'validé';
                return $batterie;
            });
            $batteries = $batteries->merge($batteriesValidees);
        }

        // 1) Peupler les données BMS pour chaque batterie (stateData, setingData, etc.)
        foreach ($batteries as $battery) {
            $this->populateBatteryData($battery); 
            // 2) Récupérer le SOC si disponible
            $battery->soc = $battery->stateData['SOC'] ?? 0; 
        }

        if ($request->wantsJson()) {
            return response()->json($batteries);
        }
        return view('batteries.index', compact('batteries'));
    } catch (Exception $e) {
        \Log::error("Erreur lors de la récupération des batteries : " . $e->getMessage());
        return response()->json(['error' => 'Erreur de récupération'], 500);
    }
}

private function populateBatteryData($battery)
{
    // 1) Récupérer la dernière donnée BMS correspondant au mac_id de la batterie
    //    (Par exemple via la table bms_data, ou via un cache)
    $bmsData = $this->getLatestBMSData($battery->mac_id);

    // 2) Associer les données au modèle
    $battery->stateData  = $bmsData['stateData'];
    $battery->setingData = $bmsData['setingData'];
    $battery->longitude  = $bmsData['longitude'];
    $battery->latitude   = $bmsData['latitude'];

    // 3) Ajouter le SOC (si présent)
    $battery->soc = $battery->stateData['SOC'] ?? 0;

    
}



// Fonction pour récupérer les dernières données BMS
/**
 * Récupère la dernière entrée de la table bms_data pour un mac_id donné.
 * Retourne un tableau contenant 'stateData', 'setingData', 'longitude', 'latitude'.
 */
private function getLatestBMSData($mac_id)
{
    // Rechercher la dernière donnée BMS correspondant au mac_id
    $bmsData = BMSData::where('mac_id', $mac_id)
        ->orderBy('timestamp', 'desc')
        ->first();

    if ($bmsData) {
        return [
            'stateData'  => json_decode($bmsData->state, true),
            'setingData' => json_decode($bmsData->seting, true),
            'longitude'  => $bmsData->longitude,
            'latitude'   => $bmsData->latitude,
        ];
    }

    // Si aucune donnée n’est trouvée, on renvoie un tableau vide ou des valeurs par défaut
    return [
        'stateData'  => [],
        'setingData' => [],
        'longitude'  => null,
        'latitude'   => null,
    ];
}





    // Ajouter une batterie (support AJAX)
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'mac_id' => 'required|unique:batteries,mac_id',
                'fabriquant' => 'required',
                'gps' => 'required',
                'date_production' => 'nullable|date',
            ]);

            // Génération de l'ID unique
            $today = Carbon::now()->format('Ymd');
            $lastBatterie = Batterie::where('batterie_unique_id', 'like', 'PXB' . $today . '%')
                ->orderBy('batterie_unique_id', 'desc')
                ->first();
            $counter = $lastBatterie ? (int)substr($lastBatterie->batterie_unique_id, -3) + 1 : 1;
            $uniqueId = 'PXB' . $today . str_pad($counter, 3, '0', STR_PAD_LEFT);

            $batterie = Batterie::create([
                'mac_id' => $validatedData['mac_id'],
                'fabriquant' => $validatedData['fabriquant'],
                'gps' => $validatedData['gps'],
                'date_production' => $validatedData['date_production'],
                'batterie_unique_id' => $uniqueId,
                'statut' => 'en attente'
            ]);
            return response()->json($batterie, 201);
        } catch (Exception $e) {
            Log::error("Erreur lors de l'ajout de la batterie : " . $e->getMessage());
            return response()->json(['error' => 'Erreur d\'ajout'], 500);
        }
    }

    // Mise à jour d'une batterie (support AJAX)
    public function update(Request $request, $id)
    {
        try {
            $batterie = Batterie::findOrFail($id);
            $validatedData = $request->validate([
                'fabriquant' => 'required',
                'gps' => 'required',
                'date_production' => 'nullable|date',
            ]);
            $batterie->update([
                'fabriquant' => $validatedData['fabriquant'],
                'gps' => $validatedData['gps'],
                'date_production' => $validatedData['date_production'],
                'statut' => 'en attente'
            ]);
            return response()->json($batterie);
        } catch (Exception $e) {
            Log::error("Erreur lors de la mise à jour de la batterie : " . $e->getMessage());
            return response()->json(['error' => 'Erreur de mise à jour'], 500);
        }
    }

    // Valider une batterie
    public function validate(Request $request, $id)
    {
        try {
            $batterie = Batterie::findOrFail($id);
            $validatedData = $request->validate([
                'capacite' => 'required|numeric',
                'tension' => 'required|numeric',
            ]);
            // Créer une batterie validée dans la table dédiée
            $batterieValidee = BatteriesValide::create([
                'mac_id' => $batterie->mac_id,
                'batterie_unique_id' => $batterie->batterie_unique_id,
                'fabriquant' => $batterie->fabriquant,
                'gps' => $batterie->gps,
                'date_production' => $batterie->date_production,
                'capacite' => $validatedData['capacite'],
                'tension' => $validatedData['tension'],
            ]);
            // Mise à jour du statut et suppression logique
            $batterie->statut = 'validé';
            $batterie->deleted_at = Carbon::now();
            $batterie->save();
            return response()->json($batterieValidee);
        } catch (Exception $e) {
            Log::error("Erreur lors de la validation de la batterie : " . $e->getMessage());
            return response()->json(['error' => 'Erreur de validation'], 500);
        }
    }

    // Rejeter une batterie
    public function reject($id)
    {
        try {
            $batterie = Batterie::findOrFail($id);
            $batterie->statut = 'rejeté';
            $batterie->save();
            return response()->json($batterie);
        } catch (Exception $e) {
            Log::error("Erreur lors du rejet de la batterie : " . $e->getMessage());
            return response()->json(['error' => 'Erreur de rejet'], 500);
        }
    }

    // Supprimer une batterie (suppression logique)
    public function destroy($id)
    {
        try {
            $batterie = Batterie::findOrFail($id);
            $batterie->deleted_at = Carbon::now();
            $batterie->save();
            return response()->json(['message' => 'Batterie supprimée']);
        } catch (Exception $e) {
            Log::error("Erreur lors de la suppression de la batterie : " . $e->getMessage());
            return response()->json(['error' => 'Erreur de suppression'], 500);
        }
    }

    // (Optionnel) Affichage d'une page dédiée aux batteries validées
    public function indexValide()
    {
        try {
            $batteriesValidees = BatteriesValide::all();
            return view('batteries.valide', compact('batteriesValidees'));
        } catch (Exception $e) {
            Log::error("Erreur lors de l'affichage des batteries validées : " . $e->getMessage());
            return redirect()->route('batteries.index')->with('error', 'Erreur lors du chargement');
        }
    }
}
