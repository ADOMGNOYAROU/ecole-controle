import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { environment } from '../../../environments/environment';
import { crudApi, messageErreur } from '../../core/crud';
import { HorodatageFirestore, versDateAffichee, versDateInput } from '../../core/dates';

interface AnneeScolaire {
  id: string;
  libelle: string;
}

interface Trimestre {
  id: string;
  anneeScolaireId: string;
  nom: string;
  ordre: number;
  dateDebut: HorodatageFirestore;
  dateFin: HorodatageFirestore;
}

@Component({
  selector: 'app-trimestres-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <h1 class="mb-6 text-xl font-semibold text-slate-900">Trimestres</h1>

      <form
        (ngSubmit)="valider()"
        class="mb-6 grid grid-cols-1 gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-5"
      >
        <div class="sm:col-span-2">
          <label class="mb-1 block text-sm font-medium text-slate-700">Année scolaire</label>
          <select [(ngModel)]="anneeScolaireId" name="anneeScolaireId" required class="champ">
            <option value="" disabled>Choisir…</option>
            @for (annee of annees(); track annee.id) {
              <option [value]="annee.id">{{ annee.libelle }}</option>
            }
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Nom</label>
          <input [(ngModel)]="nom" name="nom" required maxlength="50" placeholder="1er trimestre" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Ordre</label>
          <input [(ngModel)]="ordre" name="ordre" type="number" min="1" max="4" required class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Début</label>
          <input [(ngModel)]="dateDebut" name="dateDebut" type="date" required class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Fin</label>
          <input [(ngModel)]="dateFin" name="dateFin" type="date" required class="champ" />
        </div>
        <div class="flex items-end gap-2 sm:col-span-2 sm:justify-end">
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
              <th class="px-4 py-3">Année scolaire</th>
              <th class="px-4 py-3">Nom</th>
              <th class="px-4 py-3">Ordre</th>
              <th class="px-4 py-3">Début</th>
              <th class="px-4 py-3">Fin</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @for (item of items(); track item.id) {
              <tr>
                <td class="px-4 py-3 text-slate-600">{{ libelleAnnee(item.anneeScolaireId) }}</td>
                <td class="px-4 py-3 font-medium text-slate-900">{{ item.nom }}</td>
                <td class="px-4 py-3 text-slate-600">{{ item.ordre }}</td>
                <td class="px-4 py-3 text-slate-600">{{ dateAffichee(item.dateDebut) }}</td>
                <td class="px-4 py-3 text-slate-600">{{ dateAffichee(item.dateFin) }}</td>
                <td class="px-4 py-3 text-right">
                  <button (click)="editer(item)" class="lien">Modifier</button>
                  <button (click)="supprimer(item)" class="lien text-red-600">Supprimer</button>
                </td>
              </tr>
            } @empty {
              <tr>
                <td colspan="6" class="px-4 py-6 text-center text-slate-400">Aucun trimestre.</td>
              </tr>
            }
          </tbody>
        </table>
      </div>
    </div>
  `,
})
export class TrimestresPage {
  private readonly http = inject(HttpClient);
  private readonly api = crudApi<Trimestre>(this.http, `${environment.apiUrl}/trimestres`);
  private readonly anneesApi = crudApi<AnneeScolaire>(this.http, `${environment.apiUrl}/annees-scolaires`);

  readonly items = signal<Trimestre[]>([]);
  readonly annees = signal<AnneeScolaire[]>([]);
  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);
  readonly enEdition = signal<string | null>(null);

  anneeScolaireId = '';
  nom = '';
  ordre = 1;
  dateDebut = '';
  dateFin = '';

  constructor() {
    void this.charger();
  }

  dateAffichee = versDateAffichee;

  libelleAnnee(id: string): string {
    return this.annees().find((a) => a.id === id)?.libelle ?? '—';
  }

  async charger(): Promise<void> {
    const [items, annees] = await Promise.all([this.api.lister(), this.anneesApi.lister()]);
    this.items.set(items);
    this.annees.set(annees);
  }

  editer(item: Trimestre): void {
    this.enEdition.set(item.id);
    this.anneeScolaireId = item.anneeScolaireId;
    this.nom = item.nom;
    this.ordre = item.ordre;
    this.dateDebut = versDateInput(item.dateDebut);
    this.dateFin = versDateInput(item.dateFin);
  }

  annuler(): void {
    this.enEdition.set(null);
    this.anneeScolaireId = '';
    this.nom = '';
    this.ordre = 1;
    this.dateDebut = '';
    this.dateFin = '';
  }

  async valider(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    const payload = {
      anneeScolaireId: this.anneeScolaireId,
      nom: this.nom,
      ordre: Number(this.ordre),
      dateDebut: this.dateDebut,
      dateFin: this.dateFin,
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

  async supprimer(item: Trimestre): Promise<void> {
    if (!confirm(`Supprimer le trimestre ${item.nom} ?`)) return;
    await this.api.supprimer(item.id);
    await this.charger();
  }
}
