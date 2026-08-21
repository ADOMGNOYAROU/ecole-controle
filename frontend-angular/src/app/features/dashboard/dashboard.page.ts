import { AsyncPipe } from '@angular/common';
import { Component, inject } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../core/auth.service';

@Component({
  selector: 'app-dashboard-page',
  standalone: true,
  imports: [AsyncPipe],
  template: `
    <div class="min-h-screen bg-slate-100 p-8">
      <div class="mx-auto max-w-2xl rounded-xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
        @if (authService.user$ | async; as user) {
          <p class="text-sm text-slate-500">Connecté</p>
          <h1 class="mt-1 text-xl font-semibold text-slate-900">{{ user.email }}</h1>
          <dl class="mt-4 space-y-1 text-sm text-slate-600">
            <div><dt class="inline font-medium">Rôle :</dt> <dd class="inline">{{ user.role }}</dd></div>
            <div><dt class="inline font-medium">École :</dt> <dd class="inline">{{ user.ecoleId ?? '—' }}</dd></div>
          </dl>
        }

        <button
          (click)="seDeconnecter()"
          class="mt-6 rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
          Se déconnecter
        </button>
      </div>
    </div>
  `,
})
export class DashboardPage {
  protected readonly authService = inject(AuthService);
  private readonly router = inject(Router);

  async seDeconnecter(): Promise<void> {
    await this.authService.deconnexion();
    await this.router.navigateByUrl('/connexion');
  }
}
