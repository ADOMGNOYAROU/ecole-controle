<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Eleve;
use App\Models\Parent as ParentModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\UserAccountCreated;

class UserAccountController extends Controller
{
    /**
     * Affiche le formulaire de création de comptes
     */
    public function index()
    {
        try {
            $eleves = Eleve::with(['user', 'classe'])->get();
            
            // Récupérer les parents séparément pour éviter l'erreur si la relation n'existe pas
            $parents = \App\Models\ParentModel::with('user')->get();
            
            return view('accounts.index', compact('eleves', 'parents'));
        } catch (\Exception $e) {
            // Si la relation parents n'existe pas, charger sans cette relation
            $eleves = Eleve::with(['user', 'classe'])->get();
            $parents = collect();
            
            return view('accounts.index', compact('eleves', 'parents'));
        }
    }

    /**
     * Crée manuellement un compte utilisateur
     */
    public function createManual(Request $request)
    {
        $request->validate([
            'type' => 'required|in:eleve,parent',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'nullable|string|min:8',
            'send_email' => 'boolean',
            'eleve_ids' => 'nullable|array',
            'eleve_ids.*' => 'exists:eleves,id',
            'eleve_id' => 'nullable|exists:eleves,id',
        ]);

        try {
            // Générer le mot de passe si non fourni
            $password = $request->password ?: $this->generatePassword();

            // Créer le compte utilisateur
            $user = User::create([
                'name' => $request->prenom . ' ' . $request->nom,
                'email' => $request->email,
                'password' => Hash::make($password),
                'role' => $request->type,
                'email_verified_at' => now(),
            ]);

            $accountInfo = [
                'type' => $request->type === 'eleve' ? 'Élève' : 'Parent',
                'name' => $request->prenom . ' ' . $request->nom,
                'email' => $request->email,
                'password' => $password,
            ];

            if ($request->type === 'eleve') {
                // Vérifier si un ID d'élève est fourni (sélection depuis la liste)
                if ($request->filled('eleve_id')) {
                    $eleve = Eleve::find($request->eleve_id);
                    if ($eleve && !$eleve->user_id) {
                        // Lier l'utilisateur à l'élève existant
                        $eleve->update([
                            'user_id' => $user->id,
                            'classe_id' => $request->classe_id,
                            'statut' => 'actif',
                        ]);
                    }
                } else {
                    // Vérifier si l'élève existe déjà (basé sur nom et prénom)
                    $existingEleve = Eleve::where('nom', $request->nom)
                        ->where('prenom', $request->prenom)
                        ->first();
                    
                    if ($existingEleve && !$existingEleve->user_id) {
                        // Lier l'utilisateur à l'élève existant
                        $existingEleve->update([
                            'user_id' => $user->id,
                            'classe_id' => $request->classe_id,
                            'matricule' => $existingEleve->matricule ?: 'ELE' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                            'statut' => 'actif',
                        ]);
                        $eleve = $existingEleve;
                    } else {
                        // Créer un nouvel élève
                        $eleve = Eleve::create([
                            'user_id' => $user->id,
                            'nom' => $request->nom,
                            'prenom' => $request->prenom,
                            'classe_id' => $request->classe_id,
                            'matricule' => 'ELE' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                            'statut' => 'actif',
                        ]);
                    }
                }
                $accountInfo['classe'] = $eleve->classe->nom ?? 'Non assigné';
            } else {
                // Créer le parent
                $parent = \App\Models\ParentModel::create([
                    'user_id' => $user->id,
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'email' => $request->email,
                    'telephone' => $request->telephone,
                    'profession' => $request->profession,
                    'statut' => 'actif',
                ]);

                // Associer les élèves au parent
                if ($request->filled('eleve_ids')) {
                    $parent->eleves()->attach($request->eleve_ids);
                    
                    // Récupérer les noms des enfants associés
                    $enfantsAssocies = Eleve::whereIn('id', $request->eleve_ids)->get();
                    $enfantsList = $enfantsAssocies->map(function($eleve) {
                        return $eleve->prenom . ' ' . $eleve->nom . ' (' . ($eleve->classe->nom ?? 'Non assigné') . ')';
                    })->implode(', ');
                    $accountInfo['enfants'] = $enfantsList;
                } else {
                    $accountInfo['enfants'] = 'Aucun enfant assigné';
                }
            }

            // Envoyer l'email si demandé
            $emailSent = false;
            if ($request->send_email) {
                try {
                    Mail::to($request->email)->send(new UserAccountCreated($accountInfo));
                    $emailSent = true;
                } catch (\Exception $e) {
                    $emailSent = false;
                }
            }

            $accountInfo['email_sent'] = $emailSent;

            return redirect()->route('accounts.index')
                ->with('success', 'Compte créé avec succès!')
                ->with('account_created', $accountInfo);

        } catch (\Exception $e) {
            return redirect()->route('accounts.index')
                ->with('error', 'Erreur lors de la création du compte: ' . $e->getMessage());
        }
    }

    /**
     * Génère des comptes pour les élèves et parents
     */
    public function generateAccounts(Request $request)
    {
        $request->validate([
            'target_type' => 'required|in:eleves,parents,all',
            'send_email' => 'boolean'
        ]);

        $accountsCreated = [];
        $errors = [];

        try {
            if ($request->target_type === 'eleves' || $request->target_type === 'all') {
                $eleveAccounts = $this->generateEleveAccounts($request->send_email);
                $accountsCreated = array_merge($accountsCreated, $eleveAccounts['created']);
                $errors = array_merge($errors, $eleveAccounts['errors']);
            }

            if ($request->target_type === 'parents' || $request->target_type === 'all') {
                $parentAccounts = $this->generateParentAccounts($request->send_email);
                $accountsCreated = array_merge($accountsCreated, $parentAccounts['created']);
                $errors = array_merge($errors, $parentAccounts['errors']);
            }

            return redirect()->route('accounts.index')
                ->with('success', count($accountsCreated) . ' comptes créés avec succès!')
                ->with('accounts', $accountsCreated)
                ->with('errors', $errors);

        } catch (\Exception $e) {
            return redirect()->route('accounts.index')
                ->with('error', 'Erreur lors de la création des comptes: ' . $e->getMessage());
        }
    }

    /**
     * Génère des comptes pour les élèves
     */
    private function generateEleveAccounts($sendEmail = false)
    {
        $created = [];
        $errors = [];

        $eleves = Eleve::whereDoesntHave('user')->get();

        foreach ($eleves as $eleve) {
            try {
                $email = $this->generateEmail($eleve->prenom, $eleve->nom, 'eleve');
                $password = $this->generatePassword();

                // Créer le compte utilisateur
                $user = User::create([
                    'name' => $eleve->prenom . ' ' . $eleve->nom,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => 'eleve',
                    'email_verified_at' => now(),
                ]);

                // Associer l'utilisateur à l'élève
                $eleve->user_id = $user->id;
                $eleve->save();

                $accountInfo = [
                    'type' => 'Élève',
                    'name' => $eleve->prenom . ' ' . $eleve->nom,
                    'email' => $email,
                    'password' => $password,
                    'classe' => $eleve->classe->nom ?? 'Non assigné'
                ];

                if ($sendEmail) {
                    try {
                        Mail::to($email)->send(new UserAccountCreated($accountInfo));
                        $accountInfo['email_sent'] = true;
                    } catch (\Exception $e) {
                        $accountInfo['email_sent'] = false;
                        $accountInfo['email_error'] = $e->getMessage();
                    }
                } else {
                    $accountInfo['email_sent'] = false;
                }

                $created[] = $accountInfo;

            } catch (\Exception $e) {
                $errors[] = [
                    'type' => 'Élève',
                    'name' => $eleve->prenom . ' ' . $eleve->nom,
                    'error' => $e->getMessage()
                ];
            }
        }

        return ['created' => $created, 'errors' => $errors];
    }

    /**
     * Génère des comptes pour les parents
     */
    private function generateParentAccounts($sendEmail = false)
    {
        $created = [];
        $errors = [];

        $parents = \App\Models\ParentModel::whereDoesntHave('user')->get();

        foreach ($parents as $parent) {
            try {
                $email = $this->generateEmail($parent->prenom, $parent->nom, 'parent');
                $password = $this->generatePassword();

                // Créer le compte utilisateur
                $user = User::create([
                    'name' => $parent->prenom . ' ' . $parent->nom,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'role' => 'parent',
                    'email_verified_at' => now(),
                ]);

                // Associer l'utilisateur au parent
                $parent->user_id = $user->id;
                $parent->save();

                // Récupérer les enfants du parent
                $enfants = $parent->eleves->pluck('prenom', 'nom')->toArray();
                $enfantsList = implode(', ', array_map(function($prenom, $nom) {
                    return "$prenom $nom";
                }, $enfants, array_keys($enfants)));

                $accountInfo = [
                    'type' => 'Parent',
                    'name' => $parent->prenom . ' ' . $parent->nom,
                    'email' => $email,
                    'password' => $password,
                    'enfants' => $enfantsList ?: 'Aucun enfant assigné'
                ];

                if ($sendEmail) {
                    try {
                        Mail::to($email)->send(new UserAccountCreated($accountInfo));
                        $accountInfo['email_sent'] = true;
                    } catch (\Exception $e) {
                        $accountInfo['email_sent'] = false;
                        $accountInfo['email_error'] = $e->getMessage();
                    }
                } else {
                    $accountInfo['email_sent'] = false;
                }

                $created[] = $accountInfo;

            } catch (\Exception $e) {
                $errors[] = [
                    'type' => 'Parent',
                    'name' => $parent->prenom . ' ' . $parent->nom,
                    'error' => $e->getMessage()
                ];
            }
        }

        return ['created' => $created, 'errors' => $errors];
    }

    /**
     * Génère un email unique
     */
    private function generateEmail($prenom, $nom, $type)
    {
        $baseEmail = strtolower(Str::slug($prenom) . '.' . Str::slug($nom)) . '@ecole.school';
        $email = $baseEmail;
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = strtolower(Str::slug($prenom) . '.' . Str::slug($nom)) . $counter . '@ecole.school';
            $counter++;
        }

        return $email;
    }

    /**
     * Génère un mot de passe sécurisé
     */
    private function generatePassword()
    {
        return Str::random(12) . '1@';
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur
     */
    public function resetPassword($userId)
    {
        $user = User::findOrFail($userId);
        $newPassword = $this->generatePassword();
        
        $user->password = Hash::make($newPassword);
        $user->save();

        return back()->with('success', 'Mot de passe réinitialisé: ' . $newPassword);
    }

    /**
     * Exporte les comptes créés en CSV
     */
    public function exportAccounts(Request $request)
    {
        $accounts = session('accounts', []);
        
        if (empty($accounts)) {
            return redirect()->route('accounts.index')
                ->with('error', 'Aucun compte à exporter');
        }

        $filename = 'comptes_' . date('Y-m-d_H-i-s') . '.csv';
        $handle = fopen($filename, 'w');
        
        // En-tête CSV
        fputcsv($handle, ['Type', 'Nom', 'Email', 'Mot de passe', 'Classe/Enfants', 'Email envoyé']);

        foreach ($accounts as $account) {
            fputcsv($handle, [
                $account['type'],
                $account['name'],
                $account['email'],
                $account['password'],
                $account['classe'] ?? $account['enfants'] ?? '',
                $account['email_sent'] ? 'Oui' : 'Non'
            ]);
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }
}
