<?php

namespace App\Http\Controllers\Associations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BatteriesValide;
use App\Models\MotosValide;
use App\Models\ValidatedUser;
use App\Models\AssociationUserMoto;
use App\Models\BatteryMotoUserAssociation;
use App\Models\BMSData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatteryMotoUserAssociationController extends Controller
{
    /**
     * Affiche la liste des associations et le formulaire
     */
    public function index()
    {
        try {
            // Récupérer les associations existantes avec les relations nécessaires en utilisant eager loading
            $associations = BatteryMotoUserAssociation::with(['association.validatedUser', 'association.motosValide', 'batterie'])
                ->orderBy('created_at', 'desc')
                ->get();
        
            // Récupérer les batteries disponibles
            $batteries = BatteriesValide::all();
        
            // Récupérer les motos associées à des utilisateurs avec eager loading
            $motos = MotosValide::whereHas('users')
                ->with('users')
                ->get();
            
            // Récupérer les utilisateurs validés
            $users = ValidatedUser::all();
            
            // Optimisation: Récupérer toutes les données BMS en une seule requête
            $macIds = $associations->pluck('batterie.mac_id')->filter()->unique();
            $bmsDataMap = [];
            
            if ($macIds->isNotEmpty()) {
                // Récupérer la dernière donnée BMS pour chaque mac_id
                $latestBmsData = DB::table('bms_data')
                    ->select('mac_id', 'state', 'timestamp')
                    ->whereIn('mac_id', $macIds)
                    ->orderBy('timestamp', 'desc')
                    ->get()
                    ->groupBy('mac_id')
                    ->map(function ($group) {
                        return $group->first();
                    });
                
                foreach ($latestBmsData as $macId => $bmsData) {
                    $bmsDataMap[$macId] = $bmsData;
                }
            }
            
            // Calculer le nombre de batteries avec niveau faible (< 20%)
            $lowBatteries = 0;
            foreach ($associations as $association) {
                $macId = $association->batterie->mac_id ?? null;
                if ($macId && isset($bmsDataMap[$macId])) {
                    $bmsData = $bmsDataMap[$macId];
                    $stateData = json_decode($bmsData->state, true);
                    $batteryLevel = $stateData['SOC'] ?? 0;
                    
                    if ($batteryLevel < 20) {
                        $lowBatteries++;
                    }
                }
            }
        
            $pageTitle = 'Associations des batteries aux motos et aux utilisateurs';
        
            return view('association.association_batterie_moto_user', compact('associations', 'batteries', 'motos', 'users', 'pageTitle', 'lowBatteries'));
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode index: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors du chargement de la page. Veuillez réessayer.');
        }
    }
    
    /**
     * Récupère les détails d'une association
     */
    public function getAssociationDetails($id)
    {
        try {
            Log::info('Récupération des détails de l\'association ID: ' . $id);
            
            $association = BatteryMotoUserAssociation::with([
                'association.validatedUser', 
                'association.motosValide', 
                'batterie'
            ])->findOrFail($id);
            
            // Construire une réponse détaillée
            $data = [
                'id' => $association->id,
                'user_unique_id' => $association->association->validatedUser->user_unique_id ?? 'Non défini',
                'user_nom' => $association->association->validatedUser->nom ?? '',
                'user_prenom' => $association->association->validatedUser->prenom ?? '',
                'moto_unique_id' => $association->association->motosValide->moto_unique_id ?? 'Non défini',
                'moto_vin' => $association->association->motosValide->vin ?? 'Non défini',
                'moto_model' => $association->association->motosValide->model ?? 'Non défini',
                'battery_unique_id' => $association->batterie->batterie_unique_id ?? 'Non défini',
                'battery_mac_id' => $association->batterie->mac_id ?? 'Non défini',
                'battery_pourcentage' => $association->batterie->pourcentage ?? 0,
                'date_association' => Carbon::parse($association->created_at)->format('d/m/Y'),
            ];
            
            Log::info('Détails récupérés avec succès pour l\'association ID: ' . $id);
            
            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des détails de l\'association: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur lors du chargement des détails de l\'association: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupère une liste de batteries disponibles pour l'association
     * Optimisé avec chargement en lot des données BMS
     */
   /**
 * Récupère une liste des batteries disponibles qui ne sont pas encore associées
 */
public function getAvailableBatteries()
{
    try {
        Log::info('Récupération des batteries disponibles non associées');
        
        // Récupérer les IDs des batteries déjà associées
        $associatedBatteryIds = BatteryMotoUserAssociation::pluck('battery_id')->toArray();
        
        // Récupérer toutes les batteries qui ne sont pas dans la liste des batteries associées
        $batteries = BatteriesValide::whereNotIn('id', $associatedBatteryIds)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Si on est en mode édition, on doit aussi inclure la batterie actuellement associée
        if (request()->has('include_battery_id')) {
            $includeBatteryId = request()->input('include_battery_id');
            $currentBattery = BatteriesValide::find($includeBatteryId);
            
            if ($currentBattery) {
                // Ajouter la batterie actuelle à la collection
                $batteries->push($currentBattery);
            }
        }
        
        // Récupérer tous les mac_ids en une seule fois
        $macIds = $batteries->pluck('mac_id')->filter()->unique();
        $bmsDataMap = [];
        
        if ($macIds->isNotEmpty()) {
            // Récupérer la dernière donnée BMS pour chaque mac_id en une seule requête
            $latestBmsData = DB::table('bms_data')
                ->select('mac_id', 'state', 'timestamp')
                ->whereIn('mac_id', $macIds)
                ->orderBy('timestamp', 'desc')
                ->get()
                ->groupBy('mac_id')
                ->map(function ($group) {
                    return $group->first();
                });
            
            foreach ($latestBmsData as $macId => $bmsData) {
                $bmsDataMap[$macId] = $bmsData;
            }
        }
        
        $data = $batteries->map(function($battery) use ($bmsDataMap) {
            // Vérifier si la batterie est en ligne à partir des données BMS
            $status = 'offline';
            $batteryLevel = 0;
            
            if ($battery->mac_id && isset($bmsDataMap[$battery->mac_id])) {
                $bmsData = $bmsDataMap[$battery->mac_id];
                $stateData = json_decode($bmsData->state, true);
                $batteryLevel = $stateData['SOC'] ?? 0;
                
                // Déterminer si la batterie est en ligne (dernière mise à jour < 5 min)
                $lastBmsTime = strtotime($bmsData->timestamp);
                $isOnline = (time() - $lastBmsTime < 300);
                $status = $isOnline ? 'online' : 'offline';
            }
            
            return [
                'id' => $battery->id,
                'batterie_unique_id' => $battery->batterie_unique_id,
                'mac_id' => $battery->mac_id,
                'pourcentage' => $batteryLevel,
                'status' => $status,
                'created_at' => Carbon::parse($battery->created_at)->format('d/m/Y'),
            ];
        });
        
        Log::info('Batteries disponibles non associées récupérées: ' . $batteries->count());
        
        return response()->json([
            'data' => $data
        ]);
    } catch (\Exception $e) {
        Log::error('Erreur lors du chargement des batteries disponibles: ' . $e->getMessage());
        return response()->json([
            'error' => 'Erreur lors du chargement des batteries disponibles: ' . $e->getMessage()
        ], 500);
    }
}
    
    /**
     * Récupère une liste de motos disponibles pour l'association
     */
    public function getAvailableMotos()
    {
        try {
            Log::info('Récupération des motos disponibles');
            
            // On récupère seulement les motos qui ont un utilisateur associé avec eager loading
            $motos = MotosValide::whereHas('users')
                ->with('users') // Charger tous les utilisateurs associés en une seule requête
                ->orderBy('created_at', 'desc')
                ->get();
            
            $data = $motos->map(function($moto) {
                return [
                    'id' => $moto->id,
                    'moto_unique_id' => $moto->moto_unique_id,
                    'vin' => $moto->vin,
                    'model' => $moto->model,
                    'user' => $moto->users->first() ? [
                        'id' => $moto->users->first()->id,
                        'nom' => $moto->users->first()->nom,
                        'prenom' => $moto->users->first()->prenom,
                        'user_unique_id' => $moto->users->first()->user_unique_id,
                    ] : null,
                    'created_at' => Carbon::parse($moto->created_at)->format('d/m/Y'),
                ];
            });
            
            Log::info('Motos disponibles récupérées: ' . $motos->count());
            
            return response()->json([
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des motos disponibles: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur lors du chargement des motos disponibles: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtenir les données BMS pour une batterie spécifique
     */
    public function getBmsData($macId)
    {
        try {
            Log::info('Récupération des données BMS pour la batterie: ' . $macId);
            
            // Récupérer la dernière donnée BMS pour cette batterie
            $bmsData = BMSData::where('mac_id', $macId)
                ->orderBy('timestamp', 'desc')
                ->first();
                
            if (!$bmsData) {
                Log::warning('Aucune donnée BMS trouvée pour la batterie: ' . $macId);
                return response()->json([
                    'error' => 'Aucune donnée BMS trouvée pour cette batterie'
                ], 404);
            }
            
            // Récupérer les données historiques pour les graphiques (30 derniers jours)
            $historicalData = BMSData::where('mac_id', $macId)
                ->orderBy('timestamp', 'desc')
                ->limit(100)
                ->get();
                
            // Formater les données pour les graphiques
            $chartData = [
                'timeLabels' => [],
                'batteryVoltage' => [],
                'remainingCapacity' => [],
                'dischargeCurrent' => [],
                'temperature' => [],
            ];
            
            foreach ($historicalData->reverse() as $data) {
                $state = json_decode($data->state, true);
                $chartData['timeLabels'][] = Carbon::parse($data->timestamp)->format('H:i d/m');
                $chartData['batteryVoltage'][] = $state['TotalVoltage'] ?? 0;
                $chartData['remainingCapacity'][] = $state['SOC'] ?? 0;
                $chartData['dischargeCurrent'][] = $state['Current'] ?? 0;
                $chartData['temperature'][] = $state['MaxTemp'] ?? 0;
            }
            
            // Récupérer la batterie correspondante
            $battery = BatteriesValide::where('mac_id', $macId)->first();
            
            // Formater l'état actuel de la batterie
            $currentState = json_decode($bmsData->state, true);
            
            // Mapper le statut de travail
            $workStatusMap = [
                '0' => 'Inactive',
                '1' => 'Charging',
                '2' => 'Discharging',
                '3' => 'Idle'
            ];
            
            $workStatus = $workStatusMap[$currentState['WorkStatus'] ?? '0'] ?? 'Unknown';
            
            // Déterminer si la batterie est en ligne (dernière mise à jour < 5 min)
            $lastBmsTime = strtotime($bmsData->timestamp);
            $isOnline = (time() - $lastBmsTime < 300);
            $batteryStatus = $isOnline ? 'Online' : 'Offline';
            
            // Construire la réponse
            $response = [
                'id' => $battery ? $battery->batterie_unique_id : $macId,
                'mac_id' => $macId,
                'status' => $batteryStatus,
                'batteryStatus' => $workStatus,
                'updatedAt' => Carbon::parse($bmsData->timestamp)->format('d/m/Y H:i:s'),
                'soc' => $currentState['SOC'] ?? 0,
                'voltage' => $currentState['TotalVoltage'] ?? 0,
                'current' => $currentState['Current'] ?? 0,
                'cycles' => $currentState['Cycles'] ?? 0,
                'type' => $battery ? $battery->type : 'Standard',
                'nominalCapacity' => $currentState['NominalCapacity'] ?? 'N/A',
                'soe' => $currentState['SOE'] ?? 'N/A',
                'ratedCurrent' => $currentState['RatedCurrent'] ?? 'N/A',
                'chargeSwitch' => ($currentState['ChrgSW'] ?? 0) ? 'Activé' : 'Désactivé',
                'dischargeSwitch' => ($currentState['DschgSW'] ?? 0) ? 'Activé' : 'Désactivé',
                'chargeCurrent' => $currentState['ChargeCurrent'] ?? 0,
                'dischargeCurrent' => $currentState['DischargeCurrent'] ?? 0,
                'temps' => $currentState['Temps'] ?? [],
                'cellVoltages' => [],
                'chartData' => $chartData,
            ];
            
            // Ajouter les tensions des cellules si disponibles
            if (isset($currentState['CellVoltages']) && is_array($currentState['CellVoltages'])) {
                foreach ($currentState['CellVoltages'] as $index => $voltage) {
                    // Déterminer le statut de la cellule
                    $status = 'normal';
                    if ($voltage < 3.0) {
                        $status = 'danger';
                    } elseif ($voltage < 3.3) {
                        $status = 'warning';
                    }
                    
                    $response['cellVoltages'][] = [
                        'number' => $index + 1,
                        'voltage' => $voltage,
                        'status' => $status
                    ];
                }
            }
            
            // Ajouter les coordonnées GPS si disponibles
            if (isset($currentState['Latitude']) && isset($currentState['Longitude'])) {
                $response['latitude'] = $currentState['Latitude'];
                $response['longitude'] = $currentState['Longitude'];
            }
            
            Log::info('Données BMS récupérées avec succès pour la batterie: ' . $macId);
            
            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des données BMS: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur lors du chargement des données BMS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * Obtenir les données BMS pour plusieurs batteries en une seule requête
 * Optimisation pour éviter de multiples requêtes individuelles
 */
public function getBulkBmsData(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'mac_ids' => 'required|array',
            'mac_ids.*' => 'string|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Paramètres invalides: ' . implode(' ', $validator->errors()->all())
            ], 422);
        }

        $macIds = $request->mac_ids;
        Log::info('Récupération des données BMS en lot pour ' . count($macIds) . ' batteries');
        
        $result = [];
        
        // Récupérer les dernières données BMS pour chaque mac_id en une seule requête
        $bmsDataCollection = DB::table('bms_data')
            ->select('mac_id', 'state', 'timestamp')
            ->whereIn('mac_id', $macIds)
            ->orderBy('timestamp', 'desc')
            ->get();
        
        // Regrouper les données par mac_id pour avoir seulement la dernière entrée pour chaque batterie
        $groupedData = $bmsDataCollection->groupBy('mac_id');
        
        foreach ($groupedData as $macId => $dataGroup) {
            $bmsData = $dataGroup->first();
            if (!$bmsData) continue;
            
            $currentState = json_decode($bmsData->state, true);
            
            // Déterminer si la batterie est en ligne (dernière mise à jour < 5 min)
            $lastBmsTime = strtotime($bmsData->timestamp);
            $isOnline = (time() - $lastBmsTime < 300);
            
            $result[$macId] = [
                'mac_id' => $macId,
                'status' => $isOnline ? 'Online' : 'Offline',
                'soc' => $currentState['SOC'] ?? 0,
                'voltage' => $currentState['TotalVoltage'] ?? 0,
                'current' => $currentState['Current'] ?? 0,
                'updatedAt' => Carbon::parse($bmsData->timestamp)->format('d/m/Y H:i:s')
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
        
    } catch (\Exception $e) {
        Log::error('Erreur lors du chargement des données BMS en lot: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du chargement des données BMS: ' . $e->getMessage()
        ], 500);
    }
}

  /**
 * Stocker une nouvelle association batterie-moto-utilisateur
 */
public function store(Request $request)
{
    try {
        Log::info('Tentative de création d\'une association');
        
        // Valider les données de la requête
        $validator = Validator::make($request->all(), [
            'battery_unique_id' => 'required|exists:batteries_valides,batterie_unique_id',
            'moto_unique_id' => 'required|exists:motos_valides,moto_unique_id',
        ], [
            'battery_unique_id.required' => 'Le champ "battery_unique_id" est requis.',
            'battery_unique_id.exists' => 'La batterie avec ce "battery_unique_id" n\'existe pas.',
            'moto_unique_id.required' => 'Le champ "moto_unique_id" est requis.',
            'moto_unique_id.exists' => 'La moto avec ce "moto_unique_id" n\'existe pas.',
        ]);

        // Si la validation échoue, retourner une réponse avec les erreurs
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation: ' . implode(' ', $validator->errors()->all())
            ], 422);
        }

        // Recherche ou échec pour la moto et la batterie
        $moto = MotosValide::where('moto_unique_id', $request->moto_unique_id)->firstOrFail();
        $batterie = BatteriesValide::where('batterie_unique_id', $request->battery_unique_id)->firstOrFail();

        Log::info('Moto trouvée : ID=' . $moto->id . ', moto_unique_id=' . $moto->moto_unique_id);
        Log::info('Batterie trouvée : ID=' . $batterie->id . ', batterie_unique_id=' . $batterie->batterie_unique_id);

        // Vérifier si l'association moto-utilisateur existe
        $motoAssociation = AssociationUserMoto::where('moto_valide_id', $moto->id)->first();

        if (!$motoAssociation) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune association utilisateur trouvée pour cette moto. Veuillez associer un utilisateur à la moto avant de procéder.'
            ], 404);
        }

        Log::info('Association moto-utilisateur trouvée : ID=' . $motoAssociation->id);
        
        // Vérifier si la batterie est déjà associée à une autre moto
       // Vérifier si la batterie est déjà associée à une autre moto
$existingBatteryAssociation = BatteryMotoUserAssociation::where('battery_id', $batterie->id)->first();
if ($existingBatteryAssociation) {
    return response()->json([
        'success' => false,
        'message' => 'Cette batterie est déjà associée à une autre moto. Veuillez d\'abord supprimer cette association.'
    ], 400);
}

        // Commencer une transaction pour garantir la cohérence des données
        DB::beginTransaction();

        // Vérifier si une association existe déjà pour cette moto dans l'association moto-utilisateur
        $existingAssociation = BatteryMotoUserAssociation::where('association_user_moto_id', $motoAssociation->id)->first();

        if ($existingAssociation) {
            // Mise à jour de l'association existante
            Log::info('Mise à jour de l\'association existante : ID=' . $existingAssociation->id);
            $existingAssociation->battery_id = $batterie->id;  // ID de la batterie
            $existingAssociation->date_association = now();  // Date d'association
            $existingAssociation->save();
            
            $message = 'Association mise à jour avec succès.';
        } else {
            // Création d'une nouvelle association
            Log::info('Création d\'une nouvelle association');
            $association = new BatteryMotoUserAssociation();
            $association->association_user_moto_id = $motoAssociation->id;  // Utilisation de l'ID de l'association moto-utilisateur
            $association->battery_id = $batterie->id;  // ID de la batterie
            $association->date_association = now();  // Date d'association
            $association->save();
            
            $message = 'Association créée avec succès.';
        }

        // Valider la transaction
        DB::commit();

        // Retourner une réponse avec succès
        return response()->json([
            'success' => true,
            'message' => $message
        ]);

    } catch (\Exception $e) {
        // Annuler la transaction en cas d'erreur
        DB::rollBack();
        Log::error('Erreur dans store : ' . $e->getMessage());
        Log::error('Trace : ' . $e->getTraceAsString());
        
        // Retourner une réponse avec l'erreur
        return response()->json([
            'success' => false,
            'message' => 'Erreur serveur : ' . $e->getMessage()
        ], 500);
    }
}



/**
 * Mettre à jour une association existante
 */
public function update(Request $request, $id)
{
    try {
        Log::info("Tentative de mise à jour de l'association ID: {$id}");
        Log::info('Données reçues: ' . json_encode($request->all()));
        
        // Valider les données
        $validator = Validator::make($request->all(), [
            'battery_unique_id' => 'required|exists:batteries_valides,batterie_unique_id',
            'moto_unique_id' => 'required|exists:motos_valides,moto_unique_id',
        ], [
            'battery_unique_id.required' => 'Vous devez sélectionner une batterie.',
            'battery_unique_id.exists' => 'La batterie sélectionnée n\'existe pas.',
            'moto_unique_id.required' => 'Vous devez sélectionner une moto.',
            'moto_unique_id.exists' => 'La moto sélectionnée n\'existe pas.',
        ]);
        
        if ($validator->fails()) {
            Log::warning('Validation échouée: ' . json_encode($validator->errors()->toArray()));
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation: ' . implode(' ', $validator->errors()->all())
            ], 422);
        }
        
        // Trouver l'association à mettre à jour
        $association = BatteryMotoUserAssociation::findOrFail($id);
        
        // Récupérer les valeurs unique_id de la requête
        $batteryUniqueId = $request->input('battery_unique_id');
        $motoUniqueId = $request->input('moto_unique_id');
        
        // Rechercher la moto et la batterie en utilisant les unique_id
        $moto = MotosValide::where('moto_unique_id', $motoUniqueId)->first();
        $batterie = BatteriesValide::where('batterie_unique_id', $batteryUniqueId)->first();
        
        if (!$moto) {
            Log::warning("Moto non trouvée: {$motoUniqueId}");
            return response()->json([
                'success' => false,
                'message' => 'Moto non trouvée dans la table des motos valides.'
            ], 404);
        }
        
        if (!$batterie) {
            Log::warning("Batterie non trouvée: {$batteryUniqueId}");
            return response()->json([
                'success' => false,
                'message' => 'Batterie non trouvée dans la table des batteries valides.'
            ], 404);
        }
        
        // Vérifier si la batterie est déjà associée à une autre moto
        $existingBatteryAssociation = BatteryMotoUserAssociation::where('battery_id', $batterie->id)
            ->where('id', '!=', $id)
            ->first();
            
        if ($existingBatteryAssociation) {
            return response()->json([
                'success' => false,
                'message' => 'Cette batterie est déjà associée à une autre moto. Veuillez d\'abord supprimer cette association.'
            ], 400);
        }
        
        // Récupérer l'association utilisateur-moto en utilisant l'ID de la moto
        $motoAssociation = AssociationUserMoto::where('moto_valide_id', $moto->id)->first();
        
        if (!$motoAssociation) {
            Log::warning("Association utilisateur-moto non trouvée pour la moto: {$moto->id}");
            return response()->json([
                'success' => false,
                'message' => 'Aucune association utilisateur-moto trouvée pour cette moto.'
            ], 404);
        }
        
        DB::beginTransaction();
        
        // Mettre à jour l'association avec les IDs (pas les unique_ids)
        $association->association_user_moto_id = $motoAssociation->id;
        $association->battery_id = $batterie->id;
        $association->updated_at = now();
        $association->save();
        
        DB::commit();
        
        Log::info("Association ID {$id} mise à jour avec succès");
        
        return response()->json([
            'success' => true,
            'message' => "L'association a été mise à jour avec succès."
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Erreur lors de la mise à jour de l'association: " . $e->getMessage());
        Log::error($e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => "Une erreur est survenue lors de la mise à jour de l'association: " . $e->getMessage()
        ], 500);
    }
}
    
    /**
     * Supprimer une association
     */
    public function destroy($id)
    {
        try {
            Log::info("Tentative de suppression de l'association ID: {$id}");
            
            $association = BatteryMotoUserAssociation::findOrFail($id);
            $association->delete();
            
            Log::info("Association ID {$id} supprimée avec succès");
            
            return response()->json([
                'success' => true,
                'message' => "L'association a été supprimée avec succès."
            ]);
            
        } catch (\Exception $e) {
            Log::error("Erreur lors de la suppression de l'association: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => "Une erreur est survenue lors de la suppression de l'association: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Récupérer les statistiques pour le tableau de bord
     * Optimisé avec chargement en lot des données BMS
     */
    public function getStats()
    {
        try {
            $totalAssociations = BatteryMotoUserAssociation::count();
            $totalBatteries = BatteriesValide::count();
            $totalUsers = ValidatedUser::count();
            
            // Récupérer toutes les associations avec les batteries en une seule requête
            $associations = BatteryMotoUserAssociation::with('batterie')->get();
            
            // Récupérer tous les mac_ids en une seule fois
            $macIds = $associations->pluck('batterie.mac_id')->filter()->unique();
            $bmsDataMap = [];
            
            if ($macIds->isNotEmpty()) {
                // Récupérer la dernière donnée BMS pour chaque mac_id en une seule requête
                $latestBmsData = DB::table('bms_data')
                    ->select('mac_id', 'state', 'timestamp')
                    ->whereIn('mac_id', $macIds)
                    ->orderBy('timestamp', 'desc')
                    ->get()
                    ->groupBy('mac_id')
                    ->map(function ($group) {
                        return $group->first();
                    });
                
                foreach ($latestBmsData as $macId => $bmsData) {
                    $bmsDataMap[$macId] = $bmsData;
                }
            }
            
            // Calculer le nombre de batteries avec niveau faible (< 20%)
            $lowBatteries = 0;
            foreach ($associations as $association) {
                $macId = $association->batterie->mac_id ?? null;
                if ($macId && isset($bmsDataMap[$macId])) {
                    $bmsData = $bmsDataMap[$macId];
                    $stateData = json_decode($bmsData->state, true);
                    $batteryLevel = $stateData['SOC'] ?? 0;
                    
                    if ($batteryLevel < 20) {
                        $lowBatteries++;
                    }
                }
            }
            
            return response()->json([
                'total_associations' => $totalAssociations,
                'total_batteries' => $totalBatteries,
                'total_users' => $totalUsers,
                'low_batteries' => $lowBatteries
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des statistiques: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur lors du chargement des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Réponse JSON standardisée
     */
    protected function jsonResponse($message, $status = 'success', $data = null, $code = 200)
    {
        return response()->json([
            'message' => $message,
            'status' => $status,
            'success' => $status === 'success',
            'data' => $data
        ], $code);
    }
}