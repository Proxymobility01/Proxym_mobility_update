<?php

namespace App\Http\Controllers\Bms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BMSData;
use App\Models\BatteriesValide;
use Illuminate\Support\Facades\Log;
use Exception;

class BMSController extends Controller
{
    public function index(Request $request)
    {
        // Récupérer l'ID de la batterie depuis la requête
        $selectedBatteryId = $request->query('battery');
        
        // Passer l'ID sélectionné à la vue
        return view('bms.index', [
            'selectedBatteryId' => $selectedBatteryId
        ]);
    }
    
    /**
     * Récupère la liste de toutes les batteries
     */
    public function getBatteries(Request $request)
    {
        try {
            $batteries = BatteriesValide::select('mac_id', 'batterie_unique_id', 'fabriquant')
                ->get()
                ->map(function($battery) {
                    // Récupérer le dernier état pour déterminer le status (online/offline)
                    $lastData = $this->getLastBMSData($battery->mac_id);
                    $updatedAt = $lastData ? date('Y-m-d H:i:s', $lastData['BMS_DateTime']) : null;
                    
                    // Considérer la batterie comme online si la dernière mise à jour date de moins de 5 minutes
                    $isOnline = $lastData && (time() - $lastData['BMS_DateTime'] < 300);
                    
                    return [
                        'id' => $battery->mac_id,
                        'uniqueId' => $battery->batterie_unique_id,
                        'manufacturer' => $battery->fabriquant,
                        'status' => $isOnline ? 'online' : 'offline',
                        'updatedAt' => $updatedAt
                    ];
                });
            
            return response()->json($batteries);
        } catch (Exception $e) {
            Log::error("Erreur lors de la récupération des batteries : " . $e->getMessage());
            return response()->json(['error' => 'Erreur de récupération des batteries'], 500);
        }
    }
    
    /**
     * Récupère les détails d'une batterie spécifique
     */
    public function getBatteryDetails(Request $request, $mac_id)
    {
        try {
            // Vérifier si la batterie existe
            $battery = BatteriesValide::where('mac_id', $mac_id)->first();
            
            if (!$battery) {
                return response()->json(['error' => 'Batterie non trouvée'], 404);
            }
            
            // Récupérer les dernières données BMS
            $bmsData = $this->getLastBMSData($mac_id);
            
            if (!$bmsData) {
                return response()->json(['error' => 'Données BMS non disponibles'], 404);
            }
            
            // Traiter les données des cellules pour l'affichage
            $cellVoltages = $this->processCellVoltages($bmsData['BetteryV_Arr']);
            
            // Calculer les données pour les graphiques (historique)
            $chartData = $this->getChartData($mac_id);
            
            // Construire la réponse avec les données formatées
            $response = [
                'id' => $mac_id,
                'status' => (time() - $bmsData['BMS_DateTime'] < 300) ? 'online' : 'offline',
                'updatedAt' => date('Y-m-d H:i:s', $bmsData['BMS_DateTime']),
                'soc' => (int)$bmsData['SOC'],
                'voltage' => (float)$bmsData['BetteryV_All'],
                'current' => 0, // À calculer à partir des données disponibles
                'power' => 0, // À calculer
                'batteryStatus' => $this->getBatteryStatus($bmsData['WorkStatus']),
                'soe' => $bmsData['SYLA'] . " Ah",
                'cycles' => $bmsData['BXHC'],
                'type' => $this->getBatteryType($bmsData['BetteryType']),
                'strings' => $bmsData['BetteryC'] . " strand",
                'nominalCapacity' => $battery->capacite . "Ah",
                'ratedCurrent' => "80A", // Information fixe, vous pourriez l'extraire du code BMS
                'chargeSwitch' => $bmsData['CHON'] == "1" ? "Open" : "Closed",
                'dischargeSwitch' => $bmsData['DHON'] == "1" ? "Open" : "Closed",
                'chargeCurrent' => $bmsData['CPowerA'] . "A",
                'dischargeCurrent' => $bmsData['DPowerA'] . "A",
                'temps' => [
                    (float)$bmsData['TC_B_1'],
                    (float)$bmsData['TC_B_2'],
                    (float)$bmsData['TC_EXT_1'],
                    (float)$bmsData['TC_EXT_2']
                ],
                'cellVoltages' => $cellVoltages,
                'maxVolt' => (float)$bmsData['CeilingVoltage'],
                'minVolt' => (float)$bmsData['MinimumVoltage'],
                'maxDiff' => (float)$bmsData['CVoltageSub'],
                'chartData' => $chartData,
                'longitude' => $bmsData['jingdu'] ?? null,
                'latitude' => $bmsData['weidu'] ?? null
            ];
            
            return response()->json($response);
        } catch (Exception $e) {
            Log::error("Erreur lors de la récupération des détails de la batterie : " . $e->getMessage());
            return response()->json(['error' => 'Erreur de récupération des détails'], 500);
        }
    }
    
    /**
     * Récupère les dernières données BMS pour une batterie
     */
    private function getLastBMSData($mac_id)
    {
        $bmsData = BMSData::where('mac_id', $mac_id)
            ->orderBy('timestamp', 'desc')
            ->first();
            
        if (!$bmsData) {
            return null;
        }
        
        // Fusionner les données state et seting
        $stateData = json_decode($bmsData->state, true);
        $setingData = json_decode($bmsData->seting, true);
        
        // Combiner les deux tableaux (setingData prend priorité en cas de clé dupliquée)
        return array_merge($stateData, $setingData);
    }
    
    /**
     * Traite les données de tension des cellules pour l'affichage
     */
    private function processCellVoltages($voltageString)
    {
        $voltages = explode('|', $voltageString);
        $result = [];
        
        foreach ($voltages as $index => $voltage) {
            // Convertir en volts (les valeurs sont en millivolts)
            $voltValue = (float)$voltage / 1000;
            
            // Déterminer le statut (normal, warning, danger)
            // On pourrait ajuster ces logiques selon vos besoins
            $status = "normal";
            if ($voltValue > 3.40) {
                $status = "warning";
            } elseif ($voltValue < 3.27) {
                $status = "danger";
            }
            
            $result[] = [
                'number' => $index + 1,
                'voltage' => $voltValue,
                'status' => $status
            ];
        }
        
        return $result;
    }
    
    /**
     * Récupère les données historiques pour les graphiques
     */
    private function getChartData($mac_id)
    {
        // Récupérer les 12 dernières heures de données (par exemple)
        $historicalData = BMSData::where('mac_id', $mac_id)
            ->where('timestamp', '>=', now()->subHours(12))
            ->orderBy('timestamp', 'asc')
            ->get();
        
        if ($historicalData->isEmpty()) {
            // Données fictives si aucune donnée historique n'est disponible
            return [
                'timeLabels' => ['00:00', '02:00', '04:00', '06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00', '22:00'],
                'batteryVoltage' => [78, 78.2, 78.5, 78.3, 78.1, 78.4, 78.6, 78.5, 78.2, 78.3, 78.5, 78.5],
                'remainingCapacity' => [100, 95, 90, 85, 75, 70, 65, 60, 58, 57, 56, 56],
                'dischargeCurrent' => [0, 0.1, 0.15, 0.2, 0.3, 0.35, 0.4, 0.42, 0.44, 0.44, 0.44, 0.44],
                'temperature' => [30, 30.2, 30.5, 30.7, 31, 31.2, 31.5, 31.7, 31.7, 31.6, 31.5, 31.7]
            ];
        }
        
        $timeLabels = [];
        $batteryVoltage = [];
        $remainingCapacity = [];
        $dischargeCurrent = [];
        $temperature = [];
        
        foreach ($historicalData as $data) {
            $stateData = json_decode($data->state, true);
            
            // Format de l'heure
            $timestamp = date('H:i', strtotime($data->timestamp));
            $timeLabels[] = $timestamp;
            
            // Données des graphiques
            $batteryVoltage[] = (float)$stateData['BetteryV_All'];
            $remainingCapacity[] = (int)$stateData['SOC'];
            $dischargeCurrent[] = (float)$stateData['DPowerA'];
            
            // Température moyenne
            $temp1 = (float)$stateData['TC_B_1'] ?? 0;
            $temp2 = (float)$stateData['TC_B_2'] ?? 0;
            $avgTemp = ($temp1 + $temp2) / 2;
            $temperature[] = round($avgTemp, 1);
        }
        
        return [
            'timeLabels' => $timeLabels,
            'batteryVoltage' => $batteryVoltage,
            'remainingCapacity' => $remainingCapacity,
            'dischargeCurrent' => $dischargeCurrent,
            'temperature' => $temperature
        ];
    }
    
    /**
     * Convertit le code de statut de travail en texte lisible
     */
    private function getBatteryStatus($statusCode)
    {
        $statuses = [
            '0' => 'Inactive',
            '1' => 'Charging',
            '2' => 'Discharging',
            '3' => 'Idle'
        ];
        
        return $statuses[$statusCode] ?? 'Unknown';
    }
    
    /**
     * Convertit le code de type de batterie en texte lisible
     */
    private function getBatteryType($typeCode)
    {
        $types = [
            '0' => 'Unknown',
            '1' => 'LFP',  // Lithium Fer Phosphate
            '2' => 'NMC',  // Lithium Nickel Manganèse Cobalt
            '3' => 'LTO'   // Lithium Titanate
        ];
        
        return $types[$typeCode] ?? 'Unknown';
    }
    
    /**
     * Route utilisée pour la mise à jour en temps réel des données
     */
    public function refreshBatteryData(Request $request, $mac_id)
    {
        try {
            return $this->getBatteryDetails($request, $mac_id);
        } catch (Exception $e) {
            Log::error("Erreur lors de la mise à jour des données : " . $e->getMessage());
            return response()->json(['error' => 'Erreur de mise à jour'], 500);
        }
    }
}