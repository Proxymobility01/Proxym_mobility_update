<?php

namespace App\Http\Controllers;

use App\Models\DailyDistance;
use App\Models\GpsLocation;
use App\Models\MotosValide;
use App\Models\User;
use App\Models\AssociationUserMoto;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailyDistanceController extends Controller
{
    /**
     * Affiche la page des distances journalières avec filtres
     */
    public function index(Request $request)
    {
        Log::info('Début de la méthode index avec paramètres:', [
            'date' => $request->input('date'),
            'search' => $request->input('search'),
            'time_filter' => $request->input('time_filter')
        ]);

        // Mise à jour automatique des distances de la journée
        $updateResult = $this->updateDailyDistances();
        Log::info('Mise à jour des distances effectuée:', $updateResult);

        // Récupérer les paramètres de filtre
        $timeFilter = $request->input('time_filter', 'today');
        $startDate = null;
        $endDate = null;
        $customDate = null;
        $startTime = $request->input('start_time', '00:00');
        $endTime = $request->input('end_time', '23:59');
        
        // Déterminer les dates en fonction du filtre temporel
        switch ($timeFilter) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $customDate = $request->input('custom_date', now()->toDateString());
                $startDate = Carbon::parse($customDate)->startOfDay();
                $endDate = Carbon::parse($customDate)->endOfDay();
                break;
            case 'daterange':
                $rangeStart = $request->input('start_date', now()->subDays(7)->toDateString());
                $rangeEnd = $request->input('end_date', now()->toDateString());
                $startDate = Carbon::parse($rangeStart)->startOfDay();
                $endDate = Carbon::parse($rangeEnd)->endOfDay();
                break;
            case 'all':
                $startDate = Carbon::createFromTimestamp(0);
                $endDate = Carbon::now();
                break;
            default:
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
        }
        
        Log::info('Période déterminée:', [
            'timeFilter' => $timeFilter,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
            'customDate' => $customDate
        ]);
        
        // Affiner avec les heures spécifiques si ce n'est pas 'all'
        if ($timeFilter !== 'all') {
            $startDateWithTime = $startDate->copy()->setTimeFromTimeString($startTime);
            $endDateWithTime = $endDate->copy()->setTimeFromTimeString($endTime);
            
            $startDate = $startDateWithTime;
            $endDate = $endDateWithTime;
            
            Log::info('Période ajustée avec heures spécifiques:', [
                'startDate' => $startDate->format('Y-m-d H:i:s'),
                'endDate' => $endDate->format('Y-m-d H:i:s'),
                'startTime' => $startTime,
                'endTime' => $endTime
            ]);
        }
        
        // Récupérer la liste des chauffeurs actifs
        $associations = AssociationUserMoto::with(['validatedUser', 'motosValide'])->get();
        $drivers = collect();
        
        foreach ($associations as $association) {
            if ($association->validatedUser) {
                $drivers->push($association->validatedUser);
            }
        }
        
        // Logs sur les chauffeurs trouvés
        if ($drivers->isNotEmpty()) {
            foreach ($drivers as $index => $driver) {
                Log::info("Chauffeur #{$index} trouvé:", [
                    'id' => $driver->id,
                    'nom' => $driver->nom ?? 'N/A',
                    'prenom' => $driver->prenom ?? 'N/A',
                    'phone' => $driver->phone ?? 'N/A'
                ]);
            }
        } else {
            Log::warning("Aucun chauffeur trouvé via les associations. Essai avec la recherche directe.");
            
            // Essai avec d'autres critères
            $drivers = User::where('role', 'chauffeur')
                         ->orWhere('is_driver', true)
                         ->get();
                         
            // Si toujours vide, récupérons simplement tous les utilisateurs
            if ($drivers->isEmpty()) {
                Log::warning("Aucun chauffeur trouvé par rôle. Récupération de tous les utilisateurs.");
                $drivers = User::all();
            }
        }
        
        Log::info('Chauffeurs récupérés:', [
            'count' => $drivers->count(),
            'premier_chauffeur' => $drivers->isNotEmpty() ? [
                'id' => $drivers->first()->id,
                'nom' => $drivers->first()->nom ?? 'N/A',
                'prenom' => $drivers->first()->prenom ?? 'N/A',
                'phone' => $drivers->first()->phone ?? 'N/A'
            ] : 'Aucun'
        ]);
        
        // Forcer la création de points GPS pour tests - COMMENTEZ CETTE SECTION EN PRODUCTION
        $this->createSampleGpsPoints();
        
        // Calculer les distances à partir des données GPS pour la période
        $distances = collect();
        $totalDistanceCalculated = 0;
        
        foreach ($drivers as $driver) {
            // Trouver les motos associées à ce chauffeur
            $associatedMotos = AssociationUserMoto::where('validated_user_id', $driver->id)
                ->with('motosValide')
                ->get();
                
            $driverTotalDistance = 0;
            $lastLocation = 'N/A';
            $driverSegments = [];
            
            Log::info("Recherche de motos pour le chauffeur {$driver->prenom} {$driver->nom}", [
                'id' => $driver->id,
                'motos_trouvées' => $associatedMotos->count()
            ]);
            
            foreach ($associatedMotos as $association) {
                if (!$association->motosValide || !$association->motosValide->gps_imei) {
                    Log::warning("Moto sans GPS IMEI pour l'association ID {$association->id}");
                    continue;
                }
                
                $moto = $association->motosValide;
                $macId = $moto->gps_imei;
                
                Log::info("Traitement de la moto ID: {$moto->id}, VIN: {$moto->vin}, IMEI: {$macId}");
                
                // Récupérer les points GPS pour cette moto - on prend TOUS les points dans la période filtrée
                $gpsPoints = GpsLocation::where('macid', $macId)
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<=', $endDate)
                    ->orderBy('created_at', 'asc')
                    ->get();
                    
                Log::info("Points GPS trouvés pour la moto {$moto->vin} dans la période définie", [
                    'macId' => $macId, 
                    'points_count' => $gpsPoints->count(),
                    'période' => [
                        'start' => $startDate->format('Y-m-d H:i:s'),
                        'end' => $endDate->format('Y-m-d H:i:s')
                    ]
                ]);
                
                if ($gpsPoints->count() < 2) {
                    Log::warning("Pas assez de points GPS pour calculer une distance (minimum 2 requis)");
                    
                    // Rechercher la dernière position connue pour ce véhicule
                    $lastKnownPosition = GpsLocation::where('macid', $macId)
                        ->orderBy('created_at', 'desc')
                        ->first();
                        
                    if ($lastKnownPosition) {
                        $lastLocation = "Lat: {$lastKnownPosition->latitude}, Lng: {$lastKnownPosition->longitude}";
                        Log::info("Dernière position connue récupérée: {$lastLocation}");
                    }
                    
                    continue;
                }
                
                // Calculer la distance pour cette moto
                $motoDistance = 0;
                $segments = [];
                $hourlyDistances = [];
                
                // Préparer un tableau d'heures pour les statistiques
                for ($hour = 0; $hour < 24; $hour++) {
                    $hourLabel = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                    $hourlyDistances[$hourLabel] = 0;
                }
                
                for ($i = 1; $i < $gpsPoints->count(); $i++) {
                    $prevPoint = $gpsPoints[$i-1];
                    $currentPoint = $gpsPoints[$i];
                    
                    // Vérifier si les coordonnées sont valides (différentes de 0,0)
                    if (($prevPoint->latitude == 0 && $prevPoint->longitude == 0) || 
                        ($currentPoint->latitude == 0 && $currentPoint->longitude == 0)) {
                        continue;
                    }
                    
                    // Vérifier si les points font partie de la même journée
                    $prevTime = $prevPoint->created_at;
                    $currentTime = $currentPoint->created_at;
                    
                    // Log des points GPS utilisés
                    Log::info("Calcul de distance entre points:", [
                        'point1' => [
                            'latitude' => $prevPoint->latitude,
                            'longitude' => $prevPoint->longitude,
                            'created_at' => $prevTime
                        ],
                        'point2' => [
                            'latitude' => $currentPoint->latitude,
                            'longitude' => $currentPoint->longitude,
                            'created_at' => $currentTime
                        ]
                    ]);
                    
                    $distance = $this->calculateDistance(
                        $prevPoint->latitude, 
                        $prevPoint->longitude, 
                        $currentPoint->latitude, 
                        $currentPoint->longitude
                    );
                    
                    $segmentInfo = [
                        'from_point' => [
                            'latitude' => $prevPoint->latitude,
                            'longitude' => $prevPoint->longitude,
                            'created_at' => $prevTime
                        ],
                        'to_point' => [
                            'latitude' => $currentPoint->latitude,
                            'longitude' => $currentPoint->longitude,
                            'created_at' => $currentTime
                        ],
                        'distance_km' => $distance,
                        'included' => ($distance < 50) // true si inclus dans le calcul
                    ];
                    $segments[] = $segmentInfo;
                    
                    // Filtrer les sauts irréalistes (erreurs GPS)
                    if ($distance < 50) { // Max 50km entre points consécutifs
                        $motoDistance += $distance;
                        Log::info("Segment #{$i} valide: {$distance} km ajoutés");
                        
                        // Ajouter à la statistique horaire
                        $hour = intval($currentTime->format('H'));
                        $hourLabel = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                        $hourlyDistances[$hourLabel] += $distance;
                    } else {
                        Log::warning("Distance ignorée car supérieure à 50 km (possible erreur GPS):", [
                            'distance' => $distance,
                            'from' => [$prevPoint->latitude, $prevPoint->longitude],
                            'to' => [$currentPoint->latitude, $currentPoint->longitude],
                            'time_diff' => $currentTime->diffInSeconds($prevTime) . ' secondes'
                        ]);
                    }
                    
                    // Mettre à jour la dernière position connue
                    $lastLocation = "Lat: {$currentPoint->latitude}, Lng: {$currentPoint->longitude}";
                }
                
                // Arrondir la distance à 2 décimales
                $motoDistance = round($motoDistance, 2);
                
                Log::info("Détail des segments calculés pour la moto {$moto->vin}:", [
                    'total_segments' => count($segments),
                    'distance_totale' => $motoDistance,
                    'répartition_horaire' => array_filter($hourlyDistances), // Filtrer les heures à 0 km
                    'dernière_position' => $lastLocation
                ]);
                
                Log::info("Distance calculée pour la moto {$moto->vin}: {$motoDistance} km");
                $driverTotalDistance += $motoDistance;
                $driverSegments = array_merge($driverSegments, $segments);
            }
            
            // Arrondir la distance totale du chauffeur
            $driverTotalDistance = round($driverTotalDistance, 2);
            
            // Log détaillé des distances du chauffeur avec répartition horaire
            $driverHourlyDistances = [];
            foreach ($driverSegments as $segment) {
                if (!$segment['included']) continue;
                
                $hour = isset($segment['to_point']['created_at']) ? 
                    $segment['to_point']['created_at']->format('H:00') : 
                    Carbon::parse($segment['to_point']['created_at'])->format('H:00');
                
                if (!isset($driverHourlyDistances[$hour])) {
                    $driverHourlyDistances[$hour] = 0;
                }
                
                $driverHourlyDistances[$hour] += $segment['distance_km'];
            }
            
            // Trier par heure
            ksort($driverHourlyDistances);
            
            // Formater les distances horaires pour le log (arrondir à 2 décimales)
            $formattedHourlyDistances = [];
            foreach ($driverHourlyDistances as $hour => $distance) {
                $formattedHourlyDistances[$hour] = round($distance, 2);
            }
            
            Log::info("Distance totale calculée pour le chauffeur {$driver->prenom} {$driver->nom} pour la période:", [
                'total_km' => $driverTotalDistance,
                'date_début' => $startDate->format('Y-m-d H:i:s'),
                'date_fin' => $endDate->format('Y-m-d H:i:s'),
                'répartition_horaire' => $formattedHourlyDistances,
                'segments_valides' => count(array_filter($driverSegments, function($segment) {
                    return $segment['included'];
                }))
            ]);
            
            // Créer ou mettre à jour l'enregistrement dans la base de données
            try {
                $dateForRecord = $startDate->copy()->startOfDay()->toDateString();
                $dailyDistance = DailyDistance::updateOrCreate(
                    [
                        'user_id' => $driver->id,
                        'date' => $dateForRecord
                    ],
                    [
                        'total_distance_km' => $driverTotalDistance,
                        'last_location' => $lastLocation,
                        'last_updated' => now()
                    ]
                );
                
                // Associer manuellement le driver à l'enregistrement
                $dailyDistance->setRelation('user', $driver);
                
                // Récupérer la position actuelle du chauffeur
                $currentPosition = $this->getCurrentDriverPosition($driver->id);
                if ($currentPosition) {
                    $dailyDistance->current_latitude = $currentPosition['latitude'];
                    $dailyDistance->current_longitude = $currentPosition['longitude'];
                    $dailyDistance->current_speed = $currentPosition['speed'];
                    $dailyDistance->last_update_time = $currentPosition['last_update'];
                    
                    // Utiliser la dernière position si disponible
                    $lastLocation = "Lat: {$currentPosition['latitude']}, Lng: {$currentPosition['longitude']}";
                    $dailyDistance->last_location = $lastLocation;
                }
                
                $distances->push($dailyDistance);
                
                Log::info("Enregistrement des distances pour {$driver->prenom} {$driver->nom}:", [
                    'user_id' => $driver->id,
                    'date' => $dateForRecord,
                    'total_distance_km' => $driverTotalDistance,
                    'created_at' => $dailyDistance->created_at,
                    'updated_at' => $dailyDistance->updated_at
                ]);
                
            } catch (\Exception $e) {
                // En cas d'erreur, créer un objet virtuel et l'ajouter à la collection
                Log::error("Erreur lors de la mise à jour/création de l'enregistrement:", [
                    'error' => $e->getMessage(),
                    'user_id' => $driver->id,
                    'date' => $startDate->toDateString()
                ]);
                
                $virtualRecord = new DailyDistance();
                $virtualRecord->user_id = $driver->id;
                $virtualRecord->date = $startDate->toDateString();
                $virtualRecord->total_distance_km = $driverTotalDistance;
                $virtualRecord->last_location = $lastLocation;
                $virtualRecord->last_updated = now();
                $virtualRecord->created_at = now();
                $virtualRecord->updated_at = now();
                
                // Associer manuellement le driver à l'enregistrement
                $virtualRecord->setRelation('user', $driver);
                
                // Récupérer la position actuelle du chauffeur
                $currentPosition = $this->getCurrentDriverPosition($driver->id);
                if ($currentPosition) {
                    $virtualRecord->current_latitude = $currentPosition['latitude'];
                    $virtualRecord->current_longitude = $currentPosition['longitude'];
                    $virtualRecord->current_speed = $currentPosition['speed'];
                    $virtualRecord->last_update_time = $currentPosition['last_update'];
                    
                    // Utiliser la dernière position si disponible
                    $lastLocation = "Lat: {$currentPosition['latitude']}, Lng: {$currentPosition['longitude']}";
                    $virtualRecord->last_location = $lastLocation;
                }
                
                $distances->push($virtualRecord);
            }
            
            $totalDistanceCalculated += $driverTotalDistance;
        }
        
        // Trier par distance décroissante
        $distances = $distances->sortByDesc('total_distance_km')->values();
        
        Log::info('Distances calculées pour tous les chauffeurs:', [
            'count' => $distances->count(),
            'total_distance' => $totalDistanceCalculated,
            'distances_par_chauffeur' => $distances->map(function($d) {
                return [
                    'chauffeur' => $d->user->prenom . ' ' . $d->user->nom,
                    'user_id' => $d->user_id,
                    'distance' => $d->total_distance_km . ' km',
                    'date' => $d->date
                ];
            })
        ]);
        
        // Calculer les statistiques
        $stats = [
            'total_distance' => $distances->sum('total_distance_km'),
            'total_drivers' => $drivers->count(),
            'avg_distance' => $drivers->count() > 0 ? 
                $distances->sum('total_distance_km') / $drivers->count() : 0,
            'max_distance' => $distances->max('total_distance_km') ?? 0,
            'active_drivers' => $distances->where('total_distance_km', '>', 0)->count()
        ];
        
        Log::info('Statistiques calculées:', $stats);
        
        // Date format pour l'affichage
        $displayDate = $this->getDisplayDateText($timeFilter, $startDate, $endDate, $customDate);
        
        return view('distances.index', compact(
            'distances', 
            'stats', 
            'timeFilter', 
            'displayDate', 
            'drivers', 
            'customDate',
            'startTime',
            'endTime'
        ));
    }
    
    /**
     * Récupère la position actuelle d'un chauffeur
     */
    public function getCurrentDriverPosition($driverId)
    {
        // Trouver toutes les motos associées à ce chauffeur
        $associations = AssociationUserMoto::where('validated_user_id', $driverId)
            ->with('motosValide')
            ->get();
        
        foreach ($associations as $association) {
            if (!$association->motosValide || !$association->motosValide->gps_imei) {
                continue;
            }
            
            $moto = $association->motosValide;
            $macId = $moto->gps_imei;
            
            // Obtenir la dernière position
            $lastLocation = GpsLocation::where('macid', $macId)
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($lastLocation && $lastLocation->latitude != 0 && $lastLocation->longitude != 0) {
                // Déterminer si la moto est active (dernières 24 heures)
                $isActive = true;
                $lastUpdateTime = $lastLocation->created_at;
                
                if ($lastUpdateTime->diffInHours(now()) > 24) {
                    $isActive = false;
                }
                
                return [
                    'moto_vin' => $moto->vin,
                    'gps_imei' => $macId,
                    'latitude' => $lastLocation->latitude,
                    'longitude' => $lastLocation->longitude,
                    'speed' => $lastLocation->speed ?? 0,
                    'is_active' => $isActive,
                    'last_update' => $lastLocation->created_at->format('Y-m-d H:i:s'),
                    'battery_level' => $lastLocation->battery_level ?? null,
                    'created_at' => $lastLocation->created_at ?? null,
                    'updated_at' => $lastLocation->updated_at ?? null
                ];
            }
        }
        
        return null;
    }

    /**
     * Méthode pour créer des points GPS de test
     * UTILISÉE UNIQUEMENT POUR LES TESTS
     */
    private function createSampleGpsPoints()
    {
        // Vérifier si des points existent déjà
        $existingPoints = GpsLocation::count();
        
        if ($existingPoints > 0) {
            Log::info("Points GPS existants trouvés: {$existingPoints} - Pas besoin d'en créer");
            return;
        }
        
        Log::info("Création de points GPS de test...");
        
        // Récupérer tous les IMEI des motos
        $motos = MotosValide::whereNotNull('gps_imei')->get();
        
        foreach ($motos as $moto) {
            $macId = $moto->gps_imei;
            
            // Créer 24 points par moto sur la journée en cours (un par heure)
            $baseTime = Carbon::today();
            
            // Coordonnées de base (Centre de Douala)
            $baseLat = 4.05;
            $baseLng = 9.74;
            
            for ($hour = 0; $hour < 24; $hour++) {
                $point = new GpsLocation();
                $point->macid = $macId;
                $point->latitude = $baseLat + (mt_rand(-100, 100) / 10000); // Petite variation
                $point->longitude = $baseLng + (mt_rand(-100, 100) / 10000); // Petite variation
                $point->speed = mt_rand(0, 30);
                $point->status = "011000100";
                $point->device_id = "test-device-" . mt_rand(1000, 9999);
                $point->update_time = $baseTime->copy()->addHours($hour)->timestamp * 1000;
                $point->server_time = $baseTime->copy()->addHours($hour)->format('Y-m-d H:i:s');
                $point->created_at = $baseTime->copy()->addHours($hour);
                $point->updated_at = $baseTime->copy()->addHours($hour);
                
                try {
                    $point->save();
                    Log::info("Point GPS créé pour la moto {$moto->vin}", [
                        'macId' => $macId,
                        'latitude' => $point->latitude,
                        'longitude' => $point->longitude,
                        'created_at' => $point->created_at,
                        'heure' => $point->created_at->format('H:i')
                    ]);
                } catch (\Exception $e) {
                    Log::error("Erreur lors de la création d'un point GPS: " . $e->getMessage());
                }
            }
        }
        
        Log::info("Points GPS de test créés avec succès");
    }

    /**
     * API endpoint pour filtrer les distances via AJAX
     */
    public function filter(Request $request)
    {
        Log::info('Début du filtrage des distances avec paramètres:', [
            'time_filter' => $request->input('time_filter'),
            'search' => $request->input('search'),
            'driver' => $request->input('driver'),
            'distance_filter' => $request->input('distance_filter'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time')
        ]);
        
        // Récupérer les paramètres de filtre
        $timeFilter = $request->input('time_filter', 'today');
        $startDate = null;
        $endDate = null;
        $customDate = null;
        $startTime = $request->input('start_time', '00:00');
        $endTime = $request->input('end_time', '23:59');
        
        // Déterminer les dates en fonction du filtre temporel
        switch ($timeFilter) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $customDate = $request->input('custom_date', now()->toDateString());
                $startDate = Carbon::parse($customDate)->startOfDay();
                $endDate = Carbon::parse($customDate)->endOfDay();
                break;
            case 'daterange':
                $rangeStart = $request->input('start_date', now()->subDays(7)->toDateString());
                $rangeEnd = $request->input('end_date', now()->toDateString());
                $startDate = Carbon::parse($rangeStart)->startOfDay();
                $endDate = Carbon::parse($rangeEnd)->endOfDay();
                break;
            case 'all':
                $startDate = Carbon::createFromTimestamp(0);
                $endDate = Carbon::now();
                break;
            default:
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
        }
        
        Log::info('Période de filtrage déterminée:', [
            'timeFilter' => $timeFilter,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s')
        ]);
        
        // Affiner avec les heures spécifiques si ce n'est pas 'all'
        if ($timeFilter !== 'all') {
            $startDateWithTime = $startDate->copy()->setTimeFromTimeString($startTime);
            $endDateWithTime = $endDate->copy()->setTimeFromTimeString($endTime);
            
            $startDate = $startDateWithTime;
            $endDate = $endDateWithTime;
            
            Log::info('Période ajustée avec heures spécifiques:', [
                'startDate' => $startDate->format('Y-m-d H:i:s'),
                'endDate' => $endDate->format('Y-m-d H:i:s'),
                'startTime' => $startTime,
                'endTime' => $endTime
            ]);
        }
        
        // Récupérer la liste des chauffeurs
        $associations = AssociationUserMoto::with(['validatedUser', 'motosValide'])->get();
        $drivers = collect();
        
        foreach ($associations as $association) {
            if ($association->validatedUser) {
                $drivers->push($association->validatedUser);
            }
        }
        
        if ($drivers->isEmpty()) {
            $drivers = User::where('role', 'chauffeur')
                         ->orWhere('is_driver', true)
                         ->get();
                         
            if ($drivers->isEmpty()) {
                $drivers = User::all();
            }
        }
        
        // Filtrer les chauffeurs si un ID spécifique est fourni
        $driverId = $request->input('driver', 'all');
        if ($driverId !== 'all') {
            Log::info('Application du filtre de chauffeur:', ['driverId' => $driverId]);
            $drivers = $drivers->where('id', $driverId);
        }
        
        // Recherche sur les chauffeurs
        $searchTerm = $request->input('search', '');
        if (!empty($searchTerm)) {
            Log::info('Application du filtre de recherche sur les chauffeurs:', ['searchTerm' => $searchTerm]);
            
            $drivers = $drivers->filter(function($driver) use ($searchTerm) {
                $searchTerm = strtolower($searchTerm);
                return  
                    stripos($driver->id, $searchTerm) !== false || 
                    stripos($driver->nom ?? '', $searchTerm) !== false || 
                    stripos($driver->prenom ?? '', $searchTerm) !== false || 
                    stripos($driver->phone ?? '', $searchTerm) !== false;
            });
        }
        
        Log::info('Chauffeurs filtrés:', ['count' => $drivers->count()]);
        
        // Vérifier si on doit filtrer par created_at ou updated_at
        $createdAtStart = $request->input('created_at_start');
        $createdAtEnd = $request->input('created_at_end');
        $updatedAtStart = $request->input('updated_at_start');
        $updatedAtEnd = $request->input('updated_at_end');
        
        // Formater les données pour l'API
        $formattedDistances = [];
        $totalDistanceCalculated = 0;
        
        foreach ($drivers as $driver) {
            // Trouver les motos associées à ce chauffeur
            $associatedMotos = AssociationUserMoto::where('validated_user_id', $driver->id)
                ->with('motosValide')
                ->get();
                
            $driverTotalDistance = 0;
            $lastLocation = 'N/A';
            $driverHourlyDistances = [];
            
            // Initialiser le tableau des distances horaires
            for ($hour = 0; $hour < 24; $hour++) {
                $hourLabel = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                $driverHourlyDistances[$hourLabel] = 0;
            }
            
            foreach ($associatedMotos as $association) {
                if (!$association->motosValide || !$association->motosValide->gps_imei) {
                    continue;
                }
                
                $moto = $association->motosValide;
                $macId = $moto->gps_imei;
                
                // Construire la requête de base pour les points GPS
                $query = GpsLocation::where('macid', $macId)
                    ->where('created_at', '>=', $startDate)
                    ->where('created_at', '<=', $endDate);
                
                // Récupérer tous les points GPS dans la période filtrée
                $gpsPoints = $query->orderBy('created_at', 'asc')->get();
                    
                Log::info("Points GPS trouvés pour la moto {$moto->vin} (filtre API):", [
                    'macId' => $macId, 
                    'points_count' => $gpsPoints->count(),
                    'période' => [
                        'start' => $startDate->format('Y-m-d H:i:s'),
                        'end' => $endDate->format('Y-m-d H:i:s')
                    ]
                ]);
                
                if ($gpsPoints->count() < 2) {
                    continue;
                }
                
                // Calculer la distance pour cette moto
                $motoDistance = 0;
                $segments = [];
                
                for ($i = 1; $i < $gpsPoints->count(); $i++) {
                    $prevPoint = $gpsPoints[$i-1];
                    $currentPoint = $gpsPoints[$i];
                    
                    // Vérifier si les coordonnées sont valides (différentes de 0,0)
                    if (($prevPoint->latitude == 0 && $prevPoint->longitude == 0) || 
                        ($currentPoint->latitude == 0 && $currentPoint->longitude == 0)) {
                        continue;
                    }
                    
                    $distance = $this->calculateDistance(
                        $prevPoint->latitude, 
                        $prevPoint->longitude, 
                        $currentPoint->latitude, 
                        $currentPoint->longitude
                    );
                    
                    $segmentInfo = [
                        'from_point' => [
                            'latitude' => $prevPoint->latitude,
                            'longitude' => $prevPoint->longitude,
                            'created_at' => $prevPoint->created_at->format('Y-m-d H:i:s')
                        ],
                        'to_point' => [
                            'latitude' => $currentPoint->latitude,
                            'longitude' => $currentPoint->longitude,
                            'created_at' => $currentPoint->created_at->format('Y-m-d H:i:s')
                        ],
                        'distance_km' => $distance,
                        'included' => ($distance < 50) // true si inclus dans le calcul
                    ];
                    $segments[] = $segmentInfo;
                    
                    // Filtrer les sauts irréalistes (erreurs GPS)
                    if ($distance < 50) { // Max 50km entre points consécutifs
                        $motoDistance += $distance;
                        
                        // Ajouter à la statistique horaire
                        $hour = $currentPoint->created_at->format('H');
                        $hourLabel = $hour . ':00';
                        if (!isset($driverHourlyDistances[$hourLabel])) {
                            $driverHourlyDistances[$hourLabel] = 0;
                        }
                        $driverHourlyDistances[$hourLabel] += $distance;
                    }
                    
                    // Sauvegarder la dernière position connue
                    $lastLocation = "Lat: {$currentPoint->latitude}, Lng: {$currentPoint->longitude}";
                }
                
                // Arrondir la distance à 2 décimales
                $motoDistance = round($motoDistance, 2);
                
                Log::info("Distance calculée pour la moto {$moto->vin} (filtre API): {$motoDistance} km");
                $driverTotalDistance += $motoDistance;
            }
            
            // Arrondir la distance totale du chauffeur
            $driverTotalDistance = round($driverTotalDistance, 2);
            
            // Formater les distances horaires (arrondir à 2 décimales)
            $formattedHourlyDistances = [];
            foreach ($driverHourlyDistances as $hour => $distance) {
                if ($distance > 0) {
                    $formattedHourlyDistances[$hour] = round($distance, 2);
                }
            }
            
            // Trier par heure
            ksort($formattedHourlyDistances);
            
            Log::info("Distance totale filtrée pour le chauffeur {$driver->prenom} {$driver->nom}:", [
                'total_km' => $driverTotalDistance,
                'répartition_horaire' => $formattedHourlyDistances
            ]);
            
            // Récupérer la position actuelle du chauffeur
            $currentPosition = $this->getCurrentDriverPosition($driver->id);
            if ($currentPosition) {
                $lastLocation = "Lat: {$currentPosition['latitude']}, Lng: {$currentPosition['longitude']}";
            }
            
            // Ajouter à la liste formatée
            $formattedDistances[] = [
                'id' => $driver->id,
                'name' => ($driver->prenom ?? '') . ' ' . ($driver->nom ?? 'N/A'),
                'phone' => $driver->phone ?? 'N/A',
                'distance_km' => number_format($driverTotalDistance, 2),
                'last_location' => $lastLocation,
                'status' => $this->getStatusLabel($driverTotalDistance),
                'hourly_distribution' => $formattedHourlyDistances,
                'created_at' => now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s')
            ];
            
            $totalDistanceCalculated += $driverTotalDistance;
        }
        
        // Appliquer le filtre de distance
        $distanceFilter = $request->input('distance_filter', 'all');
        if ($distanceFilter !== 'all') {
            Log::info('Application du filtre de distance:', ['distanceFilter' => $distanceFilter]);
            
            $formattedDistances = array_filter($formattedDistances, function($item) use ($distanceFilter) {
                $distance = floatval(str_replace(',', '', $item['distance_km']));
                
                switch ($distanceFilter) {
                    case '0-10':
                        return $distance >= 0 && $distance <= 10;
                    case '10-50':
                        return $distance > 10 && $distance <= 50;
                    case '50-100':
                        return $distance > 50 && $distance <= 100;
                    case '100+':
                        return $distance > 100;
                    default:
                        return true;
                }
            });
            
            // Rétablir les indices numériques
            $formattedDistances = array_values($formattedDistances);
        }
        
        // Trier par distance décroissante
        usort($formattedDistances, function($a, $b) {
            return floatval(str_replace(',', '', $b['distance_km'])) <=> floatval(str_replace(',', '', $a['distance_km']));
        });
        
        Log::info('Distances filtrées finales (API):', [
            'count' => count($formattedDistances),
            'total_distance' => $totalDistanceCalculated,
            'premier_chauffeur' => !empty($formattedDistances) ? $formattedDistances[0] : 'Aucun'
        ]);
        
        // Calculer les statistiques
        $filteredTotalDistance = array_reduce($formattedDistances, function($carry, $item) {
            return $carry + floatval(str_replace(',', '', $item['distance_km']));
        }, 0);
        
        $stats = [
            'total_distance' => number_format($filteredTotalDistance, 2),
            'total_drivers' => count($formattedDistances),
            'date_label' => $this->getDisplayDateText($timeFilter, $startDate, $endDate, $customDate),
            'active_drivers' => count(array_filter($formattedDistances, function($item) {
                return floatval(str_replace(',', '', $item['distance_km'])) > 0;
            }))
        ];
        
        return response()->json([
            'distances' => $formattedDistances,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Calculer les distances quotidiennes pour tous les chauffeurs pour aujourd'hui ou une période spécifiée
     */
    public function calculateDailyDistances(Request $request)
    {
        Log::info('Début du calcul des distances journalières avec paramètres:', [
            'time_filter' => $request->input('time_filter'),
            'custom_date' => $request->input('custom_date'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time')
        ]);

        // Récupérer les paramètres de filtre
        $timeFilter = $request->input('time_filter', 'today');
        $startDate = null;
        $endDate = null;
        $customDate = null;
        $startTime = $request->input('start_time', '00:00');
        $endTime = $request->input('end_time', '23:59');
        
        // Déterminer les dates en fonction du filtre temporel
        switch ($timeFilter) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $customDate = $request->input('custom_date', now()->toDateString());
                $startDate = Carbon::parse($customDate)->startOfDay();
                $endDate = Carbon::parse($customDate)->endOfDay();
                break;
            case 'daterange':
                $rangeStart = $request->input('start_date', now()->subDays(7)->toDateString());
                $rangeEnd = $request->input('end_date', now()->toDateString());
                $startDate = Carbon::parse($rangeStart)->startOfDay();
                $endDate = Carbon::parse($rangeEnd)->endOfDay();
                break;
            case 'all':
                $startDate = Carbon::createFromTimestamp(0);
                $endDate = Carbon::now();
                break;
            default:
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
        }
        
        Log::info('Période de calcul déterminée:', [
            'timeFilter' => $timeFilter,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s')
        ]);
        
        // Affiner avec les heures spécifiques si ce n'est pas 'all'
        if ($timeFilter !== 'all') {
            $startDateWithTime = $startDate->copy()->setTimeFromTimeString($startTime);
            $endDateWithTime = $endDate->copy()->setTimeFromTimeString($endTime);
            
            $startDate = $startDateWithTime;
            $endDate = $endDateWithTime;
            
            Log::info('Période ajustée avec heures spécifiques:', [
                'startDate' => $startDate->format('Y-m-d H:i:s'),
                'endDate' => $endDate->format('Y-m-d H:i:s')
            ]);
        }

        // Pour chaque date dans la plage
        $currentDate = $startDate->copy()->startOfDay();
        $endDateDay = $endDate->copy()->startOfDay();
        
        $results = [];
        $totalDistanceCalculated = 0;
        $processedCount = 0;

        while ($currentDate->lte($endDateDay)) {
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();
            
            if ($currentDate->eq($startDate->copy()->startOfDay())) {
                $dayStart = $startDate->copy();
            }
            
            if ($currentDate->eq($endDate->copy()->startOfDay())) {
                $dayEnd = $endDate->copy();
            }
            
            $dateStr = $currentDate->toDateString();
            $results[$dateStr] = ['processed' => 0, 'totalDistance' => 0, 'details' => [], 'hourlyStats' => []];
            
            // Initialiser les statistiques horaires pour ce jour
            for ($hour = 0; $hour < 24; $hour++) {
                $hourLabel = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                $results[$dateStr]['hourlyStats'][$hourLabel] = 0;
            }
            
            Log::info("Traitement pour la journée {$dateStr}:", [
                'dayStart' => $dayStart->format('Y-m-d H:i:s'),
                'dayEnd' => $dayEnd->format('Y-m-d H:i:s')
            ]);
            
            // Récupérer tous les chauffeurs actifs et leurs motos
            $associations = AssociationUserMoto::with(['validatedUser', 'motosValide'])->get();
            
            foreach ($associations as $association) {
                if (!$association->validatedUser || !$association->motosValide || !$association->motosValide->gps_imei) {
                    Log::warning("Association invalide ou moto sans GPS IMEI, ignorée");
                    continue;
                }
                
                $user = $association->validatedUser;
                $moto = $association->motosValide;
                $macId = $moto->gps_imei;
                
                Log::info("Traitement de la moto pour le chauffeur:", [
                    'moto_id' => $moto->id,
                    'vin' => $moto->vin ?? 'N/A',
                    'gps_imei' => $macId,
                    'user_id' => $user->id,
                    'nom' => $user->nom ?? 'N/A',
                    'prenom' => $user->prenom ?? 'N/A'
                ]);

                // Récupérer les points GPS pour ce véhicule dans la journée en cours
                $gpsPoints = GpsLocation::where('macid', $macId)
                    ->where('created_at', '>=', $dayStart)
                    ->where('created_at', '<=', $dayEnd)
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                Log::info("Points GPS récupérés (calcul journalier):", [
                    'count' => $gpsPoints->count(),
                    'date' => $dateStr,
                    'macId' => $macId
                ]);

                if ($gpsPoints->count() < 2) {
                    Log::warning("Pas assez de points GPS pour calculer une distance (minimum 2 requis)");
                    continue;
                }

                // Calculer la distance totale
                $totalDistance = 0;
                $lastLocation = null;
                $distanceDetails = [];
                $hourlyDistances = [];
                
                // Initialiser les distances horaires pour cette moto
                for ($hour = 0; $hour < 24; $hour++) {
                    $hourLabel = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                    $hourlyDistances[$hourLabel] = 0;
                }
                
                for ($i = 1; $i < $gpsPoints->count(); $i++) {
                    $prevPoint = $gpsPoints[$i-1];
                    $currentPoint = $gpsPoints[$i];
                    
                    // Vérifier si les coordonnées sont valides
                    if (($prevPoint->latitude == 0 && $prevPoint->longitude == 0) || 
                        ($currentPoint->latitude == 0 && $currentPoint->longitude == 0)) {
                        continue;
                    }
                    
                    // Log des points utilisés
                    Log::info("Calcul de segment de distance entre points:", [
                        'segment' => $i,
                        'point1' => [
                            'latitude' => $prevPoint->latitude,
                            'longitude' => $prevPoint->longitude,
                            'created_at' => $prevPoint->created_at->format('Y-m-d H:i:s')
                        ],
                        'point2' => [
                            'latitude' => $currentPoint->latitude,
                            'longitude' => $currentPoint->longitude,
                            'created_at' => $currentPoint->created_at->format('Y-m-d H:i:s')
                        ]
                    ]);
                    
                    $distance = $this->calculateDistance(
                        $prevPoint->latitude, 
                        $prevPoint->longitude, 
                        $currentPoint->latitude, 
                        $currentPoint->longitude
                    );
                    
                    // Enregistrer les détails de chaque segment
                    $pointInfo = [
                        'from' => [
                            'lat' => $prevPoint->latitude,
                            'lng' => $prevPoint->longitude,
                            'created_at' => $prevPoint->created_at->format('Y-m-d H:i:s')
                        ],
                        'to' => [
                            'lat' => $currentPoint->latitude,
                            'lng' => $currentPoint->longitude,
                            'created_at' => $currentPoint->created_at->format('Y-m-d H:i:s')
                        ],
                        'distance_km' => $distance,
                        'included' => ($distance < 50) // true si inclus dans le calcul
                    ];
                    $distanceDetails[] = $pointInfo;
                    
                    // Filtrer les sauts irréalistes (erreurs GPS)
                    if ($distance < 50) { // Max 50km entre points consécutifs
                        $totalDistance += $distance;
                        Log::info("Segment de distance valide: {$distance} km ajoutés au total");
                        
                        // Ajouter à la statistique horaire
                        $hour = $currentPoint->created_at->format('H');
                        $hourLabel = $hour . ':00';
                        $hourlyDistances[$hourLabel] += $distance;
                        $results[$dateStr]['hourlyStats'][$hourLabel] += $distance;
                    } else {
                        Log::warning("Distance ignorée car supérieure à 50 km (possible erreur GPS):", [
                            'distance' => $distance,
                            'from' => [$prevPoint->latitude, $prevPoint->longitude],
                            'to' => [$currentPoint->latitude, $currentPoint->longitude],
                            'time_diff' => $currentPoint->created_at->diffInSeconds($prevPoint->created_at) . ' secondes'
                        ]);
                    }
                    
                    $lastLocation = "Lat: {$currentPoint->latitude}, Lng: {$currentPoint->longitude}";
                }
                
                // Arrondir la distance à 2 décimales
                $totalDistance = round($totalDistance, 2);
                
                // Formater les distances horaires (arrondir à 2 décimales et ne garder que les heures > 0)
                $formattedHourlyDistances = [];
                foreach ($hourlyDistances as $hour => $distance) {
                    if ($distance > 0) {
                        $formattedHourlyDistances[$hour] = round($distance, 2);
                    }
                }
                
                // Trier par heure
                ksort($formattedHourlyDistances);
                
                Log::info("Calcul de distance terminé pour le jour {$dateStr}:", [
                    'chauffeur' => $user->prenom . ' ' . $user->nom,
                    'moto' => $moto->vin,
                    'totalDistance' => $totalDistance,
                    'segments' => count($distanceDetails),
                    'lastLocation' => $lastLocation,
                    'répartition_horaire' => $formattedHourlyDistances
                ]);

                // Enregistrer ou mettre à jour dans la base de données
                try {
                    $dailyDistance = DailyDistance::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'date' => $dateStr
                        ],
                        [
                            'total_distance_km' => $totalDistance,
                            'last_location' => $lastLocation
                        ]
                    );
                    
                    Log::info("Enregistrement de distance créé/mis à jour pour {$user->prenom} {$user->nom}:", [
                        'date' => $dateStr,
                        'distance' => $totalDistance,
                        'created_at' => $dailyDistance->created_at,
                        'updated_at' => $dailyDistance->updated_at
                    ]);
                } catch (\Exception $e) {
                    Log::error("Erreur lors de l'enregistrement des distances:", [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id,
                        'date' => $dateStr
                    ]);
                }

                // Ajouter aux résultats
                $results[$dateStr]['processed']++;
                $results[$dateStr]['totalDistance'] += $totalDistance;
                $results[$dateStr]['details'][] = [
                    'chauffeur' => $user->prenom . ' ' . $user->nom,
                    'user_id' => $user->id,
                    'moto' => $moto->vin,
                    'distance' => $totalDistance,
                    'segments' => count($distanceDetails),
                    'last_location' => $lastLocation,
                    'hourly_distribution' => $formattedHourlyDistances
                ];
                
                $processedCount++;
                $totalDistanceCalculated += $totalDistance;
            }
            
            // Arrondir les valeurs des statistiques horaires
            foreach ($results[$dateStr]['hourlyStats'] as $hour => $distance) {
                $results[$dateStr]['hourlyStats'][$hour] = round($distance, 2);
            }
            
            // Ne garder que les heures avec des distances > 0
            $results[$dateStr]['hourlyStats'] = array_filter($results[$dateStr]['hourlyStats'], function($distance) {
                return $distance > 0;
            });
            
            $currentDate->addDay();
        }
        
        Log::info('Calcul terminé avec détails:', [
            'processedCount' => $processedCount,
            'totalDistanceCalculated' => $totalDistanceCalculated,
            'dateRange' => [
                'start' => $startDate->format('Y-m-d H:i:s'),
                'end' => $endDate->format('Y-m-d H:i:s')
            ],
            'jours_traités' => count($results)
        ]);

        return response()->json([
            'success' => true,
            'message' => "Traitement terminé pour {$processedCount} chauffeurs sur la période sélectionnée",
            'totalDistance' => number_format($totalDistanceCalculated, 2) . " KM",
            'details' => $results,
            'dateRange' => [
                'start' => $startDate->format('Y-m-d H:i:s'),
                'end' => $endDate->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Obtenir le libellé de statut basé sur la distance
     */
    private function getStatusLabel($distance)
    {
        Log::debug('Détermination du statut pour la distance:', ['distance' => $distance]);
        
        $status = '';
        if ($distance > 50) {
            $status = 'Actif';
        } elseif ($distance > 10) {
            $status = 'Modéré';
        } else {
            $status = 'Faible activité';
        }
        
        Log::debug('Statut déterminé:', ['distance' => $distance, 'status' => $status]);
        
        return $status;
    }
    
    /**
     * Obtenir le texte d'affichage pour le filtre de date
     */
    private function getDisplayDateText($timeFilter, $startDate, $endDate, $customDate)
    {
        Log::debug('Formatage du texte de date pour affichage:', [
            'timeFilter' => $timeFilter,
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s'),
            'customDate' => $customDate
        ]);
        
        $displayText = '';
        switch ($timeFilter) {
            case 'today':
                $displayText = "Aujourd'hui";
                break;
            case 'week':
                $displayText = "Cette semaine";
                break;
            case 'month':
                $displayText = "Ce mois";
                break;
            case 'year':
                $displayText = "Cette année";
                break;
            case 'custom':
                if ($customDate) {
                    $displayText = Carbon::parse($customDate)->format('d/m/Y');
                } else {
                    $displayText = Carbon::now()->format('d/m/Y');
                }
                break;
            case 'daterange':
                $displayText = $startDate->format('d/m/Y') . " - " . $endDate->format('d/m/Y');
                break;
            case 'all':
                $displayText = "Tout l'historique";
                break;
            default:
                $displayText = "Aujourd'hui";
                break;
        }
        
        Log::debug('Texte formaté:', ['displayText' => $displayText]);
        
        return $displayText;
    }
    
    /**
     * Calculer la distance entre deux coordonnées GPS en utilisant la formule de Haversine
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        Log::debug('Calcul de distance entre points GPS:', [
            'point1' => [$lat1, $lon1],
            'point2' => [$lat2, $lon2]
        ]);
        
        $earthRadius = 6371; // Rayon de la Terre en kilomètres
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        Log::debug('Distance calculée:', ['distance_km' => $distance]);
        
        return $distance;
    }
    
    /**
     * Mettre à jour les distances journalières pour tous les chauffeurs
     * Cette fonction est appelée par index et peut aussi être utilisée dans un cron job
     */
    public function updateDailyDistances()
    {
        Log::info('Début de la mise à jour des distances journalières');
        
        // Date du jour
        $today = Carbon::today();
        $startDate = $today->copy()->startOfDay();
        $endDate = $today->copy()->endOfDay();
        
        Log::info('Période de mise à jour:', [
            'date' => $today->toDateString(),
            'startDate' => $startDate->format('Y-m-d H:i:s'),
            'endDate' => $endDate->format('Y-m-d H:i:s')
        ]);
        
        // Récupérer tous les chauffeurs actifs et leurs motos
        $associations = AssociationUserMoto::with(['validatedUser', 'motosValide'])->get();
        $driversUpdated = 0;
        $totalDistanceUpdated = 0;
        
        foreach ($associations as $association) {
            if (!$association->validatedUser || !$association->motosValide || !$association->motosValide->gps_imei) {
                continue;
            }
            
            $user = $association->validatedUser;
            $moto = $association->motosValide;
            $macId = $moto->gps_imei;
            
            Log::info("Mise à jour pour le chauffeur:", [
                'id' => $user->id,
                'nom' => $user->nom ?? 'N/A',
                'prenom' => $user->prenom ?? 'N/A',
                'moto' => $moto->vin ?? 'N/A',
                'gps_imei' => $macId
            ]);
            
            // Récupérer tous les points GPS pour cette moto aujourd'hui
            $gpsPoints = GpsLocation::where('macid', $macId)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->orderBy('created_at', 'asc')
                ->get();
                
            Log::info("Points GPS trouvés pour la mise à jour:", [
                'count' => $gpsPoints->count(),
                'date' => $today->toDateString(),
                'macId' => $macId
            ]);
            
            if ($gpsPoints->count() < 2) {
                Log::warning("Pas assez de points GPS pour calculer une distance aujourd'hui");
                continue;
            }
            
            // Calculer la distance
            $totalDistance = 0;
            $lastLocation = 'N/A';
            $hourlyDistances = [];
            
            // Initialiser le tableau des distances horaires
            for ($hour = 0; $hour < 24; $hour++) {
                $hourLabel = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
                $hourlyDistances[$hourLabel] = 0;
            }
            
            for ($i = 1; $i < $gpsPoints->count(); $i++) {
                $prevPoint = $gpsPoints[$i-1];
                $currentPoint = $gpsPoints[$i];
                
                // Vérifier si les coordonnées sont valides
                if (($prevPoint->latitude == 0 && $prevPoint->longitude == 0) || 
                    ($currentPoint->latitude == 0 && $currentPoint->longitude == 0)) {
                    continue;
                }
                
                $distance = $this->calculateDistance(
                    $prevPoint->latitude, 
                    $prevPoint->longitude, 
                    $currentPoint->latitude, 
                    $currentPoint->longitude
                );
                
                // Filtrer les sauts irréalistes (erreurs GPS)
                if ($distance < 50) { // Max 50km entre points consécutifs
                    $totalDistance += $distance;
                    
                    // Ajouter à la statistique horaire
                    $hour = $currentPoint->created_at->format('H');
                    $hourLabel = $hour . ':00';
                    $hourlyDistances[$hourLabel] += $distance;
                }
                
                // Mettre à jour la dernière position connue
                $lastLocation = "Lat: {$currentPoint->latitude}, Lng: {$currentPoint->longitude}";
            }
            
            // Arrondir la distance totale
            $totalDistance = round($totalDistance, 2);
            
            // Formater les distances horaires (arrondir à 2 décimales)
            $formattedHourlyDistances = [];
            foreach ($hourlyDistances as $hour => $distance) {
                if ($distance > 0) {
                    $formattedHourlyDistances[$hour] = round($distance, 2);
                }
            }
            
            // Trier par heure
            ksort($formattedHourlyDistances);
            
            Log::info("Distance calculée pour mise à jour:", [
                'chauffeur' => $user->prenom . ' ' . $user->nom,
                'moto' => $moto->vin,
                'distance' => $totalDistance,
                'répartition_horaire' => $formattedHourlyDistances
            ]);
            
            // Mettre à jour ou créer l'enregistrement dans la base de données
            try {
                // CORRECTION: Utiliser user_id au lieu de validated_user_id pour correspondre à la structure de la table
                $dailyDistance = DailyDistance::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $today->toDateString()
                    ],
                    [
                        'total_distance_km' => $totalDistance,
                        'last_location' => $lastLocation,
                        'last_updated' => now()
                    ]
                );
                
                Log::info("Mise à jour effectuée pour {$user->prenom} {$user->nom}:", [
                    'id' => $dailyDistance->id,
                    'user_id' => $user->id,
                    'date' => $dailyDistance->date,
                    'total_distance_km' => $totalDistance,
                    'last_updated' => $dailyDistance->last_updated
                ]);
                
                $driversUpdated++;
                $totalDistanceUpdated += $totalDistance;
                
            } catch (\Exception $e) {
                Log::error("Erreur lors de la mise à jour de la distance journalière:", [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'date' => $today->toDateString()
                ]);
            }
        }
        
        Log::info('Mise à jour des distances journalières terminée:', [
            'chauffeurs_mis_à_jour' => $driversUpdated,
            'distance_totale' => $totalDistanceUpdated,
            'date' => $today->toDateString()
        ]);
        
        return [
            'success' => true,
            'updated_drivers' => $driversUpdated,
            'total_distance' => $totalDistanceUpdated,
            'date' => $today->toDateString()
        ];
    }
}