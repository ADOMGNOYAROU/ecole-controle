# Backend — École Manager

Refonte complète du backend Laravel de gestion scolaire (juin 2026), puis transformation en
plateforme **SaaS multi-écoles** adaptée à la réalité togolaise (juin 2026). Voir l'historique du
projet pour le contexte de l'audit initial — ce document décrit l'état actuel.

## Stack

- Laravel 12, PHP 8.3, Sanctum (API tokens), SQLite (dev)
- `barryvdh/laravel-dompdf` pour la génération des bulletins PDF

## Modèle SaaS multi-écoles

**Architecture** : base de données partagée, schéma partagé — chaque table métier porte une
colonne `ecole_id`. Le trait `App\Models\Concerns\BelongsToEcole` (global scope Eloquent
`EcoleScope`) filtre automatiquement chaque requête par l'école de l'utilisateur connecté et
assigne `ecole_id` à la création. **Exception : le modèle `User` n'utilise pas ce trait** —
`Auth::user()` interrogerait `User` pour se résoudre, ce qui appellerait `EcoleScope::apply()`,
qui appellerait `Auth::user()` à nouveau → boucle infinie. Le filtrage par école sur `User` se
fait donc explicitement où nécessaire (`UserAccountController::index()`,
`AnnonceController::notifierDestinataires()`).

**Modèle économique** : freemium, facturation trimestrielle, adapté aux usages togolais (Mobile
Money Flooz/TMoney, cycle de trésorerie des écoles par trimestre).
- **Gratuit** (illimité) : classes, élèves, enseignants, matières, notes, présences, emploi du
  temps — le cœur de gestion d'une école.
- **Premium** (15 000 FCFA / trimestre, un seul plan) : bulletins PDF, paiements de scolarité,
  annonces/notifications, espaces self-service élève/parent, gestion des comptes utilisateurs.
  Gaté par le middleware `premium` (`App\Http\Middleware\EnsurePremium`).
- **Essai** : 30 jours d'accès Premium complet à l'inscription (`/inscription`), retour
  automatique au plan Gratuit si aucun abonnement payé n'est actif à l'expiration.
- **Paiement** : pas d'API de paiement branchée pour l'instant. `Facture` est créée en
  `en_attente` ; le super-admin confirme manuellement la réception du paiement Mobile Money
  (`SuperAdmin\FactureController::confirmer` → `Facture::confirmerPaiement()`), ce qui active un
  `Abonnement`. Les champs `methode_paiement`/`reference_transaction` sont prêts à être remplis
  automatiquement par un futur webhook (CinetPay/PayDunya/Mobile Money) sans changer le schéma.

| Modèle | Rôle |
|---|---|
| `Ecole` | Le tenant. `statut` (essai/actif/suspendu/expire), `plan` (gratuit/premium), `trial_ends_at`. `aAccesPremium()` calcule l'accès réel (essai en cours OU abonnement actif). |
| `Abonnement` | Période Premium active pour une école (`date_debut`/`date_fin`, montant). |
| `Facture` | Demande de paiement (`en_attente`/`payee`/`en_retard`/`annulee`), confirmation manuelle tracée (`confirmee_par_id`). |
| `User` | Authentification, rôle (`super_admin`, `admin`, `enseignant`, `eleve`, `parent`). `super_admin` a `ecole_id` nul et gère la plateforme entière via `/super-admin/*`. |
| `AnneeScolaire`, `Trimestre` | Référentiel temporel pour notes, présences, bulletins |
| `Classe` | + professeur principal (`enseignant_principal_id`) |
| `Matiere` | + coefficient par défaut (utilisé pour la moyenne générale) |
| `Enseignant` | Lié à un `User` (optionnel), affecté à des matières × classes via `enseignant_matiere_classe` |
| `Eleve` | Fusion des anciens `Eleve`/`Etudiant`. Lié à un `User` optionnel (portail élève) |
| `Tuteur` | Parent/tuteur (nommé `Tuteur` et non `Parent` — `parent` est un mot réservé en PHP) |
| `Note`, `Presence` | Rattachées à un `Trimestre` |
| `CreneauHoraire` | Emploi du temps (classe × matière × jour × heure) |
| `Paiement` | Scolarité, échéances, statut |
| `Annonce`, `Notification` | Communication admin/enseignant → parents/élèves |
| `Bulletin` | Métadonnées du bulletin généré (moyenne, rang, chemin du PDF) |
| `Responsabilite` | Rôles annexes des enseignants (surveillance, commission...) — conservé tel quel de l'existant |

## Sécurité

- Toutes les routes (web et API) exigent une authentification ; les routes API utilisent
  `auth:sanctum`, jamais exposées publiquement.
- Autorisation via **Policies** Laravel (`app/Policies`), appelées par `$this->authorize()` dans
  chaque contrôleur — jamais de vérification de rôle ad-hoc dans la vue ou le contrôleur seul.
- Validation via des **Form Requests** dédiées (`app/Http/Requests`) — aucun `$request->all()` en
  mass assignment.
- Connexion : rate limiting (5 tentatives), verrouillage temporaire.
- Mots de passe temporaires (création de compte / réinitialisation) envoyés uniquement par email
  (`Mail::send`, jamais affichés dans la réponse HTTP ou les logs applicatifs).
- Middleware `role:` nettoyé (plus de logs de debug exposant les rôles).

## Routes

- `routes/web.php` : une seule définition par action (plus de doublons de routes avec middleware
  incohérent). Les routes littérales (`/classes/create`, `/eleves/{id}/edit`) sont enregistrées
  **avant** les routes dynamiques généralistes (`/classes/{classe}`) pour éviter qu'un segment
  comme `create` soit capturé par le binding de modèle implicite.
- `routes/api.php` : toutes les routes nommées sous le préfixe `api.` pour éviter toute collision
  de nom avec les routes web homonymes (bug présent dans l'ancien projet : `route('matieres.index')`
  résolvait vers `/api/matieres` au lieu de `/matieres`).

## Fonctionnalités notables

- **Bulletins PDF** (`BulletinController`) : moyenne par matière pondérée par le coefficient de
  chaque note, moyenne générale pondérée par le coefficient de chaque matière, rang calculé par
  comparaison avec les autres élèves actifs de la classe, appréciation automatique.
- **Emploi du temps** (`EmploiDuTempsController` + `CreneauHoraire`) : grille hebdomadaire par
  classe, lecture pour tous les rôles, écriture réservée à l'admin.
- **Paiements** : statut recalculé automatiquement (`payé`/`partiel`/`en attente`/`en retard`) à
  partir du montant payé et de la date d'échéance.
- **Comptes utilisateurs** (`UserAccountController`) : provisioning de comptes pour élèves,
  enseignants et tuteurs ayant un email enregistré ; mot de passe généré aléatoirement et envoyé
  par email uniquement.

## Tests

`tests/Feature/` couvre : authentification (succès/échec/rate-limit implicite/logout),
contrôle d'accès par rôle (admin/enseignant/élève/parent), CRUD élève avec validation, génération
de bulletin PDF de bout en bout, **inscription d'une nouvelle école avec essai Premium**,
**isolation des données entre deux écoles**, **back-office super-admin** (suspension/réactivation
d'école, confirmation manuelle de facture). Lancer avec `php artisan test` (base SQLite en
mémoire, voir `phpunit.xml`).

## Prochaine étape : API de paiement

Pour brancher un agrégateur Mobile Money (CinetPay, PayDunya, ou API directe Flooz/TMoney) :
1. Remplacer le bouton "Passer au Premium" (`AbonnementController::souscrire`) par une redirection
   vers le checkout de l'agrégateur, en passant l'ID de la `Facture` créée comme référence.
2. Ajouter une route de webhook qui appelle `Facture::confirmerPaiement()` avec la méthode et la
   référence renvoyées par l'agrégateur — exactement ce que fait
   `SuperAdmin\FactureController::confirmer()` manuellement aujourd'hui.
3. Garder la confirmation manuelle super-admin comme filet de sécurité (paiement reçu hors ligne,
   litige, etc.).

## Démarrage local

```bash
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve
```

Comptes de démonstration (mot de passe `password`) : voir `database/seeders/EcoleSeeder.php`
(super-admin : `superadmin@ecole-manager.test`, école de démo : `École Démo`),
`AdminUserSeeder.php`, `EnseignantSeeder.php` et `TuteurSeeder.php`. Le mailer est configuré sur
`log` en local : les emails (identifiants de connexion, réinitialisation de mot de passe) sont
visibles dans `storage/logs/laravel.log`. Pour tester une deuxième école, utiliser `/inscription`.
