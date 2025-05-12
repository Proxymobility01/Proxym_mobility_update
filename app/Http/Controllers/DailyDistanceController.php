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

        // Récupérer les paramètres de filtre
        $timeFilter = $request->input('time_filter', 'today');
        $startDate = null;
        $endDate = null;
        $customDate = null;
        
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
        
        // Récupérer la liste des chauffeurs actifs
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
        
        // Récupérer les distances depuis la base de données
        $query = DailyDistance::with('user')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            
        // Appliquer le filtre de recherche si spécifié
        $search = $request->input('search');
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }
        
        // Appliquer le filtre de chauffeur si spécifié
        $driverId = $request->input('driver');
        if ($driverId && $driverId !== 'all') {
            $query->where('user_id', $driverId);
        }
        
        // Récupérer les résultats
        $distances = $query->orderBy('total_distance_km', 'desc')->get();
        
        // Calculer les statistiques
        $stats = [
            'total_distance' => $distances->sum('total_distance_km'),
            'total_drivers' => $distances->count(),
            'avg_distance' => $distances->count() > 0 ? 
                $distances->sum('total_distance_km') / $distances->count() : 0,
            'max_distance' => $distances->max('total_distance_km') ?? 0,
            'active_drivers' => $distances->where('total_distance_km', '>', 0)->count()
        ];
        
        // Date format pour l'affichage
        $displayDate = $this->getDisplayDateText($timeFilter, $startDate, $endDate, $customDate);
        
        return view('distances.index', compact(
            'distances', 
            'stats', 
            'timeFilter', 
            'displayDate', 
            'drivers', 
            'customDate'
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
        // Récupérer les paramètres de filtre
        $timeFilter = $request->input('time_filter', 'today');
        $startDate = null;
        $endDate = null;
        $customDate = null;
        
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
        
        // Récupérer les distances depuis la base de données
        $query = DailyDistance::with('user')
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
            
        // Appliquer le filtre de recherche si spécifié
        $search = $request->input('search');
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }
        
        // Appliquer le filtre de chauffeur si spécifié
        $driverId = $request->input('driver');
        if ($driverId && $driverId !== 'all') {
            $query->where('user_id', $driverId);
        }
        
        // Récupérer les résultats
        $distances = $query->orderBy('total_distance_km', 'desc')->get();
        
        // Formater les résultats pour l'API
        $formattedDistances = [];
        foreach ($distances as $distance) {
            $formattedDistances[] = [
                'id' => $distance->user->id,
                'name' => ($distance->user->prenom ?? '') . ' ' . ($distance->user->nom ?? 'N/A'),
                'phone' => $distance->user->phone ?? 'N/A',
                'distance_km' => number_format($distance->total_distance_km, 2),
                'last_location' => $distance->last_location ?? 'N/A',
                'status' => $this->getStatusLabel($distance->total_distance_km),
                'hourly_distribution' => [],
                'created_at' => $distance->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $distance->updated_at->format('Y-m-d H:i:s'),
                'date' => $distance->updated_at->format('Y-m-d'), // Date formatée à partir de updated_at
                'time' => $distance->updated_at->format('H:i:s'), // Heure formatée à partir de updated_at
            ];
        }
        
        // Calculer les statistiques
        $stats = [
            'total_distance' => number_format($distances->sum('total_distance_km'), 2),
            'total_drivers' => $distances->count(),
            'date_label' => $this->getDisplayDateText($timeFilter, $startDate, $endDate, $customDate),
            'active_drivers' => $distances->where('total_distance_km', '>', 0)->count()
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
        // Récupérer les paramètres de filtre
        $timeFilter = $request->input('time_filter', 'today');
        $startDate = null;
        $endDate = null;
        $customDate = null;
        
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

        // Pour chaque date dans la plage
        $currentDate = $startDate->copy()->startOfDay();
        $endDateDay = $endDate->copy()->startOfDay();
        
        $results = [];
        $totalDistanceCalculated = 0;
        $processedCount = 0;

        while ($currentDate->lte($endDateDay)) {
            $dateStr = $currentDate->toDateString();
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();
            
            // Mise à jour pour cette journée
            $updateResult = $this->updateDailyDistancesForDate($dateStr);
            
            // Récupérer les données mises à jour
            $dailyDistances = DailyDistance::with('user')
                ->where('date', $dateStr)
                ->get();
                
            $dailyTotal = $dailyDistances->sum('total_distance_km');
            $totalDistanceCalculated += $dailyTotal;
            $processedCount += $dailyDistances->count();
            
            // Formater les résultats
            $details = [];
            foreach ($dailyDistances as $distance) {
                $details[] = [
                    'chauffeur' => ($distance->user->prenom ?? '') . ' ' . ($distance->user->nom ?? 'N/A'),
                    'user_id' => $distance->user_id,
                    'distance' => $distance->total_distance_km,
                    'last_location' => $distance->last_location,
                    'date' => $distance->date,
                    'updated_at' => $distance->updated_at->format('Y-m-d H:i:s'), // Ajout de la date de mise à jour
                ];
            }
            
            $results[$dateStr] = [
                'processed' => $dailyDistances->count(),
                'totalDistance' => $dailyTotal,
                'details' => $details
            ];
            
            $currentDate->addDay();
        }
        
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
        $status = '';
        if ($distance > 50) {
            $status = 'Actif';
        } elseif ($distance > 10) {
            $status = 'Modéré';
        } else {
            $status = 'Faible activité';
        }
        
        return $status;
    }
    
    /**
     * Obtenir le texte d'affichage pour le filtre de date
     */
    private function getDisplayDateText($timeFilter, $startDate, $endDate, $customDate)
    {
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
        
        return $displayText;
    }
    
    /**
     * Calculer la distance entre deux coordonnées GPS en utilisant la formule de Haversine
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Rayon de la Terre en kilomètres
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        return $distance;
    }
    
    /**
     * Mettre à jour les distances journalières pour une date spécifique
     */
    private function updateDailyDistancesForDate($date)
    {
        Log::info("Mise à jour des distances pour la date: {$date}");
        
        $targetDate = Carbon::parse($date);
        $startDate = $targetDate->copy()->startOfDay();
        $endDate = $targetDate->copy()->endOfDay();
        
        // Récupérer tous les chauffeurs actifs et leurs motos
        $associations = AssociationUserMoto::with(['validatedUser', 'motosValide'])->get();
        $driversUpdated = 0;
        $totalDistanceUpdated = 0;
        
        foreach ($associations as $association) {
            // Vérifier si l'utilisateur et la moto existent
            if (!$association->validatedUser || !$association->motosValide || !$association->motosValide->gps_imei) {
                continue;
            }
            
            $user = $association->validatedUser;
            $moto = $association->motosValide;
            $macId = $moto->gps_imei;
            
            // Récupérer les points GPS pour cette moto sur cette date
            $gpsPoints = GpsLocation::where('macid', $macId)
                ->where('created_at', '>=', $startDate)
                ->where('created_at', '<=', $endDate)
                ->orderBy('created_at', 'asc')
                ->get();
                
            if ($gpsPoints->count() < 2) {
                continue;
            }
            
            // Vérifier si un enregistrement existe déjà pour cet utilisateur à cette date
            $existingRecord = DailyDistance::where('user_id', $user->id)
                ->where('date', $date)
                ->first();
                
            $isNewRecord = ($existingRecord === null);
            
            // Initialiser la dernière position connue
            $lastLocation = $isNewRecord ? 'N/A' : $existingRecord->last_location;
            
            // Recalculer entièrement la distance totale
            $totalDistance = 0;
            
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
                }
                
                // Mettre à jour la dernière position connue
                $lastLocation = "Lat: {$currentPoint->latitude}, Lng: {$currentPoint->longitude}";
            }
            
            // Arrondir la distance totale
            $totalDistance = round($totalDistance, 2);
            
            // Créer un nouvel enregistrement ou mettre à jour l'existant
            try {
                $dailyDistance = DailyDistance::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $date
                    ],
                    [
                        'total_distance_km' => $totalDistance,
                        'last_location' => $lastLocation,
                        'last_updated' => now()
                    ]
                );
                
                $driversUpdated++;
                $totalDistanceUpdated += $totalDistance;
                
            } catch (\Exception $e) {
                Log::error("Erreur lors de la mise à jour de la distance journalière:", [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'date' => $date
                ]);
            }
        }
        
        return [
            'success' => true,
            'updated_drivers' => $driversUpdated,
            'total_distance' => $totalDistanceUpdated,
            'date' => $date
        ];
    }
    
    /**
     * Mettre à jour les distances journalières pour tous les chauffeurs - pour le cron job
     */
    public function updateDailyDistances()
    {
        return $this->updateDailyDistancesForDate(Carbon::today()->toDateString());
    }
}