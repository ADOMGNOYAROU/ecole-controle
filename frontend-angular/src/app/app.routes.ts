import { Routes } from '@angular/router';
import { authGuard, roleGuard } from './core/guards';

export const routes: Routes = [
  { path: '', pathMatch: 'full', redirectTo: 'connexion' },
  {
    path: 'connexion',
    loadComponent: () => import('./features/auth/login.page').then((m) => m.LoginPage),
  },
  {
    path: 'inscription',
    loadComponent: () => import('./features/auth/inscription.page').then((m) => m.InscriptionPage),
  },
  {
    path: '',
    canActivate: [authGuard],
    loadComponent: () => import('./layout/app-shell.page').then((m) => m.AppShellPage),
    children: [
      {
        path: 'dashboard',
        loadComponent: () => import('./features/dashboard/dashboard.page').then((m) => m.DashboardPage),
      },
      {
        path: '',
        canActivate: [roleGuard(['admin'])],
        children: [
          {
            path: 'annees-scolaires',
            loadComponent: () =>
              import('./features/annees-scolaires/annees-scolaires.page').then((m) => m.AnneesScolairesPage),
          },
          {
            path: 'trimestres',
            loadComponent: () => import('./features/trimestres/trimestres.page').then((m) => m.TrimestresPage),
          },
          {
            path: 'classes',
            loadComponent: () => import('./features/classes/classes.page').then((m) => m.ClassesPage),
          },
          {
            path: 'matieres',
            loadComponent: () => import('./features/matieres/matieres.page').then((m) => m.MatieresPage),
          },
          {
            path: 'enseignants',
            loadComponent: () => import('./features/enseignants/enseignants.page').then((m) => m.EnseignantsPage),
          },
          {
            path: 'tuteurs',
            loadComponent: () => import('./features/tuteurs/tuteurs.page').then((m) => m.TuteursPage),
          },
          {
            path: 'eleves',
            loadComponent: () => import('./features/eleves/eleves.page').then((m) => m.ElevesPage),
          },
          {
            path: 'paiements',
            loadComponent: () => import('./features/paiements/paiements.page').then((m) => m.PaiementsPage),
          },
          {
            path: 'abonnement',
            loadComponent: () => import('./features/abonnement/abonnement.page').then((m) => m.AbonnementPage),
          },
        ],
      },
      {
        path: '',
        canActivate: [roleGuard(['admin', 'enseignant'])],
        children: [
          {
            path: 'emploi-du-temps',
            loadComponent: () =>
              import('./features/emploi-du-temps/emploi-du-temps.page').then((m) => m.EmploiDuTempsPage),
          },
          {
            path: 'notes',
            loadComponent: () => import('./features/notes/notes.page').then((m) => m.NotesPage),
          },
          {
            path: 'presences',
            loadComponent: () => import('./features/presences/presences.page').then((m) => m.PresencesPage),
          },
          {
            path: 'bulletins',
            loadComponent: () => import('./features/bulletins/bulletins.page').then((m) => m.BulletinsPage),
          },
          {
            path: 'progression',
            loadComponent: () => import('./features/progression/progression.page').then((m) => m.ProgressionPage),
          },
          {
            path: 'notifications',
            loadComponent: () =>
              import('./features/notifications/notifications.page').then((m) => m.NotificationsPage),
          },
          {
            path: 'annonces',
            loadComponent: () => import('./features/annonces/annonces.page').then((m) => m.AnnoncesPage),
          },
        ],
      },
      {
        path: '',
        canActivate: [roleGuard(['enseignant', 'parent'])],
        children: [
          {
            path: 'messagerie',
            loadComponent: () => import('./features/messagerie/messagerie.page').then((m) => m.MessageriePage),
          },
        ],
      },
      {
        path: '',
        canActivate: [roleGuard(['eleve'])],
        children: [
          {
            path: 'mon-espace',
            loadComponent: () => import('./features/mon-espace/mon-espace.page').then((m) => m.MonEspacePage),
          },
        ],
      },
      {
        path: '',
        canActivate: [roleGuard(['parent'])],
        children: [
          {
            path: 'mes-enfants',
            loadComponent: () => import('./features/mes-enfants/mes-enfants.page').then((m) => m.MesEnfantsPage),
          },
        ],
      },
      {
        path: '',
        canActivate: [roleGuard(['super_admin'])],
        children: [
          {
            path: 'super-admin',
            loadComponent: () =>
              import('./features/super-admin/dashboard.page').then((m) => m.SuperAdminDashboardPage),
          },
          {
            path: 'super-admin/ecoles',
            loadComponent: () => import('./features/super-admin/ecoles.page').then((m) => m.SuperAdminEcolesPage),
          },
          {
            path: 'super-admin/ecoles/:id',
            loadComponent: () =>
              import('./features/super-admin/ecole-detail.page').then((m) => m.SuperAdminEcoleDetailPage),
          },
          {
            path: 'super-admin/factures',
            loadComponent: () =>
              import('./features/super-admin/factures.page').then((m) => m.SuperAdminFacturesPage),
          },
        ],
      },
    ],
  },
];
