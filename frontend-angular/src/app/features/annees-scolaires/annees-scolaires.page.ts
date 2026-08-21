import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { environment } from '../../../environments/environment';
import { crudApi, messageErreur } from '../../core/crud';
import { HorodatageFirestore, versDateAffichee, versDateInput } from '../../core/dates';

interface AnneeScolaire {
  id: string;
  libelle: string;
  dateDebut: HorodatageFirestore;
  dateFin: HorodatageFirestore;
  active: boolean;
}

@Component({
  selector: 'app-annees-scolaires-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Années scolaires</h1>
      </div>

      <form
        (ngSubmit)="valider()"
        class="mb-6 grid grid-cols-1 gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-4"
      >
        <div class="sm:col-span-2">
          <label class="mb-1 block text-sm font-medium text-slate-700">Libellé</label>
          <input
            [(ngModel)]="libelle"
            name="libelle"
            required
            maxlength="20"
            placeholder="2026-2027"
            class="champ"
          />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Début</label>
          <input [(ngModel)]="dateDebut" name="dateDebut" type="date" required class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Fin</label>
          <input [(ngModel)]="dateFin" name="dateFin" type="date" required class="champ" />
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700 sm:col-span-2">
          <input [(ngModel)]="active" name="active" type="checkbox" class="rounded border-slate-300" />
          Année active
        </label>
        <div class="flex items-end gap-2 sm:col-span-2 sm:justify-end">
          @if (enEdition()) {
            <button type="button" (click)="annuler()" class="btn-ghost">Annuler</button>
          }
          <button type="submit" [disabled]="enCours()" class="btn-primary">
            {{ enEdition() ? 'Enregistrer' : 'Ajouter' }}
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
              <th class="px-4 py-3">Libellé</th>
              <th class="px-4 py-3">Début</th>
              <th class="px-4 py-3">Fin</th>
              <th class="px-4 py-3">Statut</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @for (item of items(); track item.id) {
              <tr>
                <td class="px-4 py-3 font-medium text-slate-900">{{ item.libelle }}</td>
                <td class="px-4 py-3 text-slate-600">{{ dateAffichee(item.dateDebut) }}</td>
                <td class="px-4 py-3 text-slate-600">{{ dateAffichee(item.dateFin) }}</td>
                <td class="px-4 py-3">
                  @if (item.active) {
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700"
                      >Active</span
                    >
                  } @else {
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600"
                      >Inactive</span
                    >
                  }
                </td>
                <td class="px-4 py-3 text-right">
                  <button (click)="editer(item)" class="lien">Modifier</button>
                  <button (click)="supprimer(item)" class="lien text-red-600">Supprimer</button>
                </td>
              </tr>
            } @empty {
              <tr>
                <td colspan="5" class="px-4 py-6 text-center text-slate-400">Aucune année scolaire.</td>
              </tr>
            }
          </tbody>
        </table>
      </div>
    </div>
  `,
})
export class AnneesScolairesPage {
  private readonly http = inject(HttpClient);
  private readonly api = crudApi<AnneeScolaire>(this.http, `${environment.apiUrl}/annees-scolaires`);

  readonly items = signal<AnneeScolaire[]>([]);
  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);
  readonly enEdition = signal<string | null>(null);

  libelle = '';
  dateDebut = '';
  dateFin = '';
  active = false;

  constructor() {
    void this.charger();
  }

  dateAffichee = versDateAffichee;

  async charger(): Promise<void> {
    this.items.set(await this.api.lister());
  }

  editer(item: AnneeScolaire): void {
    this.enEdition.set(item.id);
    this.libelle = item.libelle;
    this.dateDebut = versDateInput(item.dateDebut);
    this.dateFin = versDateInput(item.dateFin);
    this.active = item.active;
  }

  annuler(): void {
    this.enEdition.set(null);
    this.libelle = '';
    this.dateDebut = '';
    this.dateFin = '';
    this.active = false;
  }

  async valider(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    const payload = { libelle: this.libelle, dateDebut: this.dateDebut, dateFin: this.dateFin, active: this.active };
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

  async supprimer(item: AnneeScolaire): Promise<void> {
    if (!confirm(`Supprimer l'année ${item.libelle} ?`)) return;
    await this.api.supprimer(item.id);
    await this.charger();
  }
}
