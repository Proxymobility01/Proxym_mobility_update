<?php

namespace App\Http\Controllers\Distributeurs;

use App\Models\Distributeur;
use App\Models\UsersDistributeur;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DistributeurController extends Controller
{
    /**
     * Affiche la vue principale de gestion des distributeurs
     */
    public function index()
    {
        try {
            $distributeurs = Distributeur::orderBy('created_at', 'desc')->get();
            return view('distributeurs.index', compact('distributeurs'));
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode index: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors du chargement de la page.');
        }
    }

    /**
     * Récupère la liste des distributeurs (pour rafraîchissement AJAX)
     */
    public function getDistributeursList()
    {
        try {
            $distributeurs = Distributeur::orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'distributeurs' => $distributeurs
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode getDistributeursList: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des distributeurs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des distributeurs (pour mise à jour AJAX)
     */
    public function getStats()
    {
        try {
            $totalDistributeurs = Distributeur::count();
            $totalDistributeursDouala = Distributeur::where('ville', 'Douala')->count();
            $totalDistributeursYaounde = Distributeur::where('ville', 'Yaoundé')->count();
            $totalUsers =  UsersDistributeur::count();
            
            return response()->json([
                'success' => true,
                'total_distributeurs' => $totalDistributeurs,
                'total_douala' => $totalDistributeursDouala,
                'total_yaounde' => $totalDistributeursYaounde,
                'total_users' => $totalUsers,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode getStats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les détails d'un distributeur pour l'édition
     */
    public function getDistributeurDetails($id)
    {
        try {
            $distributeur = Distributeur::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'distributeur' => $distributeur
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode getDistributeurDetails: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des détails du distributeur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Route pour récupérer les statistiques actualisées
     */
    public function getStatistics()
    {
        try {
            $totalDistributeurs = Distributeur::count();
            $totalDouala = Distributeur::where('ville', 'Douala')->count();
            $totalYaounde = Distributeur::where('ville', 'Yaoundé')->count();
            $totalUsers = UsersDistributeur::count();
            
            return response()->json([
                'success' => true,
                'total_distributeurs' => $totalDistributeurs,
                'total_douala' => $totalDouala,
                'total_yaounde' => $totalYaounde,
                'total_users' => $totalUsers
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode getStatistics: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enregistre un nouveau distributeur
     */
    public function store(Request $request)
    {
        try {
            // Validation des entrées
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'ville' => 'required|string|max:255',
                'quartier' => 'required|string|max:255',
                'phone' => 'required|string|unique:distributeurs',
                'email' => 'required|email|unique:distributeurs',
                'password' => 'required|string|min:6',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
        
            // Liste des codes de quartiers pour chaque ville
            $quartiers = [
                "Douala" => [
                    "Akwa" => "AKW", "Bali" => "BAL", "Bekoko" => "BEK", "Bepanda" => "BEP", "Bilonguè" => "BIL",
                    "Bonabéri" => "BNB", "Bonadibong" => "BND", "Bonagang" => "BNG", "Bonamikengue" => "BNM", "Bonamouang" => "BNA",
                    "Bonamoussadi" => "BMS", "Bonamoussadi Cité" => "BMC", "Bonanjo" => "BNO", "Bonapriso" => "BAP",
                    "Deido" => "DEI", "Logbaba" => "LGB", "Ndokoti" => "NDT"
                ],
                "Yaoundé" => [
                    "Ahala" => "AHA", "Anguissa" => "ANG", "Bastos" => "BAS", "Biyem-Assi" => "BYA", "Briqueterie" => "BRI",
                    "Elig-Effa" => "ELE", "Melen" => "MEL", "Messassi" => "MSS", "Mokolo" => "MOK", "Obili" => "OBI"
                ]
            ];
        
            // Codes des villes
            $codesVilles = [
                'Douala' => 'DLA',
                'Yaoundé' => 'YDE'
            ];
        
            // Récupération du code quartier (avec un fallback si non trouvé)
            $quartierCode = isset($quartiers[$request->ville][$request->quartier])
                ? $quartiers[$request->ville][$request->quartier]
                : strtoupper(substr($request->quartier, 0, 3));
        
            // Récupération du code ville (DLA ou YDE)
            $villeCode = $codesVilles[$request->ville] ?? strtoupper(substr($request->ville, 0, 3));
        
            // Nombre de distributeurs existants dans le même quartier pour incrémentation du numéro de station
            $count = Distributeur::where('ville', $request->ville)
                           ->where('quartier', $request->quartier)
                           ->count();
        
            // Formatage du numéro de distributeur avec 2 chiffres (01, 02, 03, ...)
            $numDistributeur = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        
            // Génération de l'ID unique du distributeur
            $distributeurUniqueId = "DI-{$villeCode}-{$quartierCode}-{$numDistributeur}";
        
            // Hash du mot de passe
            $hashedPassword = Hash::make($request->password);
        
            // Création du distributeur dans la base de données
            $distributeur = Distributeur::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'ville' => $request->ville,
                'quartier' => $request->quartier,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => $hashedPassword,
                'distributeur_unique_id' => $distributeurUniqueId,
            ]);
        
            return response()->json([
                'success' => true,
                'message' => 'Distributeur ajouté avec succès',
                'distributeur' => $distributeur
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode store: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du distributeur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche les détails d'un distributeur pour l'édition
     */
    public function edit($id)
    {
        try {
            $distributeur = Distributeur::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'distributeur' => $distributeur
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode edit: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des détails du distributeur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour un distributeur existant
     */
    public function update(Request $request, $id)
    {
        try {
            $distributeur = Distributeur::findOrFail($id);
            
            // Validation des champs
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'ville' => 'required|string|max:255',
                'quartier' => 'required|string|max:255',
                'phone' => 'required|string|unique:distributeurs,phone,' . $id,
                'email' => 'required|email|unique:distributeurs,email,' . $id,
                'password' => 'nullable|string|min:6',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
    
            $data = $request->except(['password', '_token', '_method']);
    
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }
    
            // Vérifie si la ville ou le quartier a changé
            if (
                $request->ville !== $distributeur->ville || 
                $request->quartier !== $distributeur->quartier
            ) {
                // Liste des codes de quartiers pour chaque ville
                $quartiers = [
                    "Douala" => [
                        "Akwa" => "AKW", "Bali" => "BAL", "Bekoko" => "BEK", "Bepanda" => "BEP", "Bilonguè" => "BIL",
                        "Bonabéri" => "BNB", "Bonadibong" => "BND", "Bonagang" => "BNG", "Bonamikengue" => "BNM", "Bonamouang" => "BNA",
                        "Bonamoussadi" => "BMS", "Bonamoussadi Cité" => "BMC", "Bonanjo" => "BNO", "Bonapriso" => "BAP",
                        "Deido" => "DEI", "Logbaba" => "LGB", "Ndokoti" => "NDT"
                    ],
                    "Yaoundé" => [
                        "Ahala" => "AHA", "Anguissa" => "ANG", "Bastos" => "BAS", "Biyem-Assi" => "BYA", "Briqueterie" => "BRI",
                        "Elig-Effa" => "ELE", "Melen" => "MEL", "Messassi" => "MSS", "Mokolo" => "MOK", "Obili" => "OBI"
                    ]
                ];
    
                // Codes des villes
                $codesVilles = [
                    'Douala' => 'DLA',
                    'Yaoundé' => 'YDE'
                ];
    
                $ville = $request->ville;
                $quartier = $request->quartier;
    
                $quartierCode = isset($quartiers[$ville][$quartier])
                    ? $quartiers[$ville][$quartier]
                    : strtoupper(substr($quartier, 0, 3));
                $villeCode = $codesVilles[$ville] ?? strtoupper(substr($ville, 0, 3));
    
                $count = Distributeur::where('ville', $ville)
                                   ->where('quartier', $quartier)
                                   ->count();
    
                $numDistributeur = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
                $distributeurUniqueId = "DI-{$villeCode}-{$quartierCode}-{$numDistributeur}";
    
                $data['distributeur_unique_id'] = $distributeurUniqueId;
            }
    
            $distributeur->update($data);
    
            // Rechargement du distributeur pour obtenir les données à jour
            $distributeur = $distributeur->fresh();
    
            return response()->json([
                'success' => true,
                'message' => 'Distributeur mis à jour avec succès',
                'distributeur' => $distributeur
            ]);
    
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode update: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du distributeur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime un distributeur
     */
    public function destroy($id)
    {
        try {
            $distributeur = Distributeur::findOrFail($id);
            
            // Vérifier les relations avant la suppression
            // Exemple: si le distributeur est lié à d'autres entités, on pourrait empêcher la suppression
            
            $nom = $distributeur->nom;
            $prenom = $distributeur->prenom;
            
            $distributeur->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Le distributeur $nom $prenom a été supprimé avec succès"
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode destroy: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du distributeur: ' . $e->getMessage()
            ], 500);
        }
    }
}