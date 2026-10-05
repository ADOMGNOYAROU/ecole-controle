<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JournalAuditeur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = strtolower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => ['Trop de tentatives. Réessayez dans '.RateLimiter::availableIn($throttleKey).' secondes.'],
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey);

            app(JournalAuditeur::class)->enregistrer('connexion_echouee', $user, ['email_saisi' => $request->email, 'canal' => 'application mobile'], ecoleId: $user?->ecole_id, libelle: $request->email);

            throw ValidationException::withMessages(['email' => ['Identifiants invalides.']]);
        }

        RateLimiter::clear($throttleKey);

        app(JournalAuditeur::class)->enregistrer('connexion', $user, ['canal' => 'application mobile'], user: $user);
        $user->marquerConnexion();

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['eleve.classe', 'enseignant', 'tuteur']);

        return response()->json(['success' => true, 'user' => $user]);
    }
}
