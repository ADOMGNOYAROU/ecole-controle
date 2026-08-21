import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { environment } from '../../../environments/environment';
import { crudApi, messageErreur } from '../../core/crud';

interface AnneeScolaire {
  id: string;
  libelle: string;
}
interface Enseignant {
  id: string;
  nom: string;
  prenom: string;
}
interface Classe {
  id: string;
  nom: string;
  niveau: string | null;
  anneeScolaireId: string;
  enseignantPrincipalId: string | null;
  capacite: number | null;
}

@Component({
  selector: 'app-classes-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <h1 class="mb-6 text-xl font-semibold text-slate-900">Classes</h1>

      <form
        (ngSubmit)="valider()"
        class="mb-6 grid grid-cols-1 gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-5"
      >
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Nom</label>
          <input [(ngModel)]="nom" name="nom" required maxlength="100" placeholder="6ème A" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Niveau</label>
          <input [(ngModel)]="niveau" name="niveau" maxlength="100" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Année scolaire</label>
          <select [(ngModel)]="anneeScolaireId" name="anneeScolaireId" required class="champ">
            <option value="" disabled>Choisir…</option>
            @for (annee of annees(); track annee.id) {
              <option [value]="annee.id">{{ annee.libelle }}</option>
            }
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Enseignant principal</label>
          <select [(ngModel)]="enseignantPrincipalId" name="enseignantPrincipalId" class="champ">
            <option value="">Aucun</option>
            @for (enseignant of enseignants(); track enseignant.id) {
              <option [value]="enseignant.id">{{ enseignant.prenom }} {{ enseignant.nom }}</option>
            }
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Capacité</label>
          <input [(ngModel)]="capacite" name="capacite" type="number" min="1" max="500" class="champ" />
        </div>

        <div class="flex items-end gap-2 sm:col-span-5 sm:justify-end">
          @if (enEdition()) {
            <button type="button" (click)="annuler()" class="btn-ghost">Annuler</button>
          }
          <button type="submit" [disabled]="enCours()" class="btn-primary">
            {{ enEdition() ? 'Enregistrer' : 'Ajouter' }}
          </button>
        </div>
        @if (erreur()) {
          <p class="text-sm text-red-600 sm:col-span-5">{{ erreur() }}</p>
        }
      </form>

      <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
          <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
              <th class="px-4 py-3">Nom</th>
              <th class="px-4 py-3">Année scolaire</th>
              <th class="px-4 py-3">Enseignant principal</th>
              <th class="px-4 py-3">Capacité</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @for (item of items(); track item.id) {
              <tr>
                <td class="px-4 py-3 font-medium text-slate-900">{{ item.nom }}</td>
                <td class="px-4 py-3 text-slate-600">{{ libelleAnnee(item.anneeScolaireId) }}</td>
                <td class="px-4 py-3 text-slate-600">{{ nomEnseignant(item.enseignantPrincipalId) }}</td>
                <td class="px-4 py-3 text-slate-600">{{ item.capacite ?? '—' }}</td>
                <td class="px-4 py-3 text-right">
                  <button (click)="editer(item)" class="lien">Modifier</button>
                  <button (click)="supprimer(item)" class="lien text-red-600">Supprimer</button>
                </td>
              </tr>
            } @empty {
              <tr>
                <td colspan="5" class="px-4 py-6 text-center text-slate-400">Aucune classe.</td>
              </tr>
            }
          </tbody>
        </table>
      </div>
    </div>
  `,
})
export class ClassesPage {
  private readonly http = inject(HttpClient);
  private readonly api = crudApi<Classe>(this.http, `${environment.apiUrl}/classes`);
  private readonly anneesApi = crudApi<AnneeScolaire>(this.http, `${environment.apiUrl}/annees-scolaires`);
  private readonly enseignantsApi = crudApi<Enseignant>(this.http, `${environment.apiUrl}/enseignants`);

  readonly items = signal<Classe[]>([]);
  readonly annees = signal<AnneeScolaire[]>([]);
  readonly enseignants = signal<Enseignant[]>([]);
  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);
  readonly enEdition = signal<string | null>(null);

  nom = '';
  niveau = '';
  anneeScolaireId = '';
  enseignantPrincipalId = '';
  capacite: number | null = null;

  constructor() {
    void this.charger();
  }

  libelleAnnee(id: string): string {
    return this.annees().find((a) => a.id === id)?.libelle ?? '—';
  }

  nomEnseignant(id: string | null): string {
    if (!id) return '—';
    const enseignant = this.enseignants().find((e) => e.id === id);
    return enseignant ? `${enseignant.prenom} ${enseignant.nom}` : '—';
  }

  async charger(): Promise<void> {
    const [items, annees, enseignants] = await Promise.all([
      this.api.lister(),
      this.anneesApi.lister(),
      this.enseignantsApi.lister(),
    ]);
    this.items.set(items);
    this.annees.set(annees);
    this.enseignants.set(enseignants);
  }

  editer(item: Classe): void {
    this.enEdition.set(item.id);
    this.nom = item.nom;
    this.niveau = item.niveau ?? '';
    this.anneeScolaireId = item.anneeScolaireId;
    this.enseignantPrincipalId = item.enseignantPrincipalId ?? '';
    this.capacite = item.capacite;
  }

  annuler(): void {
    this.enEdition.set(null);
    this.nom = '';
    this.niveau = '';
    this.anneeScolaireId = '';
    this.enseignantPrincipalId = '';
    this.capacite = null;
  }

  async valider(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    const payload = {
      nom: this.nom,
      niveau: this.niveau || undefined,
      anneeScolaireId: this.anneeScolaireId,
      enseignantPrincipalId: this.enseignantPrincipalId || undefined,
      capacite: this.capacite || undefined,
    };
    try {
      const id = this.enEdition();
      if (id) {
        await this.api.modifier(id, payload);
      } else {
        await this.api.creer(payload);
      }
      this.annuler();
      await this.charger();
    } catch (error) {
      this.erreur.set(messageErreur(error));
    } finally {
      this.enCours.set(false);
    }
  }

  async supprimer(item: Classe): Promise<void> {
    if (!confirm(`Supprimer la classe ${item.nom} ?`)) return;
    await this.api.supprimer(item.id);
    await this.charger();
  }
}
