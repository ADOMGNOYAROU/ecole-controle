import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';

interface Trimestre {
  id: string;
  nom: string;
  ordre: number;
}
interface MatiereBulletin {
  nom: string;
  moyenne: number | null;
  coefficient: number;
}
interface NotesReponse {
  trimestres: Trimestre[];
  trimestre: Trimestre | null;
  donnees: { matieres: MatiereBulletin[]; moyenneGenerale: number | null } | null;
}
interface Presence {
  id: string;
  date: string;
  statut: 'present' | 'absent' | 'retard';
  motif: string | null;
}
interface Paiement {
  id: string;
  type: string;
  montant: number;
  montantPaye: number;
  dateEcheance: string;
  statut: string;
}

type Onglet = 'notes' | 'presences' | 'paiements';

@Component({
  selector: 'app-mon-espace-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <h1 class="mb-6 text-xl font-semibold text-slate-900">Mon espace</h1>

      <div class="mb-6 flex gap-2">
        <button (click)="ouvrirOnglet('notes')" [class]="classeOnglet('notes')">Mes notes</button>
        <button (click)="ouvrirOnglet('presences')" [class]="classeOnglet('presences')">Mes présences</button>
        <button (click)="ouvrirOnglet('paiements')" [class]="classeOnglet('paiements')">Mes paiements</button>
      </div>

      @if (onglet() === 'notes') {
        @if (notes(); as data) {
          <div class="mb-4 max-w-xs">
            <label class="mb-1 block text-sm font-medium text-slate-700">Trimestre</label>
            <select [ngModel]="data.trimestre?.id" (ngModelChange)="chargerNotes($event)" class="champ">
              @for (t of data.trimestres; track t.id) {
                <option [value]="t.id">{{ t.nom }}</option>
              }
            </select>
          </div>
          <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
              <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                  <th class="px-4 py-3">Matière</th>
                  <th class="px-4 py-3">Moyenne /20</th>
                  <th class="px-4 py-3">Coefficient</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                @for (m of data.donnees?.matieres ?? []; track m.nom) {
                  <tr>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ m.nom }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ m.moyenne ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ m.coefficient }}</td>
                  </tr>
                } @empty {
                  <tr>
                    <td colspan="3" class="px-4 py-6 text-center text-slate-400">Aucune note pour ce trimestre.</td>
                  </tr>
                }
              </tbody>
            </table>
            @if (data.donnees?.moyenneGenerale !== null && data.donnees?.moyenneGenerale !== undefined) {
              <p class="border-t border-slate-200 px-4 py-3 text-sm font-semibold text-slate-900">
                Moyenne générale : {{ data.donnees?.moyenneGenerale }}/20
              </p>
            }
          </div>
        }
      }

      @if (onglet() === 'presences') {
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
          <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Statut</th>
                <th class="px-4 py-3">Motif</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @for (p of presences(); track p.id) {
                <tr>
                  <td class="px-4 py-3 text-slate-600">{{ p.date }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ p.statut }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ p.motif ?? '—' }}</td>
                </tr>
              } @empty {
                <tr>
                  <td colspan="3" class="px-4 py-6 text-center text-slate-400">Aucune présence enregistrée.</td>
                </tr>
              }
            </tbody>
          </table>
        </div>
      }

      @if (onglet() === 'paiements') {
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
          <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Montant</th>
                <th class="px-4 py-3">Payé</th>
                <th class="px-4 py-3">Échéance</th>
                <th class="px-4 py-3">Statut</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @for (p of paiements(); track p.id) {
                <tr>
                  <td class="px-4 py-3 text-slate-600">{{ p.type }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ p.montant }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ p.montantPaye }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ p.dateEcheance }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ p.statut }}</td>
                </tr>
              } @empty {
                <tr>
                  <td colspan="5" class="px-4 py-6 text-center text-slate-400">Aucun paiement enregistré.</td>
                </tr>
              }
            </tbody>
          </table>
        </div>
      }
    </div>
  `,
})
export class MonEspacePage {
  private readonly http = inject(HttpClient);

  readonly onglet = signal<Onglet>('notes');
  readonly notes = signal<NotesReponse | null>(null);
  readonly presences = signal<Presence[]>([]);
  readonly paiements = signal<Paiement[]>([]);

  constructor() {
    void this.chargerNotes();
  }

  classeOnglet(onglet: Onglet): string {
    return this.onglet() === onglet ? 'btn-primary' : 'btn-ghost';
  }

  async ouvrirOnglet(onglet: Onglet): Promise<void> {
    this.onglet.set(onglet);
    if (onglet === 'notes' && !this.notes()) await this.chargerNotes();
    if (onglet === 'presences') await this.chargerPresences();
    if (onglet === 'paiements') await this.chargerPaiements();
  }

  async chargerNotes(trimestreId?: string): Promise<void> {
    const params = trimestreId ? `?trimestreId=${trimestreId}` : '';
    this.notes.set(await firstValueFrom(this.http.get<NotesReponse>(`${environment.apiUrl}/mon-espace/notes${params}`)));
  }

  async chargerPresences(): Promise<void> {
    this.presences.set(await firstValueFrom(this.http.get<Presence[]>(`${environment.apiUrl}/mon-espace/presences`)));
  }

  async chargerPaiements(): Promise<void> {
    this.paiements.set(await firstValueFrom(this.http.get<Paiement[]>(`${environment.apiUrl}/mon-espace/paiements`)));
  }
}
