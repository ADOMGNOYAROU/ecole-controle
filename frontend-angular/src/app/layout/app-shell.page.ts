import { Component, inject } from '@angular/core';
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
          <a routerLink="/dashboard" routerLinkActive="nav-active" class="nav-link">Tableau de bord</a>

          <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Référentiel</p>
          <a routerLink="/annees-scolaires" routerLinkActive="nav-active" class="nav-link">Années scolaires</a>
          <a routerLink="/trimestres" routerLinkActive="nav-active" class="nav-link">Trimestres</a>
          <a routerLink="/classes" routerLinkActive="nav-active" class="nav-link">Classes</a>
          <a routerLink="/matieres" routerLinkActive="nav-active" class="nav-link">Matières</a>
          <a routerLink="/enseignants" routerLinkActive="nav-active" class="nav-link">Enseignants</a>
          <a routerLink="/tuteurs" routerLinkActive="nav-active" class="nav-link">Parents / Tuteurs</a>
          <a routerLink="/eleves" routerLinkActive="nav-active" class="nav-link">Élèves</a>
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

  async seDeconnecter(): Promise<void> {
    await this.authService.deconnexion();
    await this.router.navigateByUrl('/connexion');
  }
}
