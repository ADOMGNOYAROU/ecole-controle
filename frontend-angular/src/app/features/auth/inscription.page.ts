import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import { AuthService } from '../../core/auth.service';

@Component({
  selector: 'app-inscription-page',
  standalone: true,
  imports: [FormsModule, RouterLink],
  template: `
    <div class="min-h-screen flex items-center justify-center bg-slate-100 px-4 py-10">
      <div class="w-full max-w-md">
        <div class="mb-8 flex items-center justify-center gap-2">
          <div
            class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-violet-600 text-white font-bold shadow-sm"
          >
            É
          </div>
          <span class="text-xl font-semibold text-slate-900">Scolarix</span>
        </div>

        <form
          (ngSubmit)="inscrire()"
          class="rounded-xl bg-white p-8 shadow-sm ring-1 ring-slate-200 space-y-4"
        >
          <h1 class="text-lg font-semibold text-slate-900">Inscrire votre école</h1>
          <p class="text-sm text-slate-500">30 jours d'essai Premium gratuit, sans carte bancaire.</p>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="nomEcole">Nom de l'école</label>
            <input
              id="nomEcole"
              name="nomEcole"
              required
              [(ngModel)]="nomEcole"
              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1" for="ville">Ville</label>
              <input
                id="ville"
                name="ville"
                [(ngModel)]="ville"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1" for="telephone">Téléphone</label>
              <input
                id="telephone"
                name="telephone"
                [(ngModel)]="telephone"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
              />
            </div>
          </div>

          <hr class="border-slate-200" />

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="adminNom">Votre nom</label>
            <input
              id="adminNom"
              name="adminNom"
              required
              [(ngModel)]="adminNom"
              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="adminEmail">Votre email</label>
            <input
              id="adminEmail"
              name="adminEmail"
              type="email"
              required
              [(ngModel)]="adminEmail"
              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="adminPassword">Mot de passe</label>
            <input
              id="adminPassword"
              name="adminPassword"
              type="password"
              required
              minlength="8"
              [(ngModel)]="adminPassword"
              class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500"
            />
            <p class="mt-1 text-xs text-slate-400">8 caractères minimum.</p>
          </div>

          @if (erreur()) {
            <p class="text-sm text-red-600">{{ erreur() }}</p>
          }

          <button
            type="submit"
            [disabled]="enCours()"
            class="w-full rounded-md bg-brand-600 py-2 text-sm font-semibold text-white hover:bg-brand-700 disabled:opacity-60"
          >
            {{ enCours() ? 'Création…' : 'Créer mon école' }}
          </button>

          <p class="text-center text-sm text-slate-500">
            Déjà inscrit ?
            <a routerLink="/connexion" class="font-medium text-brand-600 hover:underline">Se connecter</a>
          </p>
        </form>
      </div>
    </div>
  `,
})
export class InscriptionPage {
  private readonly http = inject(HttpClient);
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);

  nomEcole = '';
  ville = '';
  telephone = '';
  adminNom = '';
  adminEmail = '';
  adminPassword = '';

  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);

  async inscrire(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    try {
      const { customToken } = await firstValueFrom(
        this.http.post<{ customToken: string }>(`${environment.apiUrl}/inscription`, {
          nomEcole: this.nomEcole,
          ville: this.ville || undefined,
          telephone: this.telephone || undefined,
          adminNom: this.adminNom,
          adminEmail: this.adminEmail,
          adminPassword: this.adminPassword,
        }),
      );

      await this.authService.connexionAvecToken(customToken);
      await this.router.navigateByUrl('/dashboard');
    } catch (error) {
      this.erreur.set(this.messageErreur(error));
    } finally {
      this.enCours.set(false);
    }
  }

  private messageErreur(error: unknown): string {
    const status = (error as { status?: number } | null)?.status;
    if (status === 409) {
      return 'Cette adresse email est déjà utilisée.';
    }
    return "Une erreur est survenue, réessayez.";
  }
}
