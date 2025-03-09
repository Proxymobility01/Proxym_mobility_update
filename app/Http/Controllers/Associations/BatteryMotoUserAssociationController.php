<?php

namespace App\Http\Controllers\Associations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BatteriesValide;
use App\Models\MotosValide;
use App\Models\ValidatedUser;
use App\Models\AssociationUserMoto;
use App\Models\BatteryMotoUserAssociation;
use Carbon\Carbon;

class BatteryMotoUserAssociationController extends Controller
{
    // Affiche la liste des associations et le formulaire
    public function index()
    {
        // Récupérer les associations existantes avec les relations nécessaires
        $associations = BatteryMotoUserAssociation::with(['association', 'batterie'])->orderBy('created_at', 'desc')->get();
    
        // Récupérer les batteries qui ne sont pas associées à une moto via 'association_user_moto_id'
        $batteries = BatteriesValide::whereDoesntHave('motos')->get();
    
        // Récupérer les motos associées à des utilisateurs
        $motos = MotosValide::whereHas('users')->with('users')->get();
        
        // Récupérer les utilisateurs validés
        $users = ValidatedUser::all();
    
        $pageTitle = 'Associations des batteries aux motos et aux utilisateurs';
    
        return view('association.association_batterie_moto_user', compact('associations', 'batteries', 'motos', 'users', 'pageTitle'));
    }
    
    /**
     * Stocke une nouvelle association de batterie à l'utilisateur
     */
    public function store(Request $request)
    {
        // Validation des données du formulaire
        $request->validate([
            'battery_unique_id' => 'required|array',
            'battery_unique_id.*' => 'exists:batteries_valides,batterie_unique_id',
            'user_unique_id' => 'required|array',
            'user_unique_id.*' => 'exists:validated_users,user_unique_id',
        ]);

        $successCount = 0;
        $errorCount = 0;
        
        // Pour chaque batterie
        foreach ($request->battery_unique_id as $batteryUniqueId) {
            $batterie = BatteriesValide::where('batterie_unique_id', $batteryUniqueId)->first();
            
            if (!$batterie) {
                $errorCount++;
                continue;
            }
            
            // Pour chaque utilisateur
            foreach ($request->user_unique_id as $userUniqueId) {
                $user = ValidatedUser::where('user_unique_id', $userUniqueId)->first();
                
                if (!$user) {
                    $errorCount++;
                    continue;
                }

                // Vérifier si la batterie est déjà associée
                $existingAssociation = BatteryMotoUserAssociation::where('battery_id', $batterie->id)
                    ->where('user_id', $user->id)
                    ->first();
                
                if (!$existingAssociation) {
                    // Créer une nouvelle association
                    BatteryMotoUserAssociation::create([
                        'battery_id' => $batterie->id,
                        'user_id' => $user->id,
                        'date_association' => Carbon::now(),
                    ]);
                    
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
        }
        
        if ($successCount > 0 && $errorCount == 0) {
            return redirect()->route('associations.batterie.moto.user.index')->with('success', "$successCount association(s) créée(s) avec succès.");
        } elseif ($successCount > 0 && $errorCount > 0) {
            return redirect()->route('associations.batterie.moto.user.index')->with('warning', "$successCount association(s) créée(s), mais $errorCount non réalisée(s) car déjà existante(s).");
        } else {
            return redirect()->route('associations.batterie.moto.user.index')->with('error', "Aucune association créée. $errorCount association(s) déjà existante(s).");
        }
    }
    
    /**
     * Confirme une association de batterie avec un utilisateur
     */
    public function confirmAssociation(Request $request)
    {
        // Validation des données
        $batteryId = $request->battery_id;
        $userId = $request->user_ids;
        
        $battery = BatteriesValide::find($batteryId);
        $user = ValidatedUser::where('user_unique_id', $userId)->first();
        
        if (!$battery || !$user) {
            return redirect()->back()->with('error', 'Batterie ou utilisateur introuvable.');
        }
        
        // Vérifier si l'association existe déjà
        $existingAssociation = BatteryMotoUserAssociation::where('battery_id', $battery->id)
            ->where('user_id', $user->id)
            ->first();
            
        if ($existingAssociation) {
            return redirect()->back()->with('error', 'Cette association existe déjà.');
        }
        
        // Créer l'association
        BatteryMotoUserAssociation::create([
            'battery_id' => $battery->id,
            'user_id' => $user->id,
            'date_association' => Carbon::now(),
        ]);
        
        return redirect()->back()->with('success', 'L\'association a été confirmée avec succès.');
    }
    
    /**
     * Met à jour une association existante
     */
    public function update(Request $request, $id)
    {
        // Validation des données
        $request->validate([
            'battery_unique_id' => 'required|exists:batteries_valides,batterie_unique_id',
            'user_unique_id' => 'required|exists:validated_users,user_unique_id',
        ]);
        
        $association = BatteryMotoUserAssociation::findOrFail($id);
        
        $battery = BatteriesValide::where('batterie_unique_id', $request->battery_unique_id)->first();
        $user = ValidatedUser::where('user_unique_id', $request->user_unique_id)->first();
        
        if (!$battery || !$user) {
            return redirect()->back()->with('error', 'Batterie ou utilisateur introuvable.');
        }
        
        // Vérifier si l'association existe déjà ailleurs
        $existingAssociation = BatteryMotoUserAssociation::where('battery_id', $battery->id)
            ->where('user_id', $user->id)
            ->where('id', '!=', $id)
            ->first();
            
        if ($existingAssociation) {
            return redirect()->back()->with('error', 'Cette association existe déjà.');
        }
        
        // Mettre à jour l'association
        $association->battery_id = $battery->id;
        $association->user_id = $user->id;
        $association->date_association = Carbon::now();
        $association->save();
        
        return redirect()->route('associations.batterie.moto.user.index')->with('success', 'L\'association a été mise à jour avec succès.');
    }
    
    /**
     * Supprime une association
     */
    public function destroy($id)
    {
        $association = BatteryMotoUserAssociation::findOrFail($id);
        $association->delete();
        
        return redirect()->route('associations.batterie.moto.user.index')->with('success', 'L\'association a été supprimée avec succès.');
    }
    
    /**
     * Récupère les détails d'une association
     */
    public function getAssociationDetails($id)
    {
        $association = BatteryMotoUserAssociation::with(['user', 'batterie'])->findOrFail($id);
        
        return response()->json([
            'id' => $association->id,
            'user_unique_id' => $association->user->user_unique_id,
            'user_nom' => $association->user->nom,
            'user_prenom' => $association->user->prenom,
            'battery_unique_id' => $association->batterie->batterie_unique_id,
            'battery_pourcentage' => $association->batterie->pourcentage,
            'date_association' => Carbon::parse($association->date_association)->format('d/m/Y'),
        ]);
    }
    
    /**
     * Récupère toutes les associations
     */
    public function getAllAssociations(Request $request)
    {
        $query = BatteryMotoUserAssociation::with(['user', 'batterie']);
        
        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('user_unique_id', 'like', "%{$search}%");
            })->orWhereHas('batterie', function($q) use ($search) {
                $q->where('batterie_unique_id', 'like', "%{$search}%");
            });
        }
        
        $associations = $query->orderBy('created_at', 'desc')->get();
        
        // Transformer les données pour l'API
        $formattedAssociations = $associations->map(function($association) {
            return [
                'id' => $association->id,
                'user_unique_id' => $association->user->user_unique_id,
                'user_nom' => $association->user->nom,
                'user_prenom' => $association->user->prenom,
                'battery_unique_id' => $association->batterie->batterie_unique_id,
                'battery_pourcentage' => $association->batterie->pourcentage,
                'date_association' => Carbon::parse($association->date_association)->format('d/m/Y'),
            ];
        });
        
        return response()->json($formattedAssociations);
    }
    
    /**
     * Récupère les statistiques
     */
    public function getStats()
    {
        $totalBatteries = BatteriesValide::count();
        $totalUsers = ValidatedUser::count();
        $totalAssociations = BatteryMotoUserAssociation::count();
        
        return response()->json([
            'total_batteries' => $totalBatteries,
            'total_users' => $totalUsers,
            'total_associations' => $totalAssociations,
        ]);
    }
}