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
            // Récupérer les associations existantes avec les relations nécessaires
            $associations = BatteryMotoUserAssociation::with(['association.validatedUser', 'association.motosValide', 'batterie'])
                ->orderBy('created_at', 'desc')
                ->get();
        
            // Récupérer les batteries disponibles
            $batteries = BatteriesValide::all();
        
            // Récupérer les motos associées à des utilisateurs
            $motos = MotosValide::whereHas('users')
                ->with('users')
                ->get();
            
            // Récupérer les utilisateurs validés
            $users = ValidatedUser::all();
            
            // Calculer le nombre de batteries avec niveau faible (< 20%)
            $lowBatteries = 0;
            foreach ($associations as $association) {
                $macId = $association->batterie->mac_id ?? null;
                if ($macId) {
                    $bmsData = BMSData::where('mac_id', $macId)
                        ->orderBy('timestamp', 'desc')
                        ->first();
                    
                    if ($bmsData) {
                        $stateData = json_decode($bmsData->state, true);
                        $batteryLevel = $stateData['SOC'] ?? 0;
                        
                        if ($batteryLevel < 20) {
                            $lowBatteries++;
                        }
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
     */
    public function getAvailableBatteries()
    {
        try {
            Log::info('Récupération des batteries disponibles');
            
            // On inclut toutes les batteries, même celles déjà associées
            // pour permettre le changement d'association dans l'édition
            $batteries = BatteriesValide::orderBy('created_at', 'desc')
                ->get();
            
            $data = $batteries->map(function($battery) {
                // Vérifier si la batterie est en ligne à partir des données BMS
                $status = 'offline';
                $batteryLevel = 0;
                
                if ($battery->mac_id) {
                    $bmsData = BMSData::where('mac_id', $battery->mac_id)
                        ->orderBy('timestamp', 'desc')
                        ->first();
                    
                    if ($bmsData) {
                        $stateData = json_decode($bmsData->state, true);
                        $batteryLevel = $stateData['SOC'] ?? 0;
                        
                        // Déterminer si la batterie est en ligne (dernière mise à jour < 5 min)
                        $lastBmsTime = strtotime($bmsData->timestamp);
                        $isOnline = (time() - $lastBmsTime < 300);
                        $status = $isOnline ? 'online' : 'offline';
                    }
                }
                
                return [
                    'id' => $battery->id,
                    'batterie_unique_id' => $battery->batterie_unique_id,
                    'mac_id' => $battery->mac_id,
                    'pourcentage' => $batteryLevel, // Utiliser la valeur des données BMS
                    'status' => $status,
                    'created_at' => Carbon::parse($battery->created_at)->format('d/m/Y'),
                ];
            });
            
            Log::info('Batteries disponibles récupérées: ' . $batteries->count());
            
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
            
            // On récupère seulement les motos qui ont un utilisateur associé
            $motos = MotosValide::whereHas('users')
                ->with('users')
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
     * Stocker une nouvelle association batterie-moto-utilisateur ou mettre à jour une existante
     */
    public function store(Request $request)
    {
        try {
            Log::info('Tentative de création d\'une association');
            Log::info('Données reçues: ' . json_encode($request->all()));
            
            // Valider les données
            $validator = Validator::make($request->all(), [
                'battery_unique_id' => 'required|exists:batteries_valides,batterie_unique_id',
                'moto_unique_id' => 'required|exists:motos_valides,moto_unique_id',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation: ' . implode(' ', $validator->errors()->all())
                ], 422);
            }
    
            // Extraire les valeurs
            $batteryId = $request->battery_unique_id;
            $motoId = $request->moto_unique_id;
    
            // Rechercher la moto et la batterie
            $moto = MotosValide::where('moto_unique_id', $motoId)->first();
            $batterie = BatteriesValide::where('batterie_unique_id', $batteryId)->first();
    
            if (!$moto || !$batterie) {
                return response()->json([
                    'success' => false,
                    'message' => 'Moto ou batterie introuvable.'
                ], 404);
            }
    
            // ⚠️ Correction ici : utiliser `moto_valide_id`
            $motoAssociation = AssociationUserMoto::where('moto_valide_id', $moto->id)->first();
            if (!$motoAssociation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune association utilisateur-moto trouvée pour cette moto.'
                ], 404);
            }
    
            DB::beginTransaction();
    
            // Vérifier si une association existe déjà
            $existingAssociation = BatteryMotoUserAssociation::where('association_user_moto_id', $motoAssociation->id)->first();
    
            if ($existingAssociation) {
                // Mise à jour
                $existingAssociation->battery_id = $batterie->id;
                $existingAssociation->date_association = now();
                $existingAssociation->save();
            } else {
                // Nouvelle association
                $association = new BatteryMotoUserAssociation();
                $association->association_user_moto_id = $motoAssociation->id;
                $association->battery_id = $batterie->id;
                $association->date_association = now();
                $association->save();
            }
    
            DB::commit();
    
            return response()->json([
                'success' => true,
                'message' => 'Association créée ou mise à jour avec succès.'
            ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur dans store : ' . $e->getMessage());
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
            
            // Récupérer les nouvelles données
            $batteryId = $request->input('battery_unique_id');
            $motoId = $request->input('moto_unique_id');
            
            // Rechercher la moto
            $moto = MotosValide::where('moto_unique_id', $motoId)->first();
            if (!$moto) {
                Log::warning("Moto non trouvée: {$motoId}");
                return response()->json([
                    'success' => false,
                    'message' => 'Moto non trouvée dans la table des motos valides.'
                ], 404);
            }
            
            // Rechercher la batterie
            $batterie = BatteriesValide::where('batterie_unique_id', $batteryId)->first();
            if (!$batterie) {
                Log::warning("Batterie non trouvée: {$batteryId}");
                return response()->json([
                    'success' => false,
                    'message' => 'Batterie non trouvée dans la table des batteries valides.'
                ], 404);
            }
            
            // Récupérer l'association utilisateur-moto
            $motoAssociation = AssociationUserMoto::where('moto_id', $moto->id)->first();
            if (!$motoAssociation) {
                Log::warning("Association utilisateur-moto non trouvée pour la moto: {$motoId}");
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune association utilisateur-moto trouvée pour cette moto.'
                ], 404);
            }
            
            DB::beginTransaction();
            
            // Mettre à jour l'association
            $association->association_id = $motoAssociation->id;
            $association->batterie_id = $batterie->id;
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
     */
    public function getStats()
    {
        try {
            $totalAssociations = BatteryMotoUserAssociation::count();
            $totalBatteries = BatteriesValide::count();
            $totalUsers = ValidatedUser::count();
            
            // Calculer le nombre de batteries avec niveau faible (< 20%)
            $lowBatteries = 0;
            $associations = BatteryMotoUserAssociation::with('batterie')->get();
            
            foreach ($associations as $association) {
                $macId = $association->batterie->mac_id ?? null;
                if ($macId) {
                    $bmsData = BMSData::where('mac_id', $macId)
                        ->orderBy('timestamp', 'desc')
                        ->first();
                    
                    if ($bmsData) {
                        $stateData = json_decode($bmsData->state, true);
                        $batteryLevel = $stateData['SOC'] ?? 0;
                        
                        if ($batteryLevel < 20) {
                            $lowBatteries++;
                        }
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