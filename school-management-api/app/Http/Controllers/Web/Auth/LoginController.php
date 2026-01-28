<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Traiter la tentative de connexion
     */
    public function login(Request $request)
    {
        // Log de débogage
        \Log::info('Tentative de connexion', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'method' => $request->method()
        ]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        \Log::info('Credentials validés', ['email' => $credentials['email']]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            \Log::info('Authentification réussie', ['user_id' => Auth::id()]);
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        \Log::warning('Échec de l\'authentification', ['email' => $credentials['email']]);

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    /**
     * Déconnexion de l'utilisateur
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
