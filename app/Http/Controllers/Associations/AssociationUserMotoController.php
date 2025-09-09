<?php

namespace App\Http\Controllers\Associations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MotosValide;
use App\Models\ValidatedUser;
use App\Models\AssociationUserMoto;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssociationUserMotoController extends Controller
{
    public function __construct()
    {
        // Constructeur sans dépendance
    }

    /**
     * Afficher la vue d'association des utilisateurs et des motos
     */
    public function showAssociationForm()
    {
        $motos = MotosValide::all();
        $users = ValidatedUser::all();
    
        // Récupérer toutes les associations triées par created_at en ordre décroissant
        $associations = AssociationUserMoto::query()
            ->with(['validatedUser', 'motosValide']) // Charger les relations nécessaires
            ->orderBy('created_at', 'desc') // Ajouter le tri
            ->get();
    
        $pageTitle = 'Associations des motos aux utilisateurs';
    
        return view('association.association_user_moto', compact('motos', 'users', 'associations', 'pageTitle'));
    }
    
    /**
     * Associer une moto à un utilisateur
     */
    public function associerMotoAUtilisateurs(Request $request)
    {
        // Validation des données
        $request->validate([
            'moto_unique_id' => 'required|exists:motos_valides,moto_unique_id',
            'user_unique_id' => 'required|exists:validated_users,user_unique_id',
        ]);
    
        // Récupérer la moto par son ID unique
        $moto = MotosValide::where('moto_unique_id', $request->moto_unique_id)->first();
    
        // Si la moto n'existe pas
        if (!$moto) {
            return redirect()->back()->with('error', 'La moto n\'existe pas.');
        }
    
        // Récupérer l'utilisateur par son ID unique
        $user = ValidatedUser::where('user_unique_id', $request->user_unique_id)->first();
        
        // Si l'utilisateur n'existe pas
        if (!$user) {
            return redirect()->back()->with('error', 'L\'utilisateur n\'existe pas.');
        }
    
        // Vérifier si la moto est déjà associée à un utilisateur
        $existingAssociations = DB::table('association_user_motos')
            ->where('moto_valide_id', $moto->id)
            ->pluck('validated_user_id')
            ->toArray();
    
        // Si des associations existent déjà pour cette moto
        if (!empty($existingAssociations)) {
            // Stocker les informations nécessaires pour la confirmation
            return redirect()->back()
                ->with('warning', 'Cette moto est déjà associée à un ou plusieurs utilisateurs. Voulez-vous l\'associer à d\'autres utilisateurs ?')
                ->with('moto_id', $moto->id)
                ->with('user_id', $user->user_unique_id);
        }
    
        // Vérifier si l'association existe déjà
        $exists = DB::table('association_user_motos')
            ->where('validated_user_id', $user->id)
            ->where('moto_valide_id', $moto->id)
            ->exists();
    
        // Si l'association n'existe pas déjà
        if (!$exists) {
            // Ajouter l'association
            DB::table('association_user_motos')->insert([
                'validated_user_id' => $user->id,
                'moto_valide_id' => $moto->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return redirect()->back()->with('success', 'La moto a été associée à l\'utilisateur sélectionné.');
        } else {
            return redirect()->back()->with('error', 'L\'utilisateur est déjà associé à cette moto.');
        }
    }
    
    /**
     * Action à appeler si l'utilisateur confirme l'association
     */
    public function confirmAssociation(Request $request)
    {
        // Récupérer la moto et l'utilisateur associé
        $moto = MotosValide::find($request->moto_id);
        if (!$moto) {
            return redirect()->back()->with('error', 'Moto introuvable.');
        }
        
        // Récupérer l'utilisateur
        $user = ValidatedUser::where('user_unique_id', $request->user_ids)->first();
        if (!$user) {
            return redirect()->back()->with('error', 'Utilisateur introuvable.');
        }
        
        // Vérifier si l'association existe déjà
        $exists = DB::table('association_user_motos')
            ->where('validated_user_id', $user->id)
            ->where('moto_valide_id', $moto->id)
            ->exists();
            
        if (!$exists) {
            // Ajouter l'association
            DB::table('association_user_motos')->insert([
                'validated_user_id' => $user->id,
                'moto_valide_id' => $moto->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return redirect()->back()->with('success', 'L\'association a été confirmée avec succès.');
        } else {
            return redirect()->back()->with('error', 'L\'association existe déjà.');
        }
    }
    
    /**
     * Méthode pour mettre à jour une association
     */
    public function updateAssociation(Request $request, $id)
    {
        // Validation des données
        $request->validate([
            'moto_unique_id' => 'required|exists:motos_valides,moto_unique_id',
            'user_unique_id' => 'required|exists:validated_users,user_unique_id',
        ]);

        // Récupérer l'association
        $association = AssociationUserMoto::findOrFail($id);
        
        // Récupérer la moto et l'utilisateur
        $moto = MotosValide::where('moto_unique_id', $request->moto_unique_id)->first();
        $user = ValidatedUser::where('user_unique_id', $request->user_unique_id)->first();
        
        if (!$moto || !$user) {
            return redirect()->back()->with('error', 'Moto ou utilisateur introuvable.');
        }
        
        // Vérifier si l'association existe déjà ailleurs
        $exists = DB::table('association_user_motos')
            ->where('validated_user_id', $user->id)
            ->where('moto_valide_id', $moto->id)
            ->where('id', '!=', $id)
            ->exists();
            
        if ($exists) {
            return redirect()->back()->with('error', 'Cette association existe déjà.');
        }
        
        // Mettre à jour l'association
        $association->validated_user_id = $user->id;
        $association->moto_valide_id = $moto->id;
        $association->updated_at = now();
        $association->save();
        
        return redirect()->back()->with('success', 'L\'association a été mise à jour avec succès.');
    }
    
    /**
     * Méthode pour supprimer une association
     */
    public function deleteAssociation($id)
    {
        $association = AssociationUserMoto::findOrFail($id);
        $association->delete();
        
        return redirect()->back()->with('success', 'L\'association a été supprimée avec succès.');
    }
    
    /**
     * Méthode pour filtrer les associations (pour la recherche)
     */
    public function filterAssociations(Request $request)
    {
        $query = AssociationUserMoto::query()
            ->with(['validatedUser', 'motosValide'])
            ->orderBy('created_at', 'desc');
            
        // Si une recherche est fournie
        if ($request->filled('search')) {
            $search = $request->search;
            
            $query->whereHas('validatedUser', function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('user_unique_id', 'like', "%{$search}%");
            })->orWhereHas('motosValide', function($q) use ($search) {
                $q->where('model', 'like', "%{$search}%")
                  ->orWhere('moto_unique_id', 'like', "%{$search}%");
            });
        }
        
        $associations = $query->get();
        
        // Si c'est une requête AJAX, retourner en JSON
        if ($request->ajax()) {
            return response()->json($associations);
        }
        
        // Sinon, charger la vue avec les résultats filtrés
        $motos = MotosValide::all();
        $users = ValidatedUser::all();
        $pageTitle = 'Associations des motos aux utilisateurs';
        
        return view('association.association_user_moto', compact('motos', 'users', 'associations', 'pageTitle'));
    }
    
    /**
     * Obtenir les statistiques pour les motos et utilisateurs
     */
    public function getStats()
    {
        $totalMotos = MotosValide::count();
        $totalUsers = ValidatedUser::count();
        $totalAssociations = AssociationUserMoto::count();
            
        return response()->json([
            'total_motos' => $totalMotos,
            'total_users' => $totalUsers,
            'total_associations' => $totalAssociations
        ]);
    }
    
    /**
     * Méthode pour obtenir les détails d'une association spécifique
     */
    public function getAssociationDetails($id)
    {
        $association = AssociationUserMoto::with(['validatedUser', 'motosValide'])
            ->findOrFail($id);
            
        return response()->json([
            'id' => $association->id,
            'user_unique_id' => $association->validatedUser->user_unique_id,
            'user_nom' => $association->validatedUser->nom,
            'user_prenom' => $association->validatedUser->prenom,
            'moto_unique_id' => $association->motosValide->moto_unique_id,
            'moto_model' => $association->motosValide->model,
            'created_at' => Carbon::parse($association->created_at)->format('d/m/Y H:i:s')
        ]);
    }
    
    /**
     * Méthode pour obtenir toutes les associations au format JSON (pour API)
     */
    public function getAllAssociations()
    {
        $associations = AssociationUserMoto::with(['validatedUser', 'motosValide'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($association) {
                return [
                    'id' => $association->id,
                    'user_unique_id' => $association->validatedUser->user_unique_id,
                    'user_nom' => $association->validatedUser->nom,
                    'user_prenom' => $association->validatedUser->prenom,
                    'moto_unique_id' => $association->motosValide->moto_unique_id,
                    'moto_model' => $association->motosValide->model,
                    'created_at' => Carbon::parse($association->created_at)->format('d/m/Y H:i:s')
                ];
            });
            
        return response()->json($associations);
    }
    
    /**
     * Méthode pour obtenir les associations d'un utilisateur spécifique
     */
    public function getUserAssociations($userUniqueId)
    {
        $user = ValidatedUser::where('user_unique_id', $userUniqueId)->firstOrFail();
        
        $associations = AssociationUserMoto::with('motosValide')
            ->where('validated_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($association) {
                return [
                    'id' => $association->id,
                    'moto_unique_id' => $association->motosValide->moto_unique_id,
                    'moto_model' => $association->motosValide->model,
                    'created_at' => Carbon::parse($association->created_at)->format('d/m/Y H:i:s')
                ];
            });
            
        return response()->json($associations);
    }
    
    /**
     * Méthode pour obtenir les associations d'une moto spécifique
     */
    public function getMotoAssociations($motoUniqueId)
    {
        $moto = MotosValide::where('moto_unique_id', $motoUniqueId)->firstOrFail();
        
        $associations = AssociationUserMoto::with('validatedUser')
            ->where('moto_valide_id', $moto->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($association) {
                return [
                    'id' => $association->id,
                    'user_unique_id' => $association->validatedUser->user_unique_id,
                    'user_nom' => $association->validatedUser->nom,
                    'user_prenom' => $association->validatedUser->prenom,
                    'created_at' => Carbon::parse($association->created_at)->format('d/m/Y H:i:s')
                ];
            });
            
        return response()->json($associations);
    }
    
    /**
     * Méthode pour vérifier si une moto est déjà associée
     */
    public function checkMotoAssociation($motoUniqueId)
    {
        $moto = MotosValide::where('moto_unique_id', $motoUniqueId)->first();
        
        if (!$moto) {
            return response()->json(['exists' => false, 'message' => 'Moto introuvable']);
        }
        
        $exists = AssociationUserMoto::where('moto_valide_id', $moto->id)->exists();
        
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Cette moto est déjà associée à un utilisateur' : 'Cette moto n\'est pas encore associée'
        ]);
    }
    
    /**
     * Méthode pour vérifier si un utilisateur est déjà associé à une moto spécifique
     */
    public function checkUserMotoAssociation($userUniqueId, $motoUniqueId)
    {
        $user = ValidatedUser::where('user_unique_id', $userUniqueId)->first();
        $moto = MotosValide::where('moto_unique_id', $motoUniqueId)->first();
        
        if (!$user || !$moto) {
            return response()->json(['exists' => false, 'message' => 'Utilisateur ou moto introuvable']);
        }
        
        $exists = AssociationUserMoto::where('validated_user_id', $user->id)
            ->where('moto_valide_id', $moto->id)
            ->exists();
        
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Cet utilisateur est déjà associé à cette moto' : 'Cet utilisateur n\'est pas encore associé à cette moto'
        ]);
    }



//coupure du swap
    public function toggleSwap(Request $request, int $id)
{
    // Renvoie 0/1 (0 = couper, 1 = rallumer) pour consumption par le JS
    return DB::transaction(function () use ($id) {
        $assoc = AssociationUserMoto::lockForUpdate()->findOrFail($id);
        $assoc->swap_bloque = ! $assoc->swap_bloque; // inverse l’état
        $assoc->save();

        return response()->json((int) $assoc->swap_bloque, 200);
    });
}
}