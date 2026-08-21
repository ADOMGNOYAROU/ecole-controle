import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import { crudApi, messageErreur } from '../../core/crud';
import { telechargerPdf } from '../../core/telecharger-pdf';

interface Classe {
  id: string;
  nom: string;
}
interface Trimestre {
  id: string;
  nom: string;
}
interface Eleve {
  id: string;
  nom: string;
  prenom: string;
}
interface Bulletin {
  id: string;
  eleveId: string;
  moyenneGenerale: number | null;
  rang: number | null;
  appreciation: string;
  tauxPresence: number | null;
}

@Component({
  selector: 'app-bulletins-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <h1 class="mb-6 text-xl font-semibold text-slate-900">Bulletins</h1>

      <div class="mb-6 grid grid-cols-1 gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-4">
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Classe</label>
          <select [(ngModel)]="classeId" (ngModelChange)="charger()" class="champ">
            <option value="" disabled>Choisir…</option>
            @for (classe of classes(); track classe.id) {
              <option [value]="classe.id">{{ classe.nom }}</option>
            }
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Trimestre</label>
          <select [(ngModel)]="trimestreId" (ngModelChange)="charger()" class="champ">
            <option value="" disabled>Choisir…</option>
            @for (trimestre of trimestres(); track trimestre.id) {
              <option [value]="trimestre.id">{{ trimestre.nom }}</option>
            }
          </select>
        </div>
        <div class="sm:col-span-2 flex items-end">
          <button (click)="genererPourClasse()" [disabled]="!peutGenerer() || enCours()" class="btn-primary">
            {{ enCours() ? 'Génération…' : 'Générer les bulletins de la classe' }}
          </button>
        </div>
        @if (erreur()) {
          <p class="text-sm text-red-600 sm:col-span-4">{{ erreur() }}</p>
        }
      </div>

      @if (bulletins().length > 0) {
        <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
          <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3">Élève</th>
                <th class="px-4 py-3">Moyenne</th>
                <th class="px-4 py-3">Rang</th>
                <th class="px-4 py-3">Appréciation</th>
                <th class="px-4 py-3">Présence</th>
                <th class="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @for (bulletin of bulletinsTries(); track bulletin.id) {
                <tr>
                  <td class="px-4 py-3 font-medium text-slate-900">{{ nomEleve(bulletin.eleveId) }}</td>
                  <td class="px-4 py-3 text-slate-600">
                    {{ bulletin.moyenneGenerale !== null ? bulletin.moyenneGenerale + '/20' : 'N/A' }}
                  </td>
                  <td class="px-4 py-3 text-slate-600">{{ bulletin.rang ?? '—' }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ bulletin.appreciation }}</td>
                  <td class="px-4 py-3 text-slate-600">
                    {{ bulletin.tauxPresence !== null ? bulletin.tauxPresence + '%' : '—' }}
                  </td>
                  <td class="px-4 py-3 text-right">
                    <button (click)="telechargerBulletin(bulletin)" class="lien">Télécharger le PDF</button>
                  </td>
                </tr>
              }
            </tbody>
          </table>
        </div>
      } @else if (peutGenerer()) {
        <p class="text-sm text-slate-400">Aucun bulletin généré pour cette classe et ce trimestre.</p>
      }
    </div>
  `,
})
export class BulletinsPage {
  private readonly http = inject(HttpClient);
  private readonly classesApi = crudApi<Classe>(this.http, `${environment.apiUrl}/classes`);
  private readonly trimestresApi = crudApi<Trimestre>(this.http, `${environment.apiUrl}/trimestres`);
  private readonly elevesApi = crudApi<Eleve>(this.http, `${environment.apiUrl}/eleves`);

  readonly classes = signal<Classe[]>([]);
  readonly trimestres = signal<Trimestre[]>([]);
  readonly eleves = signal<Eleve[]>([]);
  readonly bulletins = signal<Bulletin[]>([]);
  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);

  classeId = '';
  trimestreId = '';

  constructor() {
    void this.initialiser();
  }

  peutGenerer(): boolean {
    return !!(this.classeId && this.trimestreId);
  }

  bulletinsTries(): Bulletin[] {
    return [...this.bulletins()].sort((a, b) => (a.rang ?? Infinity) - (b.rang ?? Infinity));
  }

  nomEleve(eleveId: string): string {
    const eleve = this.eleves().find((e) => e.id === eleveId);
    return eleve ? `${eleve.prenom} ${eleve.nom}` : '—';
  }

  private async initialiser(): Promise<void> {
    const [classes, trimestres, eleves] = await Promise.all([
      this.classesApi.lister(),
      this.trimestresApi.lister(),
      this.elevesApi.lister(),
    ]);
    this.classes.set(classes);
    this.trimestres.set(trimestres);
    this.eleves.set(eleves);
  }

  async charger(): Promise<void> {
    if (!this.peutGenerer()) {
      this.bulletins.set([]);
      return;
    }
    const params = new URLSearchParams({ classeId: this.classeId, trimestreId: this.trimestreId });
    this.bulletins.set(
      await firstValueFrom(this.http.get<Bulletin[]>(`${environment.apiUrl}/bulletins?${params.toString()}`)),
    );
  }

  async genererPourClasse(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    try {
      await firstValueFrom(
        this.http.post(`${environment.apiUrl}/bulletins/classes/${this.classeId}/trimestres/${this.trimestreId}`, {}),
      );
      await this.charger();
    } catch (error) {
      this.erreur.set(messageErreur(error));
    } finally {
      this.enCours.set(false);
    }
  }

  async telechargerBulletin(bulletin: Bulletin): Promise<void> {
    await telechargerPdf(
      this.http,
      `${environment.apiUrl}/bulletins/eleves/${bulletin.eleveId}/trimestres/${this.trimestreId}`,
      `bulletin-${bulletin.eleveId}.pdf`,
    );
  }
}
