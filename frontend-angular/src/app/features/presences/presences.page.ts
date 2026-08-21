import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import { crudApi, messageErreur } from '../../core/crud';

interface Classe {
  id: string;
  nom: string;
}
interface EleveLeger {
  id: string;
  nom: string;
  prenom: string;
  matricule: string;
}
interface Presence {
  id: string;
  eleveId: string;
  date: string;
  statut: 'present' | 'absent' | 'retard';
  motif: string | null;
}

type Statut = 'present' | 'absent' | 'retard';

@Component({
  selector: 'app-presences-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <h1 class="mb-6 text-xl font-semibold text-slate-900">Appel</h1>

      <div class="mb-6 grid grid-cols-1 gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-4">
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Classe</label>
          <select [(ngModel)]="classeId" (ngModelChange)="chargerExistantes()" class="champ">
            <option value="" disabled>Choisir…</option>
            @for (classe of classes(); track classe.id) {
              <option [value]="classe.id">{{ classe.nom }}</option>
            }
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Date</label>
          <input [(ngModel)]="date" type="date" (ngModelChange)="chargerExistantes()" class="champ" />
        </div>
        <div class="sm:col-span-2 flex items-end">
          <button (click)="chargerEleves()" [disabled]="!classeId" class="btn-ghost">Faire l'appel</button>
        </div>
      </div>

      @if (eleves().length > 0) {
        <form
          (ngSubmit)="enregistrer()"
          class="mb-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200"
        >
          <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3">Matricule</th>
                <th class="px-4 py-3">Élève</th>
                <th class="px-4 py-3 w-40">Statut</th>
                <th class="px-4 py-3">Motif</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @for (eleve of eleves(); track eleve.id) {
                <tr>
                  <td class="px-4 py-3 text-slate-600">{{ eleve.matricule }}</td>
                  <td class="px-4 py-3 font-medium text-slate-900">{{ eleve.prenom }} {{ eleve.nom }}</td>
                  <td class="px-4 py-3">
                    <select [(ngModel)]="statuts[eleve.id]" [name]="'statut-' + eleve.id" class="champ">
                      <option value="present">Présent</option>
                      <option value="absent">Absent</option>
                      <option value="retard">Retard</option>
                    </select>
                  </td>
                  <td class="px-4 py-3">
                    @if (statuts[eleve.id] === 'absent') {
                      <input
                        [(ngModel)]="motifs[eleve.id]"
                        [name]="'motif-' + eleve.id"
                        placeholder="Motif de l'absence"
                        class="champ"
                      />
                    }
                  </td>
                </tr>
              }
            </tbody>
          </table>
          <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
            @if (erreur()) {
              <p class="text-sm text-red-600">{{ erreur() }}</p>
            } @else {
              <span></span>
            }
            <button type="submit" [disabled]="enCours()" class="btn-primary">Enregistrer l'appel</button>
          </div>
        </form>
      }

      @if (presencesExistantes().length > 0) {
        <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
          <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3">Élève</th>
                <th class="px-4 py-3">Statut</th>
                <th class="px-4 py-3">Motif</th>
                <th class="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @for (presence of presencesExistantes(); track presence.id) {
                <tr>
                  <td class="px-4 py-3 font-medium text-slate-900">{{ nomEleve(presence.eleveId) }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ presence.statut }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ presence.motif ?? '—' }}</td>
                  <td class="px-4 py-3 text-right">
                    <button (click)="supprimerPresence(presence)" class="lien text-red-600">Supprimer</button>
                  </td>
                </tr>
              }
            </tbody>
          </table>
        </div>
      }
    </div>
  `,
})
export class PresencesPage {
  private readonly http = inject(HttpClient);
  private readonly classesApi = crudApi<Classe>(this.http, `${environment.apiUrl}/classes`);
  private readonly presencesApi = crudApi<Presence>(this.http, `${environment.apiUrl}/presences`);

  readonly classes = signal<Classe[]>([]);
  readonly eleves = signal<EleveLeger[]>([]);
  readonly presencesExistantes = signal<Presence[]>([]);
  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);

  classeId = '';
  date = new Date().toISOString().slice(0, 10);
  statuts: Record<string, Statut> = {};
  motifs: Record<string, string> = {};

  constructor() {
    void this.initialiser();
  }

  nomEleve(eleveId: string): string {
    const eleve = this.eleves().find((e) => e.id === eleveId);
    return eleve ? `${eleve.prenom} ${eleve.nom}` : '—';
  }

  private async initialiser(): Promise<void> {
    this.classes.set(await this.classesApi.lister());
  }

  async chargerEleves(): Promise<void> {
    if (!this.classeId) return;
    const eleves = await firstValueFrom(
      this.http.get<EleveLeger[]>(`${environment.apiUrl}/presences/classes/${this.classeId}/eleves`),
    );
    this.eleves.set(eleves);
    this.statuts = Object.fromEntries(eleves.map((e) => [e.id, 'present' as Statut]));
    this.motifs = {};
    await this.chargerExistantes();
  }

  async chargerExistantes(): Promise<void> {
    if (!this.classeId) {
      this.presencesExistantes.set([]);
      return;
    }
    const params = new URLSearchParams({ classeId: this.classeId, date: this.date });
    this.presencesExistantes.set(
      await firstValueFrom(this.http.get<Presence[]>(`${environment.apiUrl}/presences?${params.toString()}`)),
    );
  }

  async enregistrer(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    const presences = this.eleves().map((e) => ({
      eleveId: e.id,
      statut: this.statuts[e.id],
      motif: this.statuts[e.id] === 'absent' ? this.motifs[e.id] : undefined,
    }));

    try {
      await firstValueFrom(
        this.http.post(`${environment.apiUrl}/presences/bulk`, {
          classeId: this.classeId,
          date: this.date,
          presences,
        }),
      );
      await this.chargerExistantes();
    } catch (error) {
      this.erreur.set(messageErreur(error));
    } finally {
      this.enCours.set(false);
    }
  }

  async supprimerPresence(presence: Presence): Promise<void> {
    if (!confirm('Supprimer cette présence ?')) return;
    await this.presencesApi.supprimer(presence.id);
    await this.chargerExistantes();
  }
}
