<?php

namespace App\Http\Controllers\Gps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MotosValide;
use App\Models\GpsLocation;
use App\Models\AssociationUserMoto;
use App\Models\Employe;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LocalisationController extends Controller
{
    // Clé de cache pour le suivi des états des motos
    const ZONE_STATUS_CACHE_KEY = 'moto_zone_status';
    
    public function carteDouala()
    {   
        return view('gps.douala_map');
    }

    public function apiZoneDouala()
    {
        ob_clean();
        return response()->json($this->doualaCoordinates(), 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 1990 00:00:00 GMT'
        ]);
    }

    public function apiMotosPositions()
    {
        $positions = Cache::get('motos_latest_positions', []);
        $zone = $this->doualaCoordinates();
        $zoneStatusCache = $this->getZoneStatusCache();
        $resultats = [];

        foreach ($positions as $macid => $data) {
            $moto = MotosValide::where('gps_imei', $macid)->first();
            if (!$moto) continue;

            $point = ['lat' => (float)$data['latitude'], 'lng' => (float)$data['longitude']];
            $inZone = $data['inZone'] ?? $this->pointInPolygon($point, $zone);
            $heading = $data['heading'] ?? 0;

            $association = AssociationUserMoto::where('moto_valide_id', $moto->id)->latest()->first();
            $userInfo = "Non associé";
            $driverName = null;

            if ($association && $association->validatedUser) {
                $user = $association->validatedUser;
                $driverName = "{$user->nom} {$user->prenom}";
                $userInfo = "{$driverName} - {$user->phone}";
            }

            $this->gererAlerteZone($moto, $point, $userInfo, $inZone, $zoneStatusCache);

            $isActive = true;
            $lastUpdateTime = Carbon::parse($data['server_time']);
            if ($lastUpdateTime->diffInHours(now()) > 24) {
                $isActive = false;
            }

            $resultats[] = [
                'vin' => $data['vin'],
                'macid' => $macid,
                'driverInfo' => $userInfo,
                'driverName' => $driverName,
                'latitude' => $point['lat'],
                'longitude' => $point['lng'],
                'inZone' => $inZone,
                'heading' => $heading,
                'isActive' => $isActive,
                'lastUpdate' => $data['server_time'],
                'batteryLevel' => $data['battery_level'] ?? 100,
            ];
        }

        $this->saveZoneStatusCache($zoneStatusCache);

        ob_clean();
        return response()->json($resultats, 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 1990 00:00:00 GMT'
        ]);
    }


    public function updateCacheMotosEtZones()
    {
        \Log::info("✅ debut de la fonction update cache Moto et zone");
        \Log::info('🕒 [CRON] update:moto-zones exécuté à ' . now());
        $positions = [];
        $zone = $this->doualaCoordinates();
        $zoneStatusCache = $this->getZoneStatusCache();
        $motos = MotosValide::all();

        foreach ($motos as $moto) {
            $last = GpsLocation::where('macid', $moto->gps_imei)->orderBy('server_time', 'desc')->first();
            $prev = GpsLocation::where('macid', $moto->gps_imei)->where('id', '<>', optional($last)->id)->orderBy('server_time', 'desc')->first();

            if (!$last) continue;

            $point = ['lat' => (float) $last->latitude, 'lng' => (float) $last->longitude];
            $inZone = $this->pointInPolygon($point, $zone);
            $heading = 0;

            if ($prev) {
                $heading = $this->calculateHeading($prev->latitude, $prev->longitude, $last->latitude, $last->longitude);
            }

            $userInfo = "Non associé";
            $association = AssociationUserMoto::where('moto_valide_id', $moto->id)->latest()->first();
            if ($association && $association->validatedUser) {
                $user = $association->validatedUser;
                $userInfo = "{$user->nom} {$user->prenom} - {$user->phone}";
            }

            $this->gererAlerteZone($moto, $point, $userInfo, $inZone, $zoneStatusCache);

            $positions[$moto->gps_imei] = [
                'vin' => $moto->vin,
                'macid' => $moto->gps_imei,
                'latitude' => $point['lat'],
                'longitude' => $point['lng'],
                'server_time' => $last->server_time,
                'battery_level' => $last->battery_level ?? null,
                'heading' => $heading,
                'inZone' => $inZone,
            ];
        }

        Cache::put('motos_latest_positions', $positions, now()->addMinutes(30));
        $this->saveZoneStatusCache($zoneStatusCache);

        Log::info("✅ Cache des positions motos mis à jour avec alertes zone.");
    }

    /**
     * Récupère l'état du cache pour le suivi des motos dans/hors zone
     * 
     * @return array
     */
    private function getZoneStatusCache()
    {
        // Récupérer le cache ou initialiser s'il n'existe pas
        $today = Carbon::today()->format('Y-m-d');
        $cacheKey = self::ZONE_STATUS_CACHE_KEY . ':' . $today;
        
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, [], Carbon::tomorrow()->subSecond());
        }
        
        return Cache::get($cacheKey, []);
    }

    /**
     * Sauvegarde l'état actualisé du cache
     * 
     * @param array $zoneStatusCache
     * @return void
     */
    private function saveZoneStatusCache($zoneStatusCache)
    {
        $today = Carbon::today()->format('Y-m-d');
        $cacheKey = self::ZONE_STATUS_CACHE_KEY . ':' . $today;
        
        Cache::put($cacheKey, $zoneStatusCache, Carbon::tomorrow()->subSecond());
    }

    /**
     * Gère l'envoi d'alertes en fonction de l'historique des statuts des motos
     * Modifié pour n'envoyer qu'une seule alerte par jour sauf si le statut change
     * 
     * @param MotosValide $moto
     * @param array $point
     * @param string $userInfo
     * @param bool $inZone
     * @param array &$zoneStatusCache
     * @return void
     */
    private function gererAlerteZone($moto, $point, $userInfo, $inZone, &$zoneStatusCache)
    {
        $motoId = $moto->id;
        
        // Initialiser le statut pour cette moto si absent
        $isFirstCheck = false;
        if (!isset($zoneStatusCache[$motoId])) {
            $isFirstCheck = true;
            $zoneStatusCache[$motoId] = [
                'lastInZone' => null, // On met null pour forcer la détection de changement au premier passage
                'lastAlertSent' => null,
                'lastEntryTime' => null, // Nouvel élément pour suivre quand la moto est entrée dans la zone
                'lastExitTime' => null   // Nouvel élément pour suivre quand la moto est sortie de la zone
            ];
        }
        
        $statusChanged = $zoneStatusCache[$motoId]['lastInZone'] !== $inZone;
        $now = Carbon::now();
        
        // Cas 1: Premier passage ou la moto était dans la zone et en sort maintenant
        if (($isFirstCheck && !$inZone) || ($statusChanged && !$inZone)) {
            $shouldSendAlert = true;
            
            // Vérifier si on a déjà envoyé une alerte de sortie aujourd'hui
            if (!$isFirstCheck && $zoneStatusCache[$motoId]['lastAlertSent'] !== null) {
                // Si la moto est sortie, rentrée, puis ressortie, on envoie une nouvelle alerte
                // Sinon, on vérifie qu'aucune alerte de sortie n'a été envoyée aujourd'hui
                if (!$zoneStatusCache[$motoId]['lastEntryTime']) {
                    $shouldSendAlert = false;
                    Log::info("🔕 PAS d'alerte pour la moto VIN: {$moto->vin} - Une alerte de sortie a déjà été envoyée aujourd'hui");
                }
            }
            
            if ($shouldSendAlert) {
                $this->envoyerAlerteEmployes($moto, $point, $userInfo, 'sortie');
                $zoneStatusCache[$motoId]['lastAlertSent'] = $now->toDateTimeString();
                $zoneStatusCache[$motoId]['lastExitTime'] = $now->toDateTimeString();
                Log::info("🚨 Alerte SORTIE DE ZONE envoyée pour la moto VIN: {$moto->vin}");
            }
        }
        
        // Cas 2: La moto était hors zone et y revient maintenant
        if (!$isFirstCheck && $statusChanged && $inZone) {
            $this->envoyerAlerteEmployes($moto, $point, $userInfo, 'retour');
            $zoneStatusCache[$motoId]['lastEntryTime'] = $now->toDateTimeString();
            Log::info("✅ Notification RETOUR EN ZONE envoyée pour la moto VIN: {$moto->vin}");
        }
        
        // Mettre à jour l'état actuel
        $zoneStatusCache[$motoId]['lastInZone'] = $inZone;
    }

    /**
     * Calculer la direction entre deux points GPS
     * 
     * @param float $lat1 Latitude du point de départ
     * @param float $lon1 Longitude du point de départ
     * @param float $lat2 Latitude du point d'arrivée
     * @param float $lon2 Longitude du point d'arrivée
     * @return float Direction en degrés (0-360)
     */
    private function calculateHeading($lat1, $lon1, $lat2, $lon2)
    {
        // Convertir en radians
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLon = $lon2 - $lon1;

        $y = sin($dLon) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);

        $bearing = atan2($y, $x);
        $bearing = rad2deg($bearing);
        $bearing = fmod(($bearing + 360), 360);

        return $bearing;
    }

    private function pointInPolygon($point, $polygon)
    {
        $x = $point['lng'];
        $y = $point['lat'];
        $inside = false;

        for ($i = 0, $j = count($polygon) - 1; $i < count($polygon); $j = $i++) {
            $xi = $polygon[$i]['lng'];
            $yi = $polygon[$i]['lat'];
            $xj = $polygon[$j]['lng'];
            $yj = $polygon[$j]['lat'];

            $intersect = (($yi > $y) != ($yj > $y)) &&
                         ($x < ($xj - $xi) * ($y - $yi) / (($yj - $yi) ?: 1e-10) + $xi);
            if ($intersect) $inside = !$inside;
        }

        return $inside;
    }

    /**
     * Envoie des alertes aux employés en fonction du type d'événement
     * 
     * @param MotosValide $moto
     * @param array $point
     * @param string $userInfo
     * @param string $type Type d'alerte ('sortie' ou 'retour')
     * @return void
     */
    private function envoyerAlerteEmployes($moto, $point, $userInfo, $type = 'sortie')
    {
        $employes = Employe::all();
        
        if ($type === 'sortie') {
            $subject = "ALERTE - Moto sortie de zone Douala";
            $body = "La moto VIN: {$moto->vin}\nMacID: {$moto->gps_imei}\nCoordonnées: Latitude {$point['lat']} / Longitude {$point['lng']}\nChauffeur: {$userInfo}\nSituation: ❌ HORS DE LA ZONE DOUALA";
        } else {
            $subject = "INFO - Moto revenue en zone Douala";
            $body = "La moto VIN: {$moto->vin}\nMacID: {$moto->gps_imei}\nCoordonnées: Latitude {$point['lat']} / Longitude {$point['lng']}\nChauffeur: {$userInfo}\nSituation: ✅ DE RETOUR DANS LA ZONE DOUALA";
        }

        foreach ($employes as $employe) {
            Mail::raw($body, function ($message) use ($employe, $subject) {
                $message->to($employe->email)
                        ->subject($subject)
                        ->from('patrick.bika@proxymgroup.com', 'Proxym Group');
            });

            Log::info("📧 Mail envoyé à {$employe->email} pour la moto VIN: {$moto->vin} - Type: {$type}");
        }

        Log::info("📨 Tous les mails d'alerte ont été envoyés pour la moto VIN: {$moto->vin} - Type: {$type}");
    }

    private function doualaCoordinates()
    {
        return  [
            ['lat' => 4.1164959, 'lng' => 9.6133137],
            ['lat' => 4.1086198, 'lng' => 9.6091938],
            ['lat' => 4.1055378, 'lng' => 9.6033573],
            ['lat' => 4.0969767, 'lng' => 9.6061039],
            ['lat' => 4.0914976, 'lng' => 9.6153736],
            ['lat' => 4.084991, 'lng' => 9.6174335],
            ['lat' => 4.0805392, 'lng' => 9.6208668],
            ['lat' => 4.0822514, 'lng' => 9.6400928],
            ['lat' => 4.0764297, 'lng' => 9.646616],
            ['lat' => 4.0651286, 'lng' => 9.6514225],
            ['lat' => 4.0610191, 'lng' => 9.6596623],
            ['lat' => 4.0541699, 'lng' => 9.6682454],
            ['lat' => 4.0637588, 'lng' => 9.6919347],
            ['lat' => 4.057937, 'lng' => 9.7008611],
            ['lat' => 4.0483479, 'lng' => 9.6895314],
            ['lat' => 4.0404712, 'lng' => 9.6768285],
            ['lat' => 4.0250599, 'lng' => 9.669962],
            ['lat' => 4.0141006, 'lng' => 9.6802617],
            ['lat' => 3.9805367, 'lng' => 9.7417166],
            ['lat' => 3.9538217, 'lng' => 9.7478964],
            ['lat' => 3.9658093, 'lng' => 9.782572],
            ['lat' => 3.9593018, 'lng' => 9.8004249],
            ['lat' => 3.9572467, 'lng' => 9.8244575],
            ['lat' => 3.956733, 'lng' => 9.8423102],
            ['lat' => 3.9661518, 'lng' => 9.8553565],
            ['lat' => 3.9832767, 'lng' => 9.8646263],
            ['lat' => 4.0004012, 'lng' => 9.8611931],
            ['lat' => 4.0099908, 'lng' => 9.8502067],
            ['lat' => 4.0257448, 'lng' => 9.8454002],
            ['lat' => 4.0384164, 'lng' => 9.8433402],
            ['lat' => 4.0445808, 'lng' => 9.8333839],
            ['lat' => 4.0517726, 'lng' => 9.8306373],
            ['lat' => 4.0641013, 'lng' => 9.8392203],
            ['lat' => 4.0692381, 'lng' => 9.8371604],
            ['lat' => 4.0782276, 'lng' => 9.8431685],
            ['lat' => 4.0830004, 'lng' => 9.8502066],
            ['lat' => 4.0915402, 'lng' => 9.8527816],
            ['lat' => 4.1079349, 'lng' => 9.85158],
            ['lat' => 4.1243719, 'lng' => 9.8570732],
            ['lat' => 4.1449177, 'lng' => 9.8519233],
            ['lat' => 4.1545056, 'lng' => 9.8395637],
            ['lat' => 4.1493692, 'lng' => 9.8196509],
            ['lat' => 4.1777044, 'lng' => 9.7987941],
            ['lat' => 4.1882333, 'lng' => 9.7686675],
            ['lat' => 4.1598559, 'lng' => 9.7638179],
            ['lat' => 4.1475714, 'lng' => 9.7634316],
            ['lat' => 4.1281387, 'lng' => 9.7660925],
            ['lat' => 4.1096471, 'lng' => 9.7430899],
            ['lat' => 4.0846486, 'lng' => 9.7183705],
            ['lat' => 4.0767722, 'lng' => 9.7049809],
            ['lat' => 4.0801967, 'lng' => 9.6977712],
            ['lat' => 4.1007436, 'lng' => 9.6939946],
            ['lat' => 4.1192354, 'lng' => 9.6936513],
            ['lat' => 4.1209476, 'lng' => 9.6847249],
            ['lat' => 4.1243719, 'lng' => 9.6679021],
            ['lat' => 4.1319055, 'lng' => 9.6541691],
            ['lat' => 4.1300222, 'lng' => 9.6380329],
            ['lat' => 4.1245431, 'lng' => 9.624815],
            ['lat' => 4.1164959, 'lng' => 9.6133137]
        ];
        
    }





    
}