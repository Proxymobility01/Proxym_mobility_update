<?php

namespace App\Http\Controllers\Employes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employe;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployesExport;

class EmployeController extends Controller
{
    /**
     * Display the employee management page
     */
    public function index()
    {
        try {
            return view('employes.manage-employes');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Get all employees for the data table
     */
    public function list(Request $request)
    {
        try {
            $query = Employe::query();
            
            // Search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }
            
            // Status filter
            if ($request->has('status') && !empty($request->status)) {
                if ($request->status === 'actif') {
                    $query->whereNull('deleted_at');
                } elseif ($request->status === 'inactif') {
                    $query->whereNotNull('deleted_at');
                }
            }
            
            // Include soft deleted employees to show inactive ones
            $employes = $query->withTrashed()->get();
            
            if ($request->expectsJson()) {
                return response()->json($employes);
            }
            
            return response()->json($employes);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created employee
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:employes,email',
                'phone' => 'required|string|max:15|unique:employes,phone',
                'password' => 'required|string|min:8|confirmed',
            ]);
            
            // Create employee
            $employe = Employe::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
            ]);
            
            if ($request->expectsJson()) {
                return response()->json($employe, 201);
            }
            
            return redirect()->route('employe.index')->with('success', 'Employé ajouté avec succès.');
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            
            return redirect()->back()->withInput()->withErrors('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified employee
     */
    public function show($id)
    {
        try {
            $employe = Employe::withTrashed()->findOrFail($id);
            
            return response()->json($employe);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Employé non trouvé.'], 404);
        }
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit($id)
    {
        try {
            $employe = Employe::withTrashed()->findOrFail($id);
            
            return response()->json($employe);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Employé non trouvé.'], 404);
        }
    }

    /**
     * Update the specified employee
     */
    public function update(Request $request, $id)
    {
        try {
            $employe = Employe::findOrFail($id);
            
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|email|unique:employes,email,' . $id,
                'phone' => 'required|string|max:15|unique:employes,phone,' . $id,
                'password' => 'nullable|string|min:8|confirmed',
            ]);
            
            // Update employee data
            $employe->nom = $validated['nom'];
            $employe->prenom = $validated['prenom'];
            $employe->email = $validated['email'];
            $employe->phone = $validated['phone'];
            
            if (!empty($validated['password'])) {
                $employe->password = Hash::make($validated['password']);
            }
            
            $employe->save();
            
            if ($request->expectsJson()) {
                return response()->json($employe);
            }
            
            return redirect()->route('employe.index')->with('success', 'Employé mis à jour avec succès.');
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            
            return redirect()->back()->withInput()->withErrors('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete the specified employee
     */
    public function destroy($id)
    {
        try {
            $employe = Employe::findOrFail($id);
            $employe->delete(); // Soft delete
            
            return response()->json(['success' => true, 'message' => 'Employé supprimé avec succès.']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Restore a soft-deleted employee
     */
    public function restore($id)
    {
        try {
            $employe = Employe::withTrashed()->findOrFail($id);
            $employe->restore();
            
            return response()->json(['success' => true, 'message' => 'Employé restauré avec succès.']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Export employees to Excel
     */
    public function export()
    {
        try {
            return Excel::download(new EmployesExport, 'liste_employes.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('employes.login-employe');
    }

    /**
     * Authenticate employee (login)
     */
    public function login(Request $request)
    {
        // Validate data
        $request->validate([
            'login' => 'required|string',  // Login can be email or phone
            'password' => 'required|string',
        ]);
    
        // Determine if 'login' field is an email or a phone number
        $identifier = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
    
        // Find employee in database
        $employe = Employe::where($identifier, $request->login)->first();
    
        // Check password with Hash::check
        if ($employe && Hash::check($request->password, $employe->password)) {
            // Authentication successful
            Auth::login($employe);
    
            // Regenerate session to avoid session fixation
            $request->session()->regenerate();
    
            // Redirect after successful login
            return redirect()->intended(route('dashboard'))->with('success', 'Connexion réussie');
        }
    
        // If authentication fails
        return back()->withErrors([
            'login' => 'Échec de l\'authentification. Vérifiez vos identifiants.',
        ]);
    }

    /**
     * Logout employee
     */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('employe.login')->with('success', 'Déconnexion réussie.');
    }
}