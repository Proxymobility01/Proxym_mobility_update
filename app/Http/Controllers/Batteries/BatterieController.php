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
use Illuminate\Support\Facades\DB;
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

        // Si aucune donnée n'est trouvée, on renvoie un tableau vide ou des valeurs par défaut
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
        // Validation sécurisée
        $validatedData = $request->validate([
            'mac_id' => 'required|unique:batteries_valides,mac_id',
            'fabriquant' => 'required|string|max:255',
            'gps' => 'required|string|max:255',
            'date_production' => 'nullable|date',
        ]);

        // Génération d’un ID unique (format : PXBYYYYMMDDnnn)
        $today = now()->format('Ymd');
        $last = BatteriesValide::where('batterie_unique_id', 'like', 'PXB' . $today . '%')
                        ->orderByDesc('batterie_unique_id')
                        ->first();
        $counter = $last ? ((int)substr($last->batterie_unique_id, -3)) + 1 : 1;
        $uniqueId = 'PXB' . $today . str_pad($counter, 3, '0', STR_PAD_LEFT);

        // Création batterie
        $batterie = Batterie::create([
            'mac_id' => $validatedData['mac_id'],
            'fabriquant' => $validatedData['fabriquant'],
            'gps' => $validatedData['gps'],
            'date_production' => $validatedData['date_production'] ?? null,
            'batterie_unique_id' => $uniqueId,
            'statut' => 'en attente',
        ]);

        return response()->json([
            'message' => 'Batterie ajoutée avec succès',
            'data' => $batterie,
        ], 201);

    } catch (ValidationException $e) {
        // Erreurs de validation
        return response()->json([
            'message' => 'Erreur de validation',
            'errors' => $e->errors(),
        ], 422);

    } catch (QueryException $e) {
        // Erreurs SQL (ex: violation contrainte unique, colonnes manquantes)
        Log::error("Erreur SQL lors de l'ajout de la batterie : " . $e->getMessage());
        return response()->json([
            'message' => 'Erreur lors de l’insertion en base de données',
            'error' => $e->getMessage(),
        ], 500);

    } catch (\Exception $e) {
        // Toutes les autres erreurs
        Log::error("Erreur inattendue lors de l'ajout de la batterie : " . $e->getMessage());
        return response()->json([
            'message' => 'Erreur inattendue',
            'error' => $e->getMessage(),
        ], 500);
    }
}

// Ajoute cette méthode dans ton BatterieController

public function edit($id)
{
    try {
        // On vérifie si la batterie est dans la table validée ou temporaire
        $batterie =  BatteriesValide::find($id) ;

        if (!$batterie) {
            return response()->json(['error' => 'Batterie introuvable.'], 404);
        }

        return response()->json($batterie);

    } catch (\Exception $e) {
        \Log::error("Erreur lors du chargement de la batterie pour édition : " . $e->getMessage());
        return response()->json(['error' => 'Erreur serveur.'], 500);
    }
}


    // Mise à jour d'une batterie (support AJAX)
    public function update(Request $request, $id)
    {
        try {
            $batterie = BatteriesValide::findOrFail($id);
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


// preremplir les champs pour la validation
    public function getValidationForm($id)
{
    try {
        $batterie = Batterie::findOrFail($id);
        return response()->json($batterie);
    } catch (\Exception $e) {
        \Log::error("Erreur chargement données pour validation : " . $e->getMessage());
        return response()->json(['error' => 'Batterie introuvable'], 404);
    }
}


    // Valider une batterie

public function validate(Request $request, $id)
{
    DB::beginTransaction();

    try {
        // Étape 1 : Récupérer la batterie à valider
        $batterie = Batterie::findOrFail($id);

        // Étape 2 : Mettre à jour ses champs
        $batterie->mac_id = $request->mac_id;
        $batterie->fabriquant = $request->fabriquant;
        $batterie->gps = $request->gps;
        $batterie->date_production = $request->date_production;
        $batterie->statut = 'validé';
        $batterie->save();

        // Étape 3 : Copier les données dans batteries_valides
        $batterieValide = new BatteriesValide();
        $batterieValide->batterie_unique_id = $batterie->batterie_unique_id;
        $batterieValide->mac_id = $batterie->mac_id;
        $batterieValide->fabriquant = $batterie->fabriquant;
        $batterieValide->gps = $batterie->gps;
        $batterieValide->date_production = $batterie->date_production;
        $batterieValide->statut = 'validé';
        $batterieValide->save();

        // Étape 4 : Supprimer la batterie de la table source
        $batterie->delete();

        DB::commit();

        return response()->json([
            'message' => 'Batterie validée, transférée et supprimée avec succès.'
        ], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Erreur transactionnelle lors de la validation : ' . $e->getMessage());

        return response()->json([
            'error' => 'Échec de la validation. Aucune modification effectuée.',
            'details' => $e->getMessage()
        ], 500);
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

    /**
     * Route principale pour afficher la page avec la carte
     */
    public function showBatteriesOnMap()
    {
        try {
            return view('batteries.map');
        } catch (Exception $e) {
            Log::error("Erreur lors de l'affichage de la carte : " . $e->getMessage());
            return redirect()->route('batteries.index')->with('error', 'Erreur lors de l\'affichage de la carte : ' . $e->getMessage());
        }
    }

    /**
     * API endpoint pour récupérer les données des batteries pour la carte
     * Méthode hautement optimisée utilisant des requêtes SQL directes et du cache
     */
    public function getBatteriesMapData()
    {
        try {
            // Utiliser le cache pour 2 minutes (durée courte pour garder les données fraîches)
            $result = Cache::remember('batteries_map_data', 120, function () {
                // Récupérer toutes les batteries validées (cette requête est rapide)
                $batteriesValidees = BatteriesValide::select(['id', 'mac_id', 'batterie_unique_id', 'fabriquant', 'gps'])
                    ->get();
                
                if ($batteriesValidees->isEmpty()) {
                    return [];
                }
                
                // Collecter tous les mac_id pour la requête suivante
                $macIds = $batteriesValidees->pluck('mac_id')->toArray();
                
                // Requête optimisée utilisant une sous-requête SQL pour obtenir les données BMS les plus récentes
                // Ceci évite de faire une requête par batterie, ce qui améliore considérablement les performances
                $latestBmsData = DB::select('
                    SELECT b.* 
                    FROM bms_data b
                    INNER JOIN (
                        SELECT mac_id, MAX(timestamp) as latest_timestamp
                        FROM bms_data
                        WHERE mac_id IN (' . implode(',', array_fill(0, count($macIds), '?')) . ')
                        GROUP BY mac_id
                    ) m ON b.mac_id = m.mac_id AND b.timestamp = m.latest_timestamp
                ', $macIds);
                
                // Créer un tableau indexé par mac_id pour accès rapide
                $bmsDataByMacId = [];
                foreach ($latestBmsData as $data) {
                    $bmsDataByMacId[$data->mac_id] = $data;
                }
                
                // Construire le tableau final des batteries avec leurs données BMS
                $result = [];
                foreach ($batteriesValidees as $battery) {
                    if (isset($bmsDataByMacId[$battery->mac_id])) {
                        $bmsData = $bmsDataByMacId[$battery->mac_id];
                        
                        // Vérifier si nous avons des coordonnées valides
                        if ($bmsData->latitude && $bmsData->longitude) {
                            $stateData = json_decode($bmsData->state, true);
                            
                            $result[] = [
                                'id' => $battery->id,
                                'mac_id' => $battery->mac_id,
                                'unique_id' => $battery->batterie_unique_id,
                                'fabriquant' => $battery->fabriquant ?? 'Inconnu',
                                'gps' => $battery->gps ?? 'Inconnu',
                                'soc' => $stateData['SOC'] ?? 0,
                                'status' => $this->getBatteryStatus($stateData['WorkStatus'] ?? '3'),
                                'latitude' => $bmsData->longitude,
                                'longitude' => $bmsData->latitude,
                                'last_update' => date('Y-m-d H:i:s', strtotime($bmsData->timestamp))
                            ];
                        }
                    }
                }
                
                return $result;
            });
            
            return response()->json($result);
        } catch (Exception $e) {
            Log::error("Erreur lors de la récupération des données des batteries : " . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des données: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * API endpoint pour récupérer les mises à jour récentes
     */
    public function getBatteriesUpdates(Request $request)
    {
        try {
            // Par défaut, récupérer les données mises à jour depuis les 30 dernières secondes
            $since = $request->query('since', now()->subSeconds(30)->toDateTimeString());
            
            // Requête optimisée pour récupérer uniquement les batteries qui ont des mises à jour récentes
            $recentlyUpdatedMacIds = DB::table('bms_data')
                ->select('mac_id')
                ->where('timestamp', '>=', $since)
                ->groupBy('mac_id')
                ->pluck('mac_id')
                ->toArray();
            
            if (empty($recentlyUpdatedMacIds)) {
                return response()->json([]);
            }
            
            // Récupérer les informations de base des batteries
            $batteries = BatteriesValide::select(['id', 'mac_id', 'batterie_unique_id', 'fabriquant', 'gps'])
                ->whereIn('mac_id', $recentlyUpdatedMacIds)
                ->get();
            
            if ($batteries->isEmpty()) {
                return response()->json([]);
            }
            
            $batteryMacIds = $batteries->pluck('mac_id')->toArray();
            
            // Récupérer les dernières données BMS pour ces batteries
            $latestBmsData = DB::select('
                SELECT b.* 
                FROM bms_data b
                INNER JOIN (
                    SELECT mac_id, MAX(timestamp) as latest_timestamp
                    FROM bms_data
                    WHERE mac_id IN (' . implode(',', array_fill(0, count($batteryMacIds), '?')) . ')
                    GROUP BY mac_id
                ) m ON b.mac_id = m.mac_id AND b.timestamp = m.latest_timestamp
            ', $batteryMacIds);
            
            // Indexer par mac_id
            $bmsDataByMacId = [];
            foreach ($latestBmsData as $data) {
                $bmsDataByMacId[$data->mac_id] = $data;
            }
            
            // Construire les données de mise à jour
            $updates = [];
            foreach ($batteries as $battery) {
                if (isset($bmsDataByMacId[$battery->mac_id])) {
                    $bmsData = $bmsDataByMacId[$battery->mac_id];
                    
                    if ($bmsData->latitude && $bmsData->longitude) {
                        $stateData = json_decode($bmsData->state, true);
                        
                        $updates[] = [
                            'id' => $battery->id,
                            'mac_id' => $battery->mac_id,
                            'unique_id' => $battery->batterie_unique_id,
                            'soc' => $stateData['SOC'] ?? 0,
                            'status' => $this->getBatteryStatus($stateData['WorkStatus'] ?? '3'),
                            'latitude' => $bmsData->latitude,
                            'longitude' => $bmsData->longitude,
                            'last_update' => date('Y-m-d H:i:s', strtotime($bmsData->timestamp))
                        ];
                    }
                }
            }
            
            // Invalider le cache si des mises à jour sont disponibles
            if (!empty($updates)) {
                Cache::forget('batteries_map_data');
            }
            
            return response()->json($updates);
        } catch (Exception $e) {
            Log::error("Erreur lors de la récupération des mises à jour : " . $e->getMessage());
            return response()->json(['error' => 'Erreur lors de la récupération des mises à jour: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Convertit le code de statut de travail en texte lisible
     */
    private function getBatteryStatus($statusCode)
    {
        $statuses = [
            '0' => 'Inactive',
            '1' => 'En charge',
            '2' => 'En décharge',
            '3' => 'En veille'
        ];
        
        return $statuses[$statusCode] ?? 'Inconnu';
    }
}