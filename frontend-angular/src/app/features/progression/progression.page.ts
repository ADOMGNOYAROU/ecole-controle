import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import { crudApi, messageErreur } from '../../core/crud';

interface Eleve {
  id: string;
  nom: string;
  prenom: string;
}

interface MoyenneTrimestre {
  trimestreId: string;
  label: string;
  moyenne: number;
}
interface MoyenneMatiere {
  matiereId: string;
  nom: string;
  moyenne: number;
  nombreNotes: number;
}
interface AnalyseProgression {
  moyennesParTrimestre: MoyenneTrimestre[];
  pointsForts: MoyenneMatiere[];
  pointsFaibles: MoyenneMatiere[];
  tendance: 'hausse' | 'baisse' | 'stable';
  risque: {
    enRisque: boolean;
    niveau: 'faible' | 'moyen' | 'eleve';
    trimestresSousSeuil: number;
    seuil: number;
    derniereMoyenne: number | null;
  };
}

@Component({
  selector: 'app-progression-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <h1 class="mb-6 text-xl font-semibold text-slate-900">Progression &amp; risque d'échec</h1>

      <div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <label class="mb-1 block text-sm font-medium text-slate-700">Élève</label>
        <select [(ngModel)]="eleveId" (ngModelChange)="analyser()" class="champ max-w-sm">
          <option value="" disabled>Choisir…</option>
          @for (eleve of eleves(); track eleve.id) {
            <option [value]="eleve.id">{{ eleve.prenom }} {{ eleve.nom }}</option>
          }
        </select>
      </div>

      @if (erreur()) {
        <p class="text-sm text-red-600">{{ erreur() }}</p>
      }

      @if (analyse(); as data) {
        <div
          class="mb-6 rounded-xl p-6 shadow-sm ring-1"
          [class]="
            data.risque.niveau === 'eleve'
              ? 'bg-red-50 ring-red-200'
              : data.risque.niveau === 'moyen'
                ? 'bg-amber-50 ring-amber-200'
                : 'bg-emerald-50 ring-emerald-200'
          "
        >
          <p class="text-xs font-semibold uppercase tracking-wide"
             [class]="data.risque.niveau === 'eleve' ? 'text-red-700' : data.risque.niveau === 'moyen' ? 'text-amber-700' : 'text-emerald-700'">
            Niveau de risque : {{ data.risque.niveau }}
          </p>
          <p class="mt-1 text-2xl font-semibold text-slate-900">
            {{ data.risque.derniereMoyenne !== null ? data.risque.derniereMoyenne + '/20' : 'N/A' }}
          </p>
          <p class="mt-1 text-sm text-slate-600">
            Tendance : {{ data.tendance }} · {{ data.risque.trimestresSousSeuil }} trimestre(s) sous le seuil de
            {{ data.risque.seuil }}/20
          </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Moyennes par trimestre</h2>
            <div class="space-y-2">
              @for (m of data.moyennesParTrimestre; track m.trimestreId) {
                <div class="flex items-center justify-between text-sm">
                  <span class="text-slate-600">{{ m.label }}</span>
                  <span class="font-medium text-slate-900">{{ m.moyenne }}/20</span>
                </div>
              } @empty {
                <p class="text-sm text-slate-400">Aucune donnée.</p>
              }
            </div>
          </div>

          <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Points forts</h2>
            <div class="space-y-2">
              @for (m of data.pointsForts; track m.matiereId) {
                <div class="flex items-center justify-between text-sm">
                  <span class="text-slate-600">{{ m.nom }}</span>
                  <span class="font-medium text-emerald-700">{{ m.moyenne }}/20</span>
                </div>
              }
            </div>
          </div>

          <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200 sm:col-span-2">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Points faibles</h2>
            <div class="space-y-2">
              @for (m of data.pointsFaibles; track m.matiereId) {
                <div class="flex items-center justify-between text-sm">
                  <span class="text-slate-600">{{ m.nom }} ({{ m.nombreNotes }} note(s))</span>
                  <span class="font-medium text-red-700">{{ m.moyenne }}/20</span>
                </div>
              }
            </div>
          </div>
        </div>
      }
    </div>
  `,
})
export class ProgressionPage {
  private readonly http = inject(HttpClient);
  private readonly elevesApi = crudApi<Eleve>(this.http, `${environment.apiUrl}/eleves`);

  readonly eleves = signal<Eleve[]>([]);
  readonly analyse = signal<AnalyseProgression | null>(null);
  readonly erreur = signal<string | null>(null);

  eleveId = '';

  constructor() {
    void this.initialiser();
  }

  private async initialiser(): Promise<void> {
    this.eleves.set(await this.elevesApi.lister());
  }

  async analyser(): Promise<void> {
    if (!this.eleveId) return;
    this.erreur.set(null);
    this.analyse.set(null);
    try {
      this.analyse.set(
        await firstValueFrom(
          this.http.get<AnalyseProgression>(`${environment.apiUrl}/eleves/${this.eleveId}/progression`),
        ),
      );
    } catch (error) {
      this.erreur.set(messageErreur(error));
    }
  }
}
