<?php
namespace App\Http\Controllers\Motos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Moto;
use App\Models\MotosValide;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class MotoController extends Controller
{
    // Liste des motos (support AJAX)
    public function index(Request $request)
    {
        try {
            $search = $request->search;
            $status = $request->status;
            $motos = collect();
    
            if ($request->filled('status')) {
                if ($status == 'validé') {
                    // Si on filtre sur "validé", récupérer uniquement depuis la table motos_valides
                    $motos = MotosValide::when($search, function($query, $search) {
                        return $query->where(function($q) use ($search) {
                            $q->where('model', 'LIKE', '%' . $search . '%')
                              ->orWhere('vin', 'LIKE', '%' . $search . '%')
                              ->orWhere('gps_imei', 'LIKE', '%' . $search . '%');
                        });
                    })->get()->map(function ($moto) {
                        $moto->statut = 'validé';
                        return $moto;
                    });
                } else {
                    // Pour les autres statuts (par exemple "en attente" ou "rejeté"), récupérer depuis la table motos
                    $motos = Moto::active()
                        ->byStatus($status)
                        ->when($search, function($query, $search) {
                            return $query->where(function($q) use ($search) {
                                $q->where('model', 'LIKE', '%' . $search . '%')
                                  ->orWhere('vin', 'LIKE', '%' . $search . '%')
                                  ->orWhere('gps_imei', 'LIKE', '%' . $search . '%');
                            });
                        })->get();
                }
            } else {
                // Aucun filtre de statut : récupérer les motos de la table motos...
                $motos = Moto::active()
                        ->when($search, function($query, $search) {
                            return $query->where(function($q) use ($search) {
                                $q->where('model', 'LIKE', '%' . $search . '%')
                                  ->orWhere('vin', 'LIKE', '%' . $search . '%')
                                  ->orWhere('gps_imei', 'LIKE', '%' . $search . '%');
                            });
                        })->get();
                // ...et les motos validées
                $motosValides = MotosValide::when($search, function($query, $search) {
                    return $query->where(function($q) use ($search) {
                        $q->where('model', 'LIKE', '%' . $search . '%')
                          ->orWhere('vin', 'LIKE', '%' . $search . '%')
                          ->orWhere('gps_imei', 'LIKE', '%' . $search . '%');
                    });
                })->get()->map(function ($moto) {
                    $moto->statut = 'validé';
                    return $moto;
                });
                $motos = $motos->merge($motosValides);
            }
    
            if ($request->wantsJson()) {
                return response()->json($motos);
            }
            return view('motos.index', compact('motos'));
        } catch (Exception $e) {
            Log::error("Erreur lors de la récupération des motos : " . $e->getMessage());
            return response()->json(['error' => 'Erreur de récupération'], 500);
        }
    }
    
    

    // Ajouter une moto (support AJAX)
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'vin' => 'required|unique:motos,vin',
                'model' => 'required',
                'gps_imei' => 'required',
            ]);

            // Génération de l'ID unique
            $today = Carbon::now()->format('Ymd');
            $lastMoto = Moto::where('moto_unique_id', 'like', 'PXM' . $today . '%')
                            ->orderBy('moto_unique_id', 'desc')
                            ->first();

            $counter = $lastMoto 
                ? (int)substr($lastMoto->moto_unique_id, -3) + 1 
                : 1;

            $uniqueId = 'PXM' . $today . str_pad($counter, 3, '0', STR_PAD_LEFT);

            $moto = Moto::create([
                'vin' => $validatedData['vin'],
                'model' => $validatedData['model'],
                'gps_imei' => $validatedData['gps_imei'],
                'moto_unique_id' => $uniqueId,
                'statut' => 'en attente'
            ]);

            return response()->json($moto, 201);
        } catch (Exception $e) {
            Log::error("Erreur lors de l'ajout de la moto : " . $e->getMessage());
            return response()->json(['error' => 'Erreur d\'ajout'], 500);
        }
    }

    // Mise à jour d'une moto (support AJAX)
    public function update(Request $request, $id)
    {
        try {
            $moto = Moto::findOrFail($id);

            $validatedData = $request->validate([
                'model' => 'required',
                'gps_imei' => 'required',
            ]);

            $moto->update([
                'model' => $validatedData['model'],
                'gps_imei' => $validatedData['gps_imei'],
                'statut' => 'en attente'
            ]);

            return response()->json($moto);
        } catch (Exception $e) {
            Log::error("Erreur lors de la mise à jour de la moto : " . $e->getMessage());
            return response()->json(['error' => 'Erreur de mise à jour'], 500);
        }
    }

    // Valider une moto
    public function validate(Request $request, $id)
    {
        try {
            $moto = Moto::findOrFail($id);

            $validatedData = $request->validate([
                'assurance' => 'required|string',
                'permis' => 'required|string',
            ]);

            // Créer une moto validée
            $motoValidee = MotosValide::create([
                'vin' => $moto->vin,
                'moto_unique_id' => $moto->moto_unique_id,
                'model' => $moto->model,
                'gps_imei' => $moto->gps_imei,
                'assurance' => $validatedData['assurance'],
                'permis' => $validatedData['permis'],
            ]);

            // Marquer comme validée et supprimer
            $moto->statut = 'validé';
            $moto->delete();

            return response()->json($motoValidee);
        } catch (Exception $e) {
            Log::error("Erreur lors de la validation de la moto : " . $e->getMessage());
            return response()->json(['error' => 'Erreur de validation'], 500);
        }
    }

    // Rejeter une moto
    public function reject($id)
    {
        try {
            $moto = Moto::findOrFail($id);
            $moto->statut = 'rejeté';
            $moto->save();

            return response()->json($moto);
        } catch (Exception $e) {
            Log::error("Erreur lors du rejet de la moto : " . $e->getMessage());
            return response()->json(['error' => 'Erreur de rejet'], 500);
        }
    }

    // Supprimer une moto
    public function destroy($id)
    {
        try {
            $moto = Moto::findOrFail($id);
            $moto->delete();

            return response()->json(['message' => 'Moto supprimée']);
        } catch (Exception $e) {
            Log::error("Erreur lors de la suppression de la moto : " . $e->getMessage());
            return response()->json(['error' => 'Erreur de suppression'], 500);
        }
    }
}