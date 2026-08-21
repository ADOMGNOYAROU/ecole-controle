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
        ],
      },
    ],
  },
];
