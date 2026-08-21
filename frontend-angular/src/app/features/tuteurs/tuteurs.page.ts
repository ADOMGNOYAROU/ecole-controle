import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { environment } from '../../../environments/environment';
import { crudApi, messageErreur } from '../../core/crud';
import { telechargerPdf } from '../../core/telecharger-pdf';

interface Eleve {
  id: string;
  nom: string;
  prenom: string;
}

interface TuteurEleve {
  id: string;
  lienParente: string;
}

interface Tuteur {
  id: string;
  nom: string;
  prenom: string;
  telephone: string;
  email: string | null;
  profession: string | null;
  adresse: string | null;
  eleves: TuteurEleve[];
}

@Component({
  selector: 'app-tuteurs-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Parents / Tuteurs</h1>
        <button (click)="telechargerRapport()" class="btn-ghost">Télécharger le rapport PDF</button>
      </div>

      <form
        (ngSubmit)="valider()"
        class="mb-6 grid grid-cols-1 gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-3"
      >
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Nom</label>
          <input [(ngModel)]="nom" name="nom" required maxlength="100" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Prénom</label>
          <input [(ngModel)]="prenom" name="prenom" required maxlength="100" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Téléphone</label>
          <input [(ngModel)]="telephone" name="telephone" required maxlength="30" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
          <input [(ngModel)]="email" name="email" type="email" maxlength="150" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Profession</label>
          <input [(ngModel)]="profession" name="profession" maxlength="150" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Adresse</label>
          <input [(ngModel)]="adresse" name="adresse" maxlength="255" class="champ" />
        </div>

        <div class="sm:col-span-3">
          <label class="mb-1 block text-sm font-medium text-slate-700">Élèves rattachés</label>
          <div class="space-y-2 rounded-md border border-slate-300 p-3">
            @for (eleve of eleves(); track eleve.id) {
              <div class="flex items-center gap-3">
                <label class="flex w-56 items-center gap-2 text-sm text-slate-700">
                  <input type="checkbox" [checked]="estSelectionne(eleve.id)" (change)="basculerEleve(eleve.id)" />
                  {{ eleve.prenom }} {{ eleve.nom }}
                </label>
                @if (estSelectionne(eleve.id)) {
                  <input
                    [ngModel]="lienParente(eleve.id)"
                    (ngModelChange)="modifierLien(eleve.id, $event)"
                    name="lien-{{ eleve.id }}"
                    placeholder="Lien de parenté (ex. père, mère…)"
                    class="champ flex-1"
                  />
                }
              </div>
            }
          </div>
        </div>

        <div class="flex items-end gap-2 sm:col-span-3 sm:justify-end">
          @if (enEdition()) {
            <button type="button" (click)="annuler()" class="btn-ghost">Annuler</button>
          }
          <button type="submit" [disabled]="enCours()" class="btn-primary">
            {{ enEdition() ? 'Enregistrer' : 'Ajouter' }}
          </button>
        </div>
        @if (erreur()) {
          <p class="text-sm text-red-600 sm:col-span-3">{{ erreur() }}</p>
        }
      </form>

      <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
          <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
              <th class="px-4 py-3">Nom</th>
              <th class="px-4 py-3">Téléphone</th>
              <th class="px-4 py-3">Élèves</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @for (item of items(); track item.id) {
              <tr>
                <td class="px-4 py-3 font-medium text-slate-900">{{ item.prenom }} {{ item.nom }}</td>
                <td class="px-4 py-3 text-slate-600">{{ item.telephone }}</td>
                <td class="px-4 py-3 text-slate-600">{{ nomsEleves(item) }}</td>
                <td class="px-4 py-3 text-right">
                  <button (click)="editer(item)" class="lien">Modifier</button>
                  <button (click)="supprimer(item)" class="lien text-red-600">Supprimer</button>
                </td>
              </tr>
            } @empty {
              <tr>
                <td colspan="4" class="px-4 py-6 text-center text-slate-400">Aucun tuteur.</td>
              </tr>
            }
          </tbody>
        </table>
      </div>
    </div>
  `,
})
export class TuteursPage {
  private readonly http = inject(HttpClient);
  private readonly api = crudApi<Tuteur>(this.http, `${environment.apiUrl}/tuteurs`);
  private readonly elevesApi = crudApi<Eleve>(this.http, `${environment.apiUrl}/eleves`);

  readonly items = signal<Tuteur[]>([]);
  readonly eleves = signal<Eleve[]>([]);
  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);
  readonly enEdition = signal<string | null>(null);

  nom = '';
  prenom = '';
  telephone = '';
  email = '';
  profession = '';
  adresse = '';
  elevesSelectionnes: TuteurEleve[] = [];

  constructor() {
    void this.charger();
  }

  async charger(): Promise<void> {
    const [items, eleves] = await Promise.all([this.api.lister(), this.elevesApi.lister()]);
    this.items.set(items);
    this.eleves.set(eleves);
  }

  nomsEleves(tuteur: Tuteur): string {
    if (tuteur.eleves.length === 0) return '—';
    return tuteur.eleves
      .map((lien) => {
        const eleve = this.eleves().find((e) => e.id === lien.id);
        return eleve ? `${eleve.prenom} ${eleve.nom} (${lien.lienParente})` : lien.lienParente;
      })
      .join(', ');
  }

  estSelectionne(eleveId: string): boolean {
    return this.elevesSelectionnes.some((e) => e.id === eleveId);
  }

  lienParente(eleveId: string): string {
    return this.elevesSelectionnes.find((e) => e.id === eleveId)?.lienParente ?? '';
  }

  basculerEleve(eleveId: string): void {
    const index = this.elevesSelectionnes.findIndex((e) => e.id === eleveId);
    if (index === -1) {
      this.elevesSelectionnes.push({ id: eleveId, lienParente: '' });
    } else {
      this.elevesSelectionnes.splice(index, 1);
    }
  }

  modifierLien(eleveId: string, lienParente: string): void {
    const entree = this.elevesSelectionnes.find((e) => e.id === eleveId);
    if (entree) {
      entree.lienParente = lienParente;
    }
  }

  editer(item: Tuteur): void {
    this.enEdition.set(item.id);
    this.nom = item.nom;
    this.prenom = item.prenom;
    this.telephone = item.telephone;
    this.email = item.email ?? '';
    this.profession = item.profession ?? '';
    this.adresse = item.adresse ?? '';
    this.elevesSelectionnes = item.eleves.map((e) => ({ ...e }));
  }

  annuler(): void {
    this.enEdition.set(null);
    this.nom = '';
    this.prenom = '';
    this.telephone = '';
    this.email = '';
    this.profession = '';
    this.adresse = '';
    this.elevesSelectionnes = [];
  }

  async valider(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    const payload = {
      nom: this.nom,
      prenom: this.prenom,
      telephone: this.telephone,
      email: this.email || undefined,
      profession: this.profession || undefined,
      adresse: this.adresse || undefined,
      eleves: this.elevesSelectionnes,
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

  async supprimer(item: Tuteur): Promise<void> {
    if (!confirm(`Supprimer ${item.prenom} ${item.nom} ?`)) return;
    await this.api.supprimer(item.id);
    await this.charger();
  }

  async telechargerRapport(): Promise<void> {
    await telechargerPdf(this.http, `${environment.apiUrl}/tuteurs/rapport`, 'rapport-tuteurs.pdf');
  }
}
