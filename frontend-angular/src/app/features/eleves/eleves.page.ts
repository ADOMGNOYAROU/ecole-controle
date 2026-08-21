import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { environment } from '../../../environments/environment';
import { crudApi, messageErreur } from '../../core/crud';
import { HorodatageFirestore, versDateInput } from '../../core/dates';
import { telechargerPdf } from '../../core/telecharger-pdf';

interface Classe {
  id: string;
  nom: string;
}

interface Eleve {
  id: string;
  matricule: string;
  nom: string;
  prenom: string;
  sexe: 'M' | 'F';
  dateNaissance: HorodatageFirestore;
  lieuNaissance: string | null;
  adresse: string | null;
  telephone: string | null;
  email: string | null;
  classeId: string | null;
  statut: 'actif' | 'inactif' | 'diplome' | 'exclu';
  dateInscription: HorodatageFirestore;
  contactUrgenceNom: string | null;
  contactUrgenceTelephone: string | null;
}

@Component({
  selector: 'app-eleves-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Élèves</h1>
        <button (click)="telechargerRapport()" class="btn-ghost">Télécharger le rapport PDF</button>
      </div>

      <form
        (ngSubmit)="valider()"
        class="mb-6 grid grid-cols-1 gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-4"
      >
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Matricule</label>
          <input [(ngModel)]="matricule" name="matricule" required maxlength="50" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Nom</label>
          <input [(ngModel)]="nom" name="nom" required maxlength="100" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Prénom</label>
          <input [(ngModel)]="prenom" name="prenom" required maxlength="100" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Sexe</label>
          <select [(ngModel)]="sexe" name="sexe" required class="champ">
            <option value="M">Masculin</option>
            <option value="F">Féminin</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Date de naissance</label>
          <input [(ngModel)]="dateNaissance" name="dateNaissance" type="date" required class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Lieu de naissance</label>
          <input [(ngModel)]="lieuNaissance" name="lieuNaissance" maxlength="150" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Classe</label>
          <select [(ngModel)]="classeId" name="classeId" class="champ">
            <option value="">Aucune</option>
            @for (classe of classes(); track classe.id) {
              <option [value]="classe.id">{{ classe.nom }}</option>
            }
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Statut</label>
          <select [(ngModel)]="statut" name="statut" required class="champ">
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
            <option value="diplome">Diplômé</option>
            <option value="exclu">Exclu</option>
          </select>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Date d'inscription</label>
          <input [(ngModel)]="dateInscription" name="dateInscription" type="date" required class="champ" />
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
          <label class="mb-1 block text-sm font-medium text-slate-700">Adresse</label>
          <input [(ngModel)]="adresse" name="adresse" maxlength="255" class="champ" />
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Contact d'urgence — nom</label>
          <input [(ngModel)]="contactUrgenceNom" name="contactUrgenceNom" maxlength="150" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Contact d'urgence — téléphone</label>
          <input [(ngModel)]="contactUrgenceTelephone" name="contactUrgenceTelephone" maxlength="30" class="champ" />
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
              <th class="px-4 py-3">Matricule</th>
              <th class="px-4 py-3">Nom</th>
              <th class="px-4 py-3">Classe</th>
              <th class="px-4 py-3">Statut</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @for (item of items(); track item.id) {
              <tr>
                <td class="px-4 py-3 text-slate-600">{{ item.matricule }}</td>
                <td class="px-4 py-3 font-medium text-slate-900">{{ item.prenom }} {{ item.nom }}</td>
                <td class="px-4 py-3 text-slate-600">{{ nomClasse(item.classeId) }}</td>
                <td class="px-4 py-3 text-slate-600">{{ item.statut }}</td>
                <td class="px-4 py-3 text-right">
                  <button (click)="editer(item)" class="lien">Modifier</button>
                  <button (click)="supprimer(item)" class="lien text-red-600">Supprimer</button>
                </td>
              </tr>
            } @empty {
              <tr>
                <td colspan="5" class="px-4 py-6 text-center text-slate-400">Aucun élève.</td>
              </tr>
            }
          </tbody>
        </table>
      </div>
    </div>
  `,
})
export class ElevesPage {
  private readonly http = inject(HttpClient);
  private readonly api = crudApi<Eleve>(this.http, `${environment.apiUrl}/eleves`);
  private readonly classesApi = crudApi<Classe>(this.http, `${environment.apiUrl}/classes`);

  readonly items = signal<Eleve[]>([]);
  readonly classes = signal<Classe[]>([]);
  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);
  readonly enEdition = signal<string | null>(null);

  matricule = '';
  nom = '';
  prenom = '';
  sexe: 'M' | 'F' = 'M';
  dateNaissance = '';
  lieuNaissance = '';
  adresse = '';
  telephone = '';
  email = '';
  classeId = '';
  statut: Eleve['statut'] = 'actif';
  dateInscription = '';
  contactUrgenceNom = '';
  contactUrgenceTelephone = '';

  constructor() {
    void this.charger();
  }

  nomClasse(id: string | null): string {
    if (!id) return '—';
    return this.classes().find((c) => c.id === id)?.nom ?? '—';
  }

  async charger(): Promise<void> {
    const [items, classes] = await Promise.all([this.api.lister(), this.classesApi.lister()]);
    this.items.set(items);
    this.classes.set(classes);
  }

  editer(item: Eleve): void {
    this.enEdition.set(item.id);
    this.matricule = item.matricule;
    this.nom = item.nom;
    this.prenom = item.prenom;
    this.sexe = item.sexe;
    this.dateNaissance = versDateInput(item.dateNaissance);
    this.lieuNaissance = item.lieuNaissance ?? '';
    this.adresse = item.adresse ?? '';
    this.telephone = item.telephone ?? '';
    this.email = item.email ?? '';
    this.classeId = item.classeId ?? '';
    this.statut = item.statut;
    this.dateInscription = versDateInput(item.dateInscription);
    this.contactUrgenceNom = item.contactUrgenceNom ?? '';
    this.contactUrgenceTelephone = item.contactUrgenceTelephone ?? '';
  }

  annuler(): void {
    this.enEdition.set(null);
    this.matricule = '';
    this.nom = '';
    this.prenom = '';
    this.sexe = 'M';
    this.dateNaissance = '';
    this.lieuNaissance = '';
    this.adresse = '';
    this.telephone = '';
    this.email = '';
    this.classeId = '';
    this.statut = 'actif';
    this.dateInscription = '';
    this.contactUrgenceNom = '';
    this.contactUrgenceTelephone = '';
  }

  async valider(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    const payload = {
      matricule: this.matricule,
      nom: this.nom,
      prenom: this.prenom,
      sexe: this.sexe,
      dateNaissance: this.dateNaissance,
      lieuNaissance: this.lieuNaissance || undefined,
      adresse: this.adresse || undefined,
      telephone: this.telephone || undefined,
      email: this.email || undefined,
      classeId: this.classeId || undefined,
      statut: this.statut,
      dateInscription: this.dateInscription,
      contactUrgenceNom: this.contactUrgenceNom || undefined,
      contactUrgenceTelephone: this.contactUrgenceTelephone || undefined,
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

  async supprimer(item: Eleve): Promise<void> {
    if (!confirm(`Supprimer ${item.prenom} ${item.nom} ?`)) return;
    await this.api.supprimer(item.id);
    await this.charger();
  }

  async telechargerRapport(): Promise<void> {
    await telechargerPdf(this.http, `${environment.apiUrl}/eleves/rapport`, 'rapport-eleves.pdf');
  }
}
