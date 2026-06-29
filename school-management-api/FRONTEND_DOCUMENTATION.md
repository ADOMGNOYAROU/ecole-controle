# Frontend — École Manager

## Stack

- Blade + Tailwind CSS 3 (compilé via Vite, pas de CDN)
- Alpine.js (composants interactifs : messages flash, menus)
- Chart.js (disponible globalement via `window.Chart` pour des graphiques futurs)
- Build : `npm run dev` (watch) / `npm run build` (production)

## Design system

Défini dans `tailwind.config.js` (palette `brand-*`) et `resources/css/app.css` (classes utilitaires
`.btn-primary`, `.btn-secondary`, `.btn-danger`, `.card`, `.form-input`, `.form-select`, `.form-error`,
`.badge-*`, `.data-table`, `.nav-link`, `.page-title`).

## Structure des vues

```
resources/views/
├── layouts/
│   ├── app.blade.php       # Layout authentifié : sidebar (nav par rôle) + topbar
│   └── auth.blade.php      # Layout page de connexion
├── components/
│   ├── flash-messages.blade.php
│   ├── card.blade.php
│   ├── button.blade.php
│   └── input.blade.php
├── auth/login.blade.php
├── dashboards/{admin,enseignant,eleve,parent}.blade.php
├── classes/ eleves/ enseignants/ matieres/ tuteurs/   # CRUD admin/enseignant
├── notes/ presences/                                  # saisie + saisie groupée + rapports
├── emploi-du-temps/index.blade.php                     # grille hebdomadaire par classe
├── paiements/                                          # suivi scolarité
├── annonces/ notifications/
├── bulletins/{index,pdf}.blade.php                     # génération + template PDF (dompdf)
├── annees-scolaires/index.blade.php                    # années scolaires + trimestres (page combinée)
├── comptes/index.blade.php                             # provisioning des comptes utilisateurs
├── profile/edit.blade.php
└── mon-espace/                                         # espace élève + espace parent
    ├── notes.blade.php / presences.blade.php / paiements.blade.php
    └── enfants.blade.php / enfant-detail.blade.php
```

Chaque ressource CRUD suit le même schéma : `index` (liste + filtres), `create`/`edit` (formulaire
partagé via un partial `_form.blade.php`), `show` quand pertinent. La navigation (sidebar) dans
`layouts/app.blade.php` s'adapte au rôle de l'utilisateur connecté (`admin`, `enseignant`, `eleve`,
`parent`).

## Notes d'implémentation

- Les boutons d'action (`@can`) s'appuient sur les Policies Laravel — pas de logique d'autorisation
  dupliquée côté vue au-delà de l'affichage conditionnel.
- Les formulaires de saisie groupée (notes/présences) chargent la liste des élèves d'une classe en
  AJAX (`fetch`) via les routes `notes.eleves` / `presences.eleves`.
- Le template `bulletins/pdf.blade.php` utilise du CSS inline (pas Tailwind) car dompdf ne supporte
  qu'un sous-ensemble de CSS.
