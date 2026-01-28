<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Store the settings.
     */
    public function store(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'school_address' => 'nullable|string',
            'school_phone' => 'nullable|string',
            'school_email' => 'nullable|email',
        ]);

        // Store settings in session or config
        session(['settings' => $request->only(['school_name', 'school_address', 'school_phone', 'school_email'])]);

        return redirect()->route('settings.index')
            ->with('success', 'Paramètres enregistrés avec succès.');
    }
}
