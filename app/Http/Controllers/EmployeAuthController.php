<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.employe_login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:employes,email',
            'password' => 'required|string',
        ]);

        $employe = Employe::where('email', $request->email)->first();

        if ($employe && Hash::check($request->password, $employe->password)) {
            session(['employe' => $employe]);
            session(['auth_token' => Str::random(60)]);

            return redirect()->route('dashboard');
        }

        return back()->withErrors(['email' => 'Identifiants incorrects'])->withInput();
    }

    public function logout()
    {
        session()->forget('employe');
        session()->forget('auth_token');

        return redirect()->route('login');
    }
}
