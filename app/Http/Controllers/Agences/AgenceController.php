<?php

namespace App\Http\Controllers\Agences;

use App\Models\Agence;
use App\Models\UsersAgence;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AgenceController extends Controller
{
    /**
     * Affiche la vue principale de gestion des agences
     */
    public function index()
    {
        try {
            $agences = Agence::orderBy('created_at', 'desc')->get();
            return view('agences.index', compact('agences'));
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode index: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors du chargement de la page.');
        }
    }

    /**
     * Récupère la liste des agences (pour rafraîchissement AJAX)
     */
    public function getAgencesList()
    {
        try {
            $agences = Agence::orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'agences' => $agences
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode getAgencesList: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des agences: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les statistiques des agences (pour mise à jour AJAX)
     */
    public function getStats()
    {
        try {
            $totalAgences = Agence::count();
            $totalAgencesDouala = Agence::where('ville', 'Douala')->count();
            $totalAgencesYaounde = Agence::where('ville', 'Yaoundé')->count();
            $totalUsers =  UsersAgence::count();
            
            return response()->json([
                'total_agences' => $totalAgences,
                'total_douala' => $totalAgencesDouala,
                'total_yaounde' => $totalAgencesYaounde,
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
     * Récupère les détails d'une agence pour l'édition
     */
    public function getAgenceDetails($id)
    {
        try {
            $agence = Agence::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'agence' => $agence
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode getAgenceDetails: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des détails de l\'agence: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enregistre une nouvelle agence
     */
    public function store(Request $request)
    {
        try {
            // Validation des entrées
            $validator = Validator::make($request->all(), [
                'nom_agence' => 'required|string|max:255',
                'nom_proprietaire' => 'required|string|max:255',
                'ville' => 'required|string|max:255',
                'quartier' => 'required|string|max:255',
                'telephone' => 'required|string|unique:agences',
                'email' => 'required|email|unique:agences',
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
        
            // Nombre d'agences existantes dans le même quartier pour incrémentation du numéro de station
            $count = Agence::where('ville', $request->ville)
                           ->where('quartier', $request->quartier)
                           ->count();
        
            // Formatage du numéro de station avec 2 chiffres (01, 02, 03, ...)
            $numStation = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        
            // Génération de l'ID unique de l'agence
            $agenceUniqueId = "AG-{$villeCode}-{$quartierCode}-{$numStation}";
        
            // Hash du mot de passe
            $hashedPassword = Hash::make($request->password);
        
            // Création de l'agence dans la base de données
            $agence = Agence::create([
                'nom_agence' => $request->nom_agence,
                'nom_proprietaire' => $request->nom_proprietaire,
                'ville' => $request->ville,
                'quartier' => $request->quartier,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'password' => $hashedPassword,
                'agence_unique_id' => $agenceUniqueId,
            ]);
        
            return response()->json([
                'success' => true,
                'message' => 'Agence ajoutée avec succès',
                'agence' => $agence
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode store: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de l\'agence: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Affiche les détails d'une agence pour l'édition
     */
    public function edit($id)
    {
        try {
            $agence = Agence::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'agence' => $agence
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode edit: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des détails de l\'agence: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour une agence existante
     */
    public function update(Request $request, $id)
    {
        try {
            $agence = Agence::findOrFail($id);
            
            // Validation des champs
            $validator = Validator::make($request->all(), [
                'nom_agence' => 'required|string|max:255',
                'nom_proprietaire' => 'required|string|max:255',
                'ville' => 'required|string|max:255',
                'quartier' => 'required|string|max:255',
                'telephone' => 'required|string|unique:agences,telephone,' . $id,
                'email' => 'required|email|unique:agences,email,' . $id,
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
                $request->ville !== $agence->ville || 
                $request->quartier !== $agence->quartier
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
    
                $count = Agence::where('ville', $ville)
                               ->where('quartier', $quartier)
                               ->count();
    
                $numStation = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
                $agenceUniqueId = "AG-{$villeCode}-{$quartierCode}-{$numStation}";
    
                $data['agence_unique_id'] = $agenceUniqueId;
            }
    
            $agence->update($data);
    
            return response()->json([
                'success' => true,
                'message' => 'Agence mise à jour avec succès',
                'agence' => $agence
            ]);
    
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode update: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'agence: ' . $e->getMessage()
            ], 500);
        }
    }
    

    /**
     * Supprime une agence
     */
    public function destroy($id)
    {
        try {
            $agence = Agence::findOrFail($id);
            $agence->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Agence supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans la méthode destroy: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'agence: ' . $e->getMessage()
            ], 500);
        }
    }
}