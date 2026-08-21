import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { environment } from '../../../environments/environment';
import { crudApi, messageErreur } from '../../core/crud';

interface Matiere {
  id: string;
  nom: string;
  code: string;
  coefficientDefaut: number;
}

@Component({
  selector: 'app-matieres-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <h1 class="mb-6 text-xl font-semibold text-slate-900">Matières</h1>

      <form
        (ngSubmit)="valider()"
        class="mb-6 grid grid-cols-1 gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-4"
      >
        <div class="sm:col-span-2">
          <label class="mb-1 block text-sm font-medium text-slate-700">Nom</label>
          <input [(ngModel)]="nom" name="nom" required maxlength="100" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Code</label>
          <input [(ngModel)]="code" name="code" required maxlength="20" placeholder="MATH" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Coefficient</label>
          <input
            [(ngModel)]="coefficientDefaut"
            name="coefficientDefaut"
            type="number"
            step="0.5"
            min="0.5"
            max="10"
            required
            class="champ"
          />
        </div>
        <div class="flex items-end gap-2 sm:col-span-4 sm:justify-end">
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
              <th class="px-4 py-3">Nom</th>
              <th class="px-4 py-3">Code</th>
              <th class="px-4 py-3">Coefficient</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @for (item of items(); track item.id) {
              <tr>
                <td class="px-4 py-3 font-medium text-slate-900">{{ item.nom }}</td>
                <td class="px-4 py-3 text-slate-600">{{ item.code }}</td>
                <td class="px-4 py-3 text-slate-600">{{ item.coefficientDefaut }}</td>
                <td class="px-4 py-3 text-right">
                  <button (click)="editer(item)" class="lien">Modifier</button>
                  <button (click)="supprimer(item)" class="lien text-red-600">Supprimer</button>
                </td>
              </tr>
            } @empty {
              <tr>
                <td colspan="4" class="px-4 py-6 text-center text-slate-400">Aucune matière.</td>
              </tr>
            }
          </tbody>
        </table>
      </div>
    </div>
  `,
})
export class MatieresPage {
  private readonly http = inject(HttpClient);
  private readonly api = crudApi<Matiere>(this.http, `${environment.apiUrl}/matieres`);

  readonly items = signal<Matiere[]>([]);
  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);
  readonly enEdition = signal<string | null>(null);

  nom = '';
  code = '';
  coefficientDefaut = 1;

  constructor() {
    void this.charger();
  }

  async charger(): Promise<void> {
    this.items.set(await this.api.lister());
  }

  editer(item: Matiere): void {
    this.enEdition.set(item.id);
    this.nom = item.nom;
    this.code = item.code;
    this.coefficientDefaut = item.coefficientDefaut;
  }

  annuler(): void {
    this.enEdition.set(null);
    this.nom = '';
    this.code = '';
    this.coefficientDefaut = 1;
  }

  async valider(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    const payload = { nom: this.nom, code: this.code, coefficientDefaut: Number(this.coefficientDefaut) };
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

  async supprimer(item: Matiere): Promise<void> {
    if (!confirm(`Supprimer la matière ${item.nom} ?`)) return;
    await this.api.supprimer(item.id);
    await this.charger();
  }
}
