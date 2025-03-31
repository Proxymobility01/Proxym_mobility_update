<?php

namespace App\Http\Controllers\Chauffeurs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ValidatedUser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ChauffeurController extends Controller
{
    /**
     * Display a listing of the chauffeurs.
     */
    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            $query = ValidatedUser::query();
            
            // Filtrage par terme de recherche
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('user_unique_id', 'like', "%{$search}%");
                });
            }
            
            // Filtrage par statut
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }
            
            $chauffeurs = $query->get();
            
            return response()->json($chauffeurs);
        }
        
        // Si ce n'est pas une requête AJAX, retourner la vue
        return view('chauffeurs.index');
    }

    /**
     * Store a newly created chauffeur in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:validated_users,email',
            'phone' => 'required|string|unique:validated_users,phone',
            'password' => 'required|string|min:8',
            'numero_cni' => 'required|string|unique:validated_users,numero_cni',
            'photo_cni_recto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'photo_cni_verso' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $chauffeur = new ValidatedUser();
        $chauffeur->user_unique_id = 'CH'.Str::random(8);
        $chauffeur->nom = $request->nom;
        $chauffeur->prenom = $request->prenom;
        $chauffeur->email = $request->email;
        $chauffeur->phone = $request->phone;
        $chauffeur->password = $request->password; // Le hachage est géré dans le modèle
        $chauffeur->numero_cni = $request->numero_cni;
        $chauffeur->status = 'en attente';
        $chauffeur->token = Str::random(60);
        $chauffeur->verification_code = rand(100000, 999999);
        $chauffeur->verification_code_sent_at = now();

        // Gestion des fichiers (photos)
        if ($request->hasFile('photo_cni_recto')) {
            $filename = 'cni_recto_' . time() . '.' . $request->photo_cni_recto->extension();
            $request->photo_cni_recto->storeAs('public/chauffeurs/cni', $filename);
            $chauffeur->photo_cni_recto = $filename;
        }

        if ($request->hasFile('photo_cni_verso')) {
            $filename = 'cni_verso_' . time() . '.' . $request->photo_cni_verso->extension();
            $request->photo_cni_verso->storeAs('public/chauffeurs/cni', $filename);
            $chauffeur->photo_cni_verso = $filename;
        }

        if ($request->hasFile('photo')) {
            $filename = 'photo_' . time() . '.' . $request->photo->extension();
            $request->photo->storeAs('public/chauffeurs/photos', $filename);
            $chauffeur->photo = $filename;
        }

        $chauffeur->save();

        return response()->json($chauffeur, 201);
    }

    /**
     * Display the specified chauffeur for editing.
     */
    public function edit($id)
    {
        $chauffeur = ValidatedUser::findOrFail($id);
        return response()->json($chauffeur);
    }

    /**
     * Update the specified chauffeur in storage.
     */
    public function update(Request $request, $id)
    {
        $chauffeur = ValidatedUser::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:validated_users,email,' . $id,
            'phone' => 'required|string|unique:validated_users,phone,' . $id,
            'numero_cni' => 'required|string|unique:validated_users,numero_cni,' . $id,
            'photo_cni_recto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'photo_cni_verso' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $chauffeur->nom = $request->nom;
        $chauffeur->prenom = $request->prenom;
        $chauffeur->email = $request->email;
        $chauffeur->phone = $request->phone;
        $chauffeur->numero_cni = $request->numero_cni;

        // Mise à jour conditionnelle du mot de passe
        if ($request->filled('password')) {
            $chauffeur->password = $request->password;
        }

        // Gestion des fichiers (photos)
        if ($request->hasFile('photo_cni_recto')) {
            // Supprimer l'ancien fichier si existant
            if ($chauffeur->photo_cni_recto) {
                Storage::delete('public/chauffeurs/cni/' . $chauffeur->photo_cni_recto);
            }
            
            $filename = 'cni_recto_' . time() . '.' . $request->photo_cni_recto->extension();
            $request->photo_cni_recto->storeAs('public/chauffeurs/cni', $filename);
            $chauffeur->photo_cni_recto = $filename;
        }

        if ($request->hasFile('photo_cni_verso')) {
            if ($chauffeur->photo_cni_verso) {
                Storage::delete('public/chauffeurs/cni/' . $chauffeur->photo_cni_verso);
            }
            
            $filename = 'cni_verso_' . time() . '.' . $request->photo_cni_verso->extension();
            $request->photo_cni_verso->storeAs('public/chauffeurs/cni', $filename);
            $chauffeur->photo_cni_verso = $filename;
        }

        if ($request->hasFile('photo')) {
            if ($chauffeur->photo) {
                Storage::delete('public/chauffeurs/photos/' . $chauffeur->photo);
            }
            
            $filename = 'photo_' . time() . '.' . $request->photo->extension();
            $request->photo->storeAs('public/chauffeurs/photos', $filename);
            $chauffeur->photo = $filename;
        }

        $chauffeur->save();

        return response()->json($chauffeur);
    }

    /**
     * Remove the specified chauffeur from storage.
     */
    public function destroy($id)
    {
        $chauffeur = ValidatedUser::findOrFail($id);
        
        // Supprimer les fichiers associés
        if ($chauffeur->photo_cni_recto) {
            Storage::delete('public/chauffeurs/cni/' . $chauffeur->photo_cni_recto);
        }
        
        if ($chauffeur->photo_cni_verso) {
            Storage::delete('public/chauffeurs/cni/' . $chauffeur->photo_cni_verso);
        }
        
        if ($chauffeur->photo) {
            Storage::delete('public/chauffeurs/photos/' . $chauffeur->photo);
        }
        
        $chauffeur->delete();
        
        return response()->json(['message' => 'Chauffeur supprimé avec succès']);
    }

    /**
     * Valider un chauffeur.
     */
    public function validate(Request $request, $id)
    {
        $chauffeur = ValidatedUser::findOrFail($id);
        $chauffeur->status = 'validé';
        $chauffeur->completed_at = now();
        $chauffeur->save();
        
        return response()->json($chauffeur);
    }

    /**
     * Rejeter un chauffeur.
     */
    public function reject(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'motif_rejet' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $chauffeur = ValidatedUser::findOrFail($id);
        $chauffeur->status = 'rejeté';
        // Si vous souhaitez enregistrer le motif du rejet, ajoutez une colonne à votre table
        // $chauffeur->motif_rejet = $request->motif_rejet;
        $chauffeur->save();
        
        return response()->json($chauffeur);
    }
}