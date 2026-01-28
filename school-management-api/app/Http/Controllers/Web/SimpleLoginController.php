<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimpleLoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $request->session()->regenerate();
            
            // Redirection selon le rôle
            $user = Auth::user();
            switch ($user->role) {
                case 'admin':
                    return redirect('/dashboard');
                case 'enseignant':
                    return redirect('/dashboard-enseignant');
                case 'eleve':
                    return redirect('/dashboard-eleve');
                case 'parent':
                    return redirect('/dashboard-parent');
                default:
                    return redirect('/dashboard');
            }
        }
        
        return back()->withErrors([
            'email' => 'Identifiants incorrects',
        ]);
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
