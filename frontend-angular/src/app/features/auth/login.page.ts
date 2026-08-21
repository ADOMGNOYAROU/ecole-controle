import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { AuthService } from '../../core/auth.service';

@Component({
  selector: 'app-login-page',
  standalone: true,
  imports: [FormsModule, RouterLink],
  template: `
    <div class="min-h-screen flex items-center justify-center bg-slate-100 px-4">
      <div class="w-full max-w-sm">
        <div class="mb-8 flex items-center justify-center gap-2">
          <div
            class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-violet-600 text-white font-bold shadow-sm"
          >
            É
          </div>
          <span class="text-xl font-semibold text-slate-900">Scolarix</span>
        </div>

        <form
          (ngSubmit)="seConnecter()"
          class="rounded-xl bg-white p-8 shadow-sm ring-1 ring-slate-200 space-y-5"
        >
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="email">Email</label>
            <input
              id="email"
              name="email"
              type="email"
              required
              [(ngModel)]="email"
              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="password">Mot de passe</label>
            <input
              id="password"
              name="password"
              type="password"
              required
              [(ngModel)]="motDePasse"
              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
            />
          </div>

          @if (erreur()) {
            <p class="text-sm text-red-600">{{ erreur() }}</p>
          }

          <button
            type="submit"
            [disabled]="enCours()"
            class="w-full rounded-md bg-brand-600 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-60"
          >
            {{ enCours() ? 'Connexion…' : 'Se connecter' }}
          </button>

          <p class="text-center text-sm text-slate-500">
            Pas encore de compte ?
            <a routerLink="/inscription" class="font-medium text-brand-600 hover:underline">Inscrire mon école</a>
          </p>
        </form>
      </div>
    </div>
  `,
})
export class LoginPage {
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);

  email = '';
  motDePasse = '';
  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);

  async seConnecter(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    try {
      await this.authService.connexion(this.email, this.motDePasse);
      await this.router.navigateByUrl('/dashboard');
    } catch {
      this.erreur.set('Email ou mot de passe incorrect.');
    } finally {
      this.enCours.set(false);
    }
  }
}
