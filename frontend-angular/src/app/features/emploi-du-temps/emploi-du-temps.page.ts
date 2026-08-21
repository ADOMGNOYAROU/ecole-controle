import { HttpClient } from '@angular/common/http';
import { Component, computed, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { firstValueFrom, map } from 'rxjs';
import { toSignal } from '@angular/core/rxjs-interop';
import { environment } from '../../../environments/environment';
import { AuthService } from '../../core/auth.service';
import { crudApi, messageErreur } from '../../core/crud';

const JOURS: { valeur: number; nom: string }[] = [
  { valeur: 1, nom: 'Lundi' },
  { valeur: 2, nom: 'Mardi' },
  { valeur: 3, nom: 'Mercredi' },
  { valeur: 4, nom: 'Jeudi' },
  { valeur: 5, nom: 'Vendredi' },
  { valeur: 6, nom: 'Samedi' },
];

interface Classe {
  id: string;
  nom: string;
}
interface Matiere {
  id: string;
  nom: string;
}
interface Enseignant {
  id: string;
  nom: string;
  prenom: string;
}
interface Creneau {
  id: string;
  classeId: string;
  matiereId: string;
  enseignantId: string | null;
  jourSemaine: number;
  heureDebut: string;
  heureFin: string;
  salle: string | null;
}

@Component({
  selector: 'app-emploi-du-temps-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Emploi du temps</h1>
        <select [(ngModel)]="classeId" (ngModelChange)="charger()" class="champ w-56">
          @for (classe of classes(); track classe.id) {
            <option [value]="classe.id">{{ classe.nom }}</option>
          }
        </select>
      </div>

      @if (estAdmin()) {
        <form
          (ngSubmit)="ajouter()"
          class="mb-6 grid grid-cols-1 gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-6"
        >
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Jour</label>
            <select [(ngModel)]="jourSemaine" name="jourSemaine" class="champ">
              @for (jour of jours; track jour.valeur) {
                <option [value]="jour.valeur">{{ jour.nom }}</option>
              }
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Matière</label>
            <select [(ngModel)]="matiereId" name="matiereId" required class="champ">
              <option value="" disabled>Choisir…</option>
              @for (matiere of matieres(); track matiere.id) {
                <option [value]="matiere.id">{{ matiere.nom }}</option>
              }
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Enseignant</label>
            <select [(ngModel)]="enseignantId" name="enseignantId" class="champ">
              <option value="">Aucun</option>
              @for (enseignant of enseignants(); track enseignant.id) {
                <option [value]="enseignant.id">{{ enseignant.prenom }} {{ enseignant.nom }}</option>
              }
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Début</label>
            <input [(ngModel)]="heureDebut" name="heureDebut" type="time" required class="champ" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Fin</label>
            <input [(ngModel)]="heureFin" name="heureFin" type="time" required class="champ" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Salle</label>
            <input [(ngModel)]="salle" name="salle" maxlength="50" class="champ" />
          </div>
          <div class="sm:col-span-6 flex justify-end">
            <button type="submit" [disabled]="enCours()" class="btn-primary">Ajouter le créneau</button>
          </div>
          @if (erreur()) {
            <p class="text-sm text-red-600 sm:col-span-6">{{ erreur() }}</p>
          }
        </form>
      }

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @for (jour of jours; track jour.valeur) {
          <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">{{ jour.nom }}</h2>
            <div class="space-y-2">
              @for (creneau of parJour(jour.valeur); track creneau.id) {
                <div class="flex items-center justify-between rounded-md bg-slate-50 px-3 py-2 text-sm">
                  <div>
                    <p class="font-medium text-slate-900">{{ creneau.heureDebut }}–{{ creneau.heureFin }}</p>
                    <p class="text-slate-600">{{ nomMatiere(creneau.matiereId) }}</p>
                    @if (creneau.salle) {
                      <p class="text-xs text-slate-400">Salle {{ creneau.salle }}</p>
                    }
                  </div>
                  @if (estAdmin()) {
                    <button (click)="supprimer(creneau)" class="lien text-red-600">Suppr.</button>
                  }
                </div>
              } @empty {
                <p class="text-sm text-slate-400">—</p>
              }
            </div>
          </div>
        }
      </div>
    </div>
  `,
})
export class EmploiDuTempsPage {
  private readonly http = inject(HttpClient);
  private readonly authService = inject(AuthService);
  private readonly classesApi = crudApi<Classe>(this.http, `${environment.apiUrl}/classes`);
  private readonly matieresApi = crudApi<Matiere>(this.http, `${environment.apiUrl}/matieres`);
  private readonly enseignantsApi = crudApi<Enseignant>(this.http, `${environment.apiUrl}/enseignants`);

  readonly jours = JOURS;
  readonly classes = signal<Classe[]>([]);
  readonly matieres = signal<Matiere[]>([]);
  readonly enseignants = signal<Enseignant[]>([]);
  readonly creneaux = signal<Creneau[]>([]);
  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);

  readonly estAdmin = computed(() => this.role() === 'admin');
  private readonly role = toSignal(this.authService.user$.pipe(map((u) => u?.role ?? null)), { initialValue: null });

  classeId = '';
  matiereId = '';
  enseignantId = '';
  jourSemaine = 1;
  heureDebut = '08:00';
  heureFin = '09:00';
  salle = '';

  constructor() {
    void this.initialiser();
  }

  parJour(jour: number): Creneau[] {
    return this.creneaux()
      .filter((c) => c.jourSemaine === jour)
      .sort((a, b) => a.heureDebut.localeCompare(b.heureDebut));
  }

  nomMatiere(id: string): string {
    return this.matieres().find((m) => m.id === id)?.nom ?? '—';
  }

  private async initialiser(): Promise<void> {
    const [classes, matieres, enseignants] = await Promise.all([
      this.classesApi.lister(),
      this.matieresApi.lister(),
      this.enseignantsApi.lister(),
    ]);
    this.classes.set(classes);
    this.matieres.set(matieres);
    this.enseignants.set(enseignants);
    this.classeId = classes[0]?.id ?? '';
    await this.charger();
  }

  async charger(): Promise<void> {
    if (!this.classeId) return;
    const params = new URLSearchParams({ classeId: this.classeId });
    this.creneaux.set(
      await firstValueFrom(this.http.get<Creneau[]>(`${environment.apiUrl}/emploi-du-temps?${params.toString()}`)),
    );
  }

  async ajouter(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    try {
      await firstValueFrom(
        this.http.post(`${environment.apiUrl}/emploi-du-temps`, {
          classeId: this.classeId,
          matiereId: this.matiereId,
          enseignantId: this.enseignantId || undefined,
          jourSemaine: Number(this.jourSemaine),
          heureDebut: this.heureDebut,
          heureFin: this.heureFin,
          salle: this.salle || undefined,
        }),
      );
      this.matiereId = '';
      this.enseignantId = '';
      this.salle = '';
      await this.charger();
    } catch (error) {
      this.erreur.set(messageErreur(error));
    } finally {
      this.enCours.set(false);
    }
  }

  async supprimer(creneau: Creneau): Promise<void> {
    if (!confirm('Supprimer ce créneau ?')) return;
    await firstValueFrom(this.http.delete(`${environment.apiUrl}/emploi-du-temps/${creneau.id}`));
    await this.charger();
  }
}
