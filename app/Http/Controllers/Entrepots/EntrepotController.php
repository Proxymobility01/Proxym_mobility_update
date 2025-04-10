<?php

namespace App\Http\Controllers\Entrepots;

use App\Models\Entrepot;
use App\Models\UsersEntrepot;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EntrepotController extends Controller
{
    /**
     * Affiche la vue principale de gestion des entrepôts
     */
    public function index()
    {
        try {
            $entrepots = Entrepot::orderBy('created_at', 'desc')->get();
            return view('entrepots.index', compact('entrepots'));
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode index: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors du chargement de la page.');
        }
    }

    /**
     * Récupère la liste des entrepôts (pour rafraîchissement AJAX)
     */
    public function getEntrepotsList()
    {
        try {
            $entrepots = Entrepot::orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'entrepots' => $entrepots
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode getEntrepotsList: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des entrepôts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des entrepôts (pour mise à jour AJAX)
     */
    public function getStats()
    {
        try {
            $totalEntrepots = Entrepot::count();
            $totalEntrepotsDouala = Entrepot::where('ville', 'Douala')->count();
            $totalEntrepotsYaounde = Entrepot::where('ville', 'Yaoundé')->count();
            $totalUsers =  UsersEntrepot::count();
            
            return response()->json([
                'total_entrepots' => $totalEntrepots,
                'total_douala' => $totalEntrepotsDouala,
                'total_yaounde' => $totalEntrepotsYaounde,
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
     * Récupère les détails d'un entrepôt pour l'édition
     */
    public function getEntrepotDetails($id)
    {
        try {
            $entrepot = Entrepot::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'entrepot' => $entrepot
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode getEntrepotDetails: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des détails de l\'entrepôt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enregistre un nouvel entrepôt
     */
    public function store(Request $request)
    {
        try {
            // Validation des entrées
            $validator = Validator::make($request->all(), [
                'nom_entrepot' => 'required|string|max:255',
                'nom_proprietaire' => 'required|string|max:255',
                'ville' => 'required|string|max:255',
                'quartier' => 'required|string|max:255',
                'telephone' => 'required|string|unique:entrepots',
                'email' => 'required|email|unique:entrepots',
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
        
            // Nombre d'entrepôts existants dans le même quartier pour incrémentation du numéro de station
            $count = Entrepot::where('ville', $request->ville)
                           ->where('quartier', $request->quartier)
                           ->count();
        
            // Formatage du numéro de station avec 2 chiffres (01, 02, 03, ...)
            $numStation = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        
            // Génération de l'ID unique de l'entrepôt
            $entrepotUniqueId = "EN-{$villeCode}-{$quartierCode}-{$numStation}";
        
            // Hash du mot de passe
            $hashedPassword = Hash::make($request->password);
        
            // Création de l'entrepôt dans la base de données
            $entrepot = Entrepot::create([
                'nom_entrepot' => $request->nom_entrepot,
                'nom_proprietaire' => $request->nom_proprietaire,
                'ville' => $request->ville,
                'quartier' => $request->quartier,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'password' => $hashedPassword,
                'entrepot_unique_id' => $entrepotUniqueId,
            ]);
        
            return response()->json([
                'success' => true,
                'message' => 'Entrepôt ajouté avec succès',
                'entrepot' => $entrepot
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode store: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de l\'entrepôt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche les détails d'un entrepôt pour l'édition
     */
    public function edit($id)
    {
        try {
            $entrepot = Entrepot::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'entrepot' => $entrepot
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode edit: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des détails de l\'entrepôt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour un entrepôt existant
     */
    public function update(Request $request, $id)
    {
        try {
            $entrepot = Entrepot::findOrFail($id);
            
            // Validation des champs
            $validator = Validator::make($request->all(), [
                'nom_entrepot' => 'required|string|max:255',
                'nom_proprietaire' => 'required|string|max:255',
                'ville' => 'required|string|max:255',
                'quartier' => 'required|string|max:255',
                'telephone' => 'required|string|unique:entrepots,telephone,' . $id,
                'email' => 'required|email|unique:entrepots,email,' . $id,
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
                $request->ville !== $entrepot->ville || 
                $request->quartier !== $entrepot->quartier
            ) {
                // Même logique que dans la méthode store pour régénérer l'identifiant
    
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
    
                $codesVilles = [
                    'Douala' => 'DLA',
                    'Yaoundé' => 'YDE'
                ];
    
                $ville = $request->ville;
                $quartier = $request->quartier;
    
                $quartierCode = $quartiers[$ville][$quartier] ?? strtoupper(substr($quartier, 0, 3));
                $villeCode = $codesVilles[$ville] ?? strtoupper(substr($ville, 0, 3));
    
                $count = Entrepot::where('ville', $ville)
                               ->where('quartier', $quartier)
                               ->count();
    
                $numStation = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
                $entrepotUniqueId = "EN-{$villeCode}-{$quartierCode}-{$numStation}";
    
                $data['entrepot_unique_id'] = $entrepotUniqueId;
            }
    
            $entrepot->update($data);
    
            return response()->json([
                'success' => true,
                'message' => 'Entrepôt mis à jour avec succès',
                'entrepot' => $entrepot
            ]);
    
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode update: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'entrepôt: ' . $e->getMessage()
            ], 500);
        }
    }
    

    /**
     * Supprime un entrepôt
     */
    public function destroy($id)
    {
        try {
            $entrepot = Entrepot::findOrFail($id);
            $entrepot->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Entrepôt supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode destroy: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'entrepôt: ' . $e->getMessage()
            ], 500);
        }
    }
}
