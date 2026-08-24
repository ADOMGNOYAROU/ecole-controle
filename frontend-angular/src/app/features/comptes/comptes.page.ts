import { HttpClient } from '@angular/common/http';
import { Component, computed, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import { crudApi, messageErreur } from '../../core/crud';

type TypeProfil = 'eleve' | 'enseignant' | 'tuteur';

interface Profil {
  id: string;
  nom: string;
  prenom: string;
  email: string | null;
  userId: string | null;
}
interface Utilisateur {
  id: string;
  name: string;
  email: string;
  role: string;
  mustChangePassword: boolean;
}
interface IdentifiantsGeneres {
  name: string;
  email: string;
  password: string;
}

@Component({
  selector: 'app-comptes-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <h1 class="mb-1 text-xl font-semibold text-slate-900">Comptes de connexion</h1>
      <p class="mb-6 text-sm text-slate-500">
        Génère un identifiant/mot de passe pour un enseignant, un élève ou un parent afin qu'il puisse se connecter à
        Scolarix.
      </p>

      @if (identifiants(); as id) {
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-5">
          <div class="flex items-start justify-between">
            <div>
              <p class="font-semibold text-emerald-900">Compte créé pour {{ id.name }}</p>
              <p class="mt-2 text-sm text-emerald-800">
                Email : <span class="font-mono font-semibold">{{ id.email }}</span>
              </p>
              <p class="text-sm text-emerald-800">
                Mot de passe : <span class="font-mono font-semibold">{{ id.password }}</span>
              </p>
              <p class="mt-2 text-xs text-emerald-700">
                Note bien ces identifiants et transmets-les à la personne concernée — ils ne seront plus jamais
                affichés.
              </p>
            </div>
            <button (click)="identifiants.set(null)" class="text-emerald-700 hover:text-emerald-900">✕</button>
          </div>
        </div>
      }

      <form
        (ngSubmit)="generer()"
        class="mb-8 grid grid-cols-1 gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-4"
      >
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Type</label>
          <select [(ngModel)]="type" name="type" (ngModelChange)="profilId = ''" class="champ">
            <option value="enseignant">Enseignant</option>
            <option value="eleve">Élève</option>
            <option value="tuteur">Parent / Tuteur</option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Personne (sans compte, avec email)</label>
          <select [(ngModel)]="profilId" name="profilId" required class="champ">
            <option value="" disabled>Choisir…</option>
            @for (profil of profilsSansCompte(); track profil.id) {
              <option [value]="profil.id">{{ profil.prenom }} {{ profil.nom }} ({{ profil.email }})</option>
            }
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Email manuel (optionnel)</label>
          <input [(ngModel)]="emailManuel" name="emailManuel" type="email" class="champ" placeholder="Sinon, celui du profil" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Mot de passe (optionnel)</label>
          <input [(ngModel)]="motDePasse" name="motDePasse" type="text" minlength="6" class="champ" placeholder="Sinon, généré" />
        </div>

        <div class="flex items-end sm:col-span-4">
          <button type="submit" [disabled]="enCours() || !profilId" class="btn-primary">
            {{ enCours() ? 'Création…' : 'Générer le compte' }}
          </button>
        </div>
        @if (erreur()) {
          <p class="text-sm text-red-600 sm:col-span-4">{{ erreur() }}</p>
        }
      </form>

      <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
          <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
              <th class="px-4 py-3">Nom</th>
              <th class="px-4 py-3">Email</th>
              <th class="px-4 py-3">Rôle</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @for (u of utilisateurs(); track u.id) {
              <tr>
                <td class="px-4 py-3 font-medium text-slate-900">{{ u.name }}</td>
                <td class="px-4 py-3 text-slate-600">{{ u.email }}</td>
                <td class="px-4 py-3 text-slate-600">{{ u.role }}</td>
                <td class="px-4 py-3 text-right">
                  <button (click)="reinitialiser(u)" class="lien">Réinitialiser mot de passe</button>
                  <button (click)="supprimer(u)" class="lien text-red-600">Supprimer</button>
                </td>
              </tr>
            } @empty {
              <tr>
                <td colspan="4" class="px-4 py-6 text-center text-slate-400">Aucun compte pour l'instant.</td>
              </tr>
            }
          </tbody>
        </table>
      </div>
    </div>
  `,
})
export class ComptesPage {
  private readonly http = inject(HttpClient);
  private readonly elevesApi = crudApi<Profil>(this.http, `${environment.apiUrl}/eleves`);
  private readonly enseignantsApi = crudApi<Profil>(this.http, `${environment.apiUrl}/enseignants`);
  private readonly tuteursApi = crudApi<Profil>(this.http, `${environment.apiUrl}/tuteurs`);

  readonly utilisateurs = signal<Utilisateur[]>([]);
  private readonly eleves = signal<Profil[]>([]);
  private readonly enseignants = signal<Profil[]>([]);
  private readonly tuteurs = signal<Profil[]>([]);

  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);
  readonly identifiants = signal<IdentifiantsGeneres | null>(null);

  type: TypeProfil = 'enseignant';
  profilId = '';
  emailManuel = '';
  motDePasse = '';

  readonly profilsSansCompte = computed(() => {
    const source =
      this.type === 'enseignant' ? this.enseignants() : this.type === 'eleve' ? this.eleves() : this.tuteurs();
    return source.filter((p) => !p.userId && !!p.email);
  });

  constructor() {
    void this.charger();
  }

  private async charger(): Promise<void> {
    const [utilisateurs, eleves, enseignants, tuteurs] = await Promise.all([
      firstValueFrom(this.http.get<Utilisateur[]>(`${environment.apiUrl}/comptes`)),
      this.elevesApi.lister(),
      this.enseignantsApi.lister(),
      this.tuteursApi.lister(),
    ]);
    this.utilisateurs.set(utilisateurs);
    this.eleves.set(eleves);
    this.enseignants.set(enseignants);
    this.tuteurs.set(tuteurs);
  }

  async generer(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    try {
      const identifiants = await firstValueFrom(
        this.http.post<IdentifiantsGeneres>(`${environment.apiUrl}/comptes/generer`, {
          type: this.type,
          profilId: this.profilId,
          emailManuel: this.emailManuel || undefined,
          motDePasse: this.motDePasse || undefined,
        }),
      );
      this.identifiants.set(identifiants);
      this.profilId = '';
      this.emailManuel = '';
      this.motDePasse = '';
      await this.charger();
    } catch (error) {
      this.erreur.set(messageErreur(error));
    } finally {
      this.enCours.set(false);
    }
  }

  async reinitialiser(utilisateur: Utilisateur): Promise<void> {
    if (!confirm(`Réinitialiser le mot de passe de ${utilisateur.name} ?`)) return;
    const identifiants = await firstValueFrom(
      this.http.patch<IdentifiantsGeneres>(`${environment.apiUrl}/comptes/${utilisateur.id}/reinitialiser-mot-de-passe`, {}),
    );
    this.identifiants.set(identifiants);
  }

  async supprimer(utilisateur: Utilisateur): Promise<void> {
    if (!confirm(`Supprimer le compte de ${utilisateur.name} ? La personne ne pourra plus se connecter.`)) return;
    await firstValueFrom(this.http.delete(`${environment.apiUrl}/comptes/${utilisateur.id}`));
    await this.charger();
  }
}
