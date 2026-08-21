import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { environment } from '../../../environments/environment';
import { crudApi, messageErreur } from '../../core/crud';
import { HorodatageFirestore, versDateInput } from '../../core/dates';

interface Matiere {
  id: string;
  nom: string;
}
interface Classe {
  id: string;
  nom: string;
}
interface Enseignant {
  id: string;
  nom: string;
  prenom: string;
  telephone: string | null;
  email: string | null;
  specialite: string | null;
  dateEmbauche: HorodatageFirestore | null;
  matiereIds: string[];
  classeIds: string[];
}

@Component({
  selector: 'app-enseignants-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <h1 class="mb-6 text-xl font-semibold text-slate-900">Enseignants</h1>

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
          <label class="mb-1 block text-sm font-medium text-slate-700">Spécialité</label>
          <input [(ngModel)]="specialite" name="specialite" maxlength="150" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Téléphone</label>
          <input [(ngModel)]="telephone" name="telephone" maxlength="30" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
          <input [(ngModel)]="email" name="email" type="email" maxlength="150" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Date d'embauche</label>
          <input [(ngModel)]="dateEmbauche" name="dateEmbauche" type="date" class="champ" />
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Matières enseignées</label>
          <div class="max-h-32 space-y-1 overflow-y-auto rounded-md border border-slate-300 p-2">
            @for (matiere of matieres(); track matiere.id) {
              <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" [checked]="matiereIds.includes(matiere.id)" (change)="basculer(matiereIds, matiere.id)" />
                {{ matiere.nom }}
              </label>
            }
          </div>
        </div>
        <div class="sm:col-span-2">
          <label class="mb-1 block text-sm font-medium text-slate-700">Classes</label>
          <div class="max-h-32 space-y-1 overflow-y-auto rounded-md border border-slate-300 p-2">
            @for (classe of classes(); track classe.id) {
              <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" [checked]="classeIds.includes(classe.id)" (change)="basculer(classeIds, classe.id)" />
                {{ classe.nom }}
              </label>
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
              <th class="px-4 py-3">Spécialité</th>
              <th class="px-4 py-3">Contact</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @for (item of items(); track item.id) {
              <tr>
                <td class="px-4 py-3 font-medium text-slate-900">{{ item.prenom }} {{ item.nom }}</td>
                <td class="px-4 py-3 text-slate-600">{{ item.specialite ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ item.email ?? item.telephone ?? '—' }}</td>
                <td class="px-4 py-3 text-right">
                  <button (click)="editer(item)" class="lien">Modifier</button>
                  <button (click)="supprimer(item)" class="lien text-red-600">Supprimer</button>
                </td>
              </tr>
            } @empty {
              <tr>
                <td colspan="4" class="px-4 py-6 text-center text-slate-400">Aucun enseignant.</td>
              </tr>
            }
          </tbody>
        </table>
      </div>
    </div>
  `,
})
export class EnseignantsPage {
  private readonly http = inject(HttpClient);
  private readonly api = crudApi<Enseignant>(this.http, `${environment.apiUrl}/enseignants`);
  private readonly matieresApi = crudApi<Matiere>(this.http, `${environment.apiUrl}/matieres`);
  private readonly classesApi = crudApi<Classe>(this.http, `${environment.apiUrl}/classes`);

  readonly items = signal<Enseignant[]>([]);
  readonly matieres = signal<Matiere[]>([]);
  readonly classes = signal<Classe[]>([]);
  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);
  readonly enEdition = signal<string | null>(null);

  nom = '';
  prenom = '';
  telephone = '';
  email = '';
  specialite = '';
  dateEmbauche = '';
  matiereIds: string[] = [];
  classeIds: string[] = [];

  constructor() {
    void this.charger();
  }

  async charger(): Promise<void> {
    const [items, matieres, classes] = await Promise.all([
      this.api.lister(),
      this.matieresApi.lister(),
      this.classesApi.lister(),
    ]);
    this.items.set(items);
    this.matieres.set(matieres);
    this.classes.set(classes);
  }

  basculer(liste: string[], id: string): void {
    const index = liste.indexOf(id);
    if (index === -1) {
      liste.push(id);
    } else {
      liste.splice(index, 1);
    }
  }

  editer(item: Enseignant): void {
    this.enEdition.set(item.id);
    this.nom = item.nom;
    this.prenom = item.prenom;
    this.telephone = item.telephone ?? '';
    this.email = item.email ?? '';
    this.specialite = item.specialite ?? '';
    this.dateEmbauche = versDateInput(item.dateEmbauche);
    this.matiereIds = [...item.matiereIds];
    this.classeIds = [...item.classeIds];
  }

  annuler(): void {
    this.enEdition.set(null);
    this.nom = '';
    this.prenom = '';
    this.telephone = '';
    this.email = '';
    this.specialite = '';
    this.dateEmbauche = '';
    this.matiereIds = [];
    this.classeIds = [];
  }

  async valider(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    const payload = {
      nom: this.nom,
      prenom: this.prenom,
      telephone: this.telephone || undefined,
      email: this.email || undefined,
      specialite: this.specialite || undefined,
      dateEmbauche: this.dateEmbauche || undefined,
      matiereIds: this.matiereIds,
      classeIds: this.classeIds,
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

  async supprimer(item: Enseignant): Promise<void> {
    if (!confirm(`Supprimer ${item.prenom} ${item.nom} ?`)) return;
    await this.api.supprimer(item.id);
    await this.charger();
  }
}
