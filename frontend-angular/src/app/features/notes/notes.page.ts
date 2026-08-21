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
interface Matiere {
  id: string;
  nom: string;
}
interface Trimestre {
  id: string;
  nom: string;
}
interface EleveLeger {
  id: string;
  nom: string;
  prenom: string;
  matricule: string;
}
interface Note {
  id: string;
  eleveId: string;
  valeur: number;
  bareme: number;
  type: string;
  dateEvaluation: string;
}

@Component({
  selector: 'app-notes-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Saisie des notes</h1>
        <button (click)="telechargerRapport()" [disabled]="!peutCharger()" class="btn-ghost">
          Télécharger le rapport PDF
        </button>
      </div>

      <div class="mb-6 grid grid-cols-1 gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-6">
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Classe</label>
          <select [(ngModel)]="classeId" (ngModelChange)="chargerContexte()" class="champ">
            <option value="" disabled>Choisir…</option>
            @for (classe of classes(); track classe.id) {
              <option [value]="classe.id">{{ classe.nom }}</option>
            }
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Matière</label>
          <select [(ngModel)]="matiereId" (ngModelChange)="chargerContexte()" class="champ">
            <option value="" disabled>Choisir…</option>
            @for (matiere of matieres(); track matiere.id) {
              <option [value]="matiere.id">{{ matiere.nom }}</option>
            }
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Trimestre</label>
          <select [(ngModel)]="trimestreId" (ngModelChange)="chargerContexte()" class="champ">
            <option value="" disabled>Choisir…</option>
            @for (trimestre of trimestres(); track trimestre.id) {
              <option [value]="trimestre.id">{{ trimestre.nom }}</option>
            }
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Type</label>
          <select [(ngModel)]="type" class="champ">
            <option value="devoir">Devoir</option>
            <option value="composition">Composition</option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Barème</label>
          <input [(ngModel)]="bareme" type="number" min="1" max="100" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Coefficient</label>
          <input [(ngModel)]="coefficient" type="number" step="0.5" min="0.5" max="10" class="champ" />
        </div>
        <div class="sm:col-span-2">
          <label class="mb-1 block text-sm font-medium text-slate-700">Date d'évaluation</label>
          <input [(ngModel)]="dateEvaluation" type="date" class="champ" />
        </div>
        <div class="sm:col-span-4 flex items-end">
          <button (click)="chargerEleves()" [disabled]="!peutCharger()" class="btn-ghost">Charger les élèves</button>
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
                <th class="px-4 py-3 w-32">Note / {{ bareme }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @for (eleve of eleves(); track eleve.id) {
                <tr>
                  <td class="px-4 py-3 text-slate-600">{{ eleve.matricule }}</td>
                  <td class="px-4 py-3 font-medium text-slate-900">{{ eleve.prenom }} {{ eleve.nom }}</td>
                  <td class="px-4 py-3">
                    <input
                      [(ngModel)]="valeurs[eleve.id]"
                      [name]="'valeur-' + eleve.id"
                      type="number"
                      min="0"
                      [max]="bareme"
                      step="0.25"
                      class="champ"
                    />
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
            <button type="submit" [disabled]="enCours()" class="btn-primary">Enregistrer les notes</button>
          </div>
        </form>
      }

      @if (notesExistantes().length > 0) {
        <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
          <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="px-4 py-3">Élève</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Note</th>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @for (note of notesExistantes(); track note.id) {
                <tr>
                  <td class="px-4 py-3 font-medium text-slate-900">{{ nomEleve(note.eleveId) }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ note.type }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ note.valeur }} / {{ note.bareme }}</td>
                  <td class="px-4 py-3 text-slate-600">{{ note.dateEvaluation }}</td>
                  <td class="px-4 py-3 text-right">
                    <button (click)="supprimerNote(note)" class="lien text-red-600">Supprimer</button>
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
export class NotesPage {
  private readonly http = inject(HttpClient);
  private readonly classesApi = crudApi<Classe>(this.http, `${environment.apiUrl}/classes`);
  private readonly matieresApi = crudApi<Matiere>(this.http, `${environment.apiUrl}/matieres`);
  private readonly trimestresApi = crudApi<Trimestre>(this.http, `${environment.apiUrl}/trimestres`);
  private readonly notesApi = crudApi<Note>(this.http, `${environment.apiUrl}/notes`);

  readonly classes = signal<Classe[]>([]);
  readonly matieres = signal<Matiere[]>([]);
  readonly trimestres = signal<Trimestre[]>([]);
  readonly eleves = signal<EleveLeger[]>([]);
  readonly notesExistantes = signal<Note[]>([]);
  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);

  classeId = '';
  matiereId = '';
  trimestreId = '';
  type: 'devoir' | 'composition' = 'devoir';
  bareme = 20;
  coefficient = 1;
  dateEvaluation = new Date().toISOString().slice(0, 10);
  valeurs: Record<string, number> = {};

  constructor() {
    void this.initialiser();
  }

  peutCharger(): boolean {
    return !!(this.classeId && this.matiereId && this.trimestreId);
  }

  nomEleve(eleveId: string): string {
    const eleve = this.eleves().find((e) => e.id === eleveId);
    return eleve ? `${eleve.prenom} ${eleve.nom}` : '—';
  }

  private async initialiser(): Promise<void> {
    const [classes, matieres, trimestres] = await Promise.all([
      this.classesApi.lister(),
      this.matieresApi.lister(),
      this.trimestresApi.lister(),
    ]);
    this.classes.set(classes);
    this.matieres.set(matieres);
    this.trimestres.set(trimestres);
  }

  async chargerEleves(): Promise<void> {
    if (!this.peutCharger()) return;
    this.eleves.set(
      await firstValueFrom(
        this.http.get<EleveLeger[]>(`${environment.apiUrl}/notes/classes/${this.classeId}/eleves`),
      ),
    );
    this.valeurs = {};
    await this.chargerContexte();
  }

  async chargerContexte(): Promise<void> {
    if (!this.peutCharger()) {
      this.notesExistantes.set([]);
      return;
    }
    const params = new URLSearchParams({
      classeId: this.classeId,
      matiereId: this.matiereId,
      trimestreId: this.trimestreId,
    });
    this.notesExistantes.set(
      await firstValueFrom(this.http.get<Note[]>(`${environment.apiUrl}/notes?${params.toString()}`)),
    );
  }

  async enregistrer(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    const notes = this.eleves()
      .filter((e) => this.valeurs[e.id] !== undefined && this.valeurs[e.id] !== null)
      .map((e) => ({ eleveId: e.id, valeur: Number(this.valeurs[e.id]) }));

    try {
      await firstValueFrom(
        this.http.post(`${environment.apiUrl}/notes/bulk`, {
          classeId: this.classeId,
          matiereId: this.matiereId,
          trimestreId: this.trimestreId,
          type: this.type,
          bareme: Number(this.bareme),
          coefficient: Number(this.coefficient),
          dateEvaluation: this.dateEvaluation,
          notes,
        }),
      );
      this.valeurs = {};
      await this.chargerContexte();
    } catch (error) {
      this.erreur.set(messageErreur(error));
    } finally {
      this.enCours.set(false);
    }
  }

  async supprimerNote(note: Note): Promise<void> {
    if (!confirm('Supprimer cette note ?')) return;
    await this.notesApi.supprimer(note.id);
    await this.chargerContexte();
  }

  async telechargerRapport(): Promise<void> {
    const params = new URLSearchParams({
      classeId: this.classeId,
      matiereId: this.matiereId,
      trimestreId: this.trimestreId,
    });
    await telechargerPdf(this.http, `${environment.apiUrl}/notes/rapport?${params.toString()}`, 'rapport-notes.pdf');
  }
}
