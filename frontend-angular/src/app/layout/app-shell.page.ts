import { Component, inject } from '@angular/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { Router, RouterLink, RouterLinkActive, RouterOutlet } from '@angular/router';
import { AuthService } from '../core/auth.service';

@Component({
  selector: 'app-shell',
  standalone: true,
  imports: [RouterLink, RouterLinkActive, RouterOutlet],
  template: `
    <div class="flex h-screen overflow-hidden bg-slate-100">
      <aside
        class="hidden lg:flex w-64 shrink-0 flex-col h-screen overflow-y-auto bg-gradient-to-b from-[#0b1d4d] to-[#101a3d] px-4 py-6"
      >
        <div class="mb-8 flex items-center gap-2 px-2 shrink-0">
          <div
            class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-violet-600 text-white font-bold shadow-sm"
          >
            É
          </div>
          <span class="text-lg font-semibold text-white">Scolarix</span>
        </div>

        <nav class="flex-1 space-y-0.5">
          @if (!estSuperAdmin()) {
            <a routerLink="/dashboard" routerLinkActive="nav-active" class="nav-link">Tableau de bord</a>
          }

          @if (estSuperAdmin()) {
            <p class="px-3 pt-2 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Administration SaaS</p>
            <a routerLink="/super-admin" routerLinkActive="nav-active" class="nav-link">Tableau de bord</a>
            <a routerLink="/super-admin/ecoles" routerLinkActive="nav-active" class="nav-link">Écoles</a>
            <a routerLink="/super-admin/factures" routerLinkActive="nav-active" class="nav-link">Factures</a>
          }

          @if (estEleve()) {
            <a routerLink="/mon-espace" routerLinkActive="nav-active" class="nav-link">Mon espace</a>
          }
          @if (estParent()) {
            <a routerLink="/mes-enfants" routerLinkActive="nav-active" class="nav-link">Mes enfants</a>
          }

          @if (estAdminOuEnseignant()) {
            <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Vie scolaire</p>
            <a routerLink="/emploi-du-temps" routerLinkActive="nav-active" class="nav-link">Emploi du temps</a>
            <a routerLink="/notes" routerLinkActive="nav-active" class="nav-link">Notes</a>
            <a routerLink="/presences" routerLinkActive="nav-active" class="nav-link">Présences</a>
            <a routerLink="/bulletins" routerLinkActive="nav-active" class="nav-link">Bulletins</a>
            <a routerLink="/progression" routerLinkActive="nav-active" class="nav-link">Risque d'échec</a>
          }

          @if (estAdmin()) {
            <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Référentiel</p>
            <a routerLink="/annees-scolaires" routerLinkActive="nav-active" class="nav-link">Années scolaires</a>
            <a routerLink="/trimestres" routerLinkActive="nav-active" class="nav-link">Trimestres</a>
            <a routerLink="/classes" routerLinkActive="nav-active" class="nav-link">Classes</a>
            <a routerLink="/matieres" routerLinkActive="nav-active" class="nav-link">Matières</a>
            <a routerLink="/enseignants" routerLinkActive="nav-active" class="nav-link">Enseignants</a>
            <a routerLink="/tuteurs" routerLinkActive="nav-active" class="nav-link">Parents / Tuteurs</a>
            <a routerLink="/eleves" routerLinkActive="nav-active" class="nav-link">Élèves</a>
            <a routerLink="/paiements" routerLinkActive="nav-active" class="nav-link">Paiements</a>
            <a routerLink="/abonnement" routerLinkActive="nav-active" class="nav-link">Abonnement</a>
          }

          @if (!estSuperAdmin()) {
            <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Communication</p>
            <a routerLink="/annonces" routerLinkActive="nav-active" class="nav-link">Annonces</a>
            <a routerLink="/notifications" routerLinkActive="nav-active" class="nav-link">Notifications</a>
            @if (estEnseignantOuParent()) {
              <a routerLink="/messagerie" routerLinkActive="nav-active" class="nav-link">Messagerie</a>
            }
          }
        </nav>

        <button
          (click)="seDeconnecter()"
          class="mt-4 rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-300 hover:bg-white/5 hover:text-white"
        >
          Se déconnecter
        </button>
      </aside>

      <main class="flex-1 overflow-y-auto">
        <router-outlet />
      </main>
    </div>
  `,
  styles: [
    `
      .nav-link {
        display: block;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #cbd5e1;
      }
      .nav-link:hover {
        background: rgba(255, 255, 255, 0.06);
        color: #fff;
      }
      .nav-active {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
      }
    `,
  ],
})
export class AppShellPage {
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);
  private readonly session = toSignal(this.authService.user$, { initialValue: null });

  estEnseignantOuParent(): boolean {
    const role = this.session()?.role;
    return role === 'enseignant' || role === 'parent';
  }

  estAdmin(): boolean {
    return this.session()?.role === 'admin';
  }

  estSuperAdmin(): boolean {
    return this.session()?.role === 'super_admin';
  }

  estAdminOuEnseignant(): boolean {
    const role = this.session()?.role;
    return role === 'admin' || role === 'enseignant';
  }

  estEleve(): boolean {
    return this.session()?.role === 'eleve';
  }

  estParent(): boolean {
    return this.session()?.role === 'parent';
  }

  async seDeconnecter(): Promise<void> {
    await this.authService.deconnexion();
    await this.router.navigateByUrl('/connexion');
  }
}
