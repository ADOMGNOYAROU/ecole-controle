import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import { crudApi, messageErreur } from '../../core/crud';
import { versDateInput } from '../../core/dates';
import { telechargerPdf } from '../../core/telecharger-pdf';

interface Eleve {
  id: string;
  nom: string;
  prenom: string;
}
interface AnneeScolaire {
  id: string;
  libelle: string;
}
interface Paiement {
  id: string;
  eleveId: string;
  anneeScolaireId: string;
  type: string;
  montant: number;
  montantPaye: number;
  dateEcheance: string;
  datePaiement: string | null;
  commentaire: string | null;
  statut: 'en_attente' | 'partiel' | 'paye' | 'retard';
}
interface Stats {
  totalAttendu: number;
  totalCollecte: number;
  enRetard: number;
}

@Component({
  selector: 'app-paiements-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold text-slate-900">Paiements</h1>
        <div class="flex gap-2">
          <button (click)="declencherRappels()" [disabled]="enCoursRappels()" class="btn-ghost">
            {{ enCoursRappels() ? 'Envoi…' : 'Déclencher les relances' }}
          </button>
          <button (click)="telechargerRapport()" class="btn-ghost">Télécharger le rapport PDF</button>
        </div>
      </div>

      @if (messageRappels()) {
        <p class="mb-4 text-sm text-emerald-700">{{ messageRappels() }}</p>
      }

      @if (stats(); as s) {
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total attendu</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900">{{ formatMontant(s.totalAttendu) }} FCFA</p>
          </div>
          <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total collecté</p>
            <p class="mt-1 text-2xl font-semibold text-emerald-700">{{ formatMontant(s.totalCollecte) }} FCFA</p>
          </div>
          <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Paiements en retard</p>
            <p class="mt-1 text-2xl font-semibold text-red-700">{{ s.enRetard }}</p>
          </div>
        </div>
      }

      <form
        (ngSubmit)="valider()"
        class="mb-6 grid grid-cols-1 gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-4"
      >
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Élève</label>
          <select [(ngModel)]="eleveId" name="eleveId" required class="champ">
            <option value="" disabled>Choisir…</option>
            @for (eleve of eleves(); track eleve.id) {
              <option [value]="eleve.id">{{ eleve.prenom }} {{ eleve.nom }}</option>
            }
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Année scolaire</label>
          <select [(ngModel)]="anneeScolaireId" name="anneeScolaireId" required class="champ">
            <option value="" disabled>Choisir…</option>
            @for (annee of annees(); track annee.id) {
              <option [value]="annee.id">{{ annee.libelle }}</option>
            }
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Type</label>
          <select [(ngModel)]="type" name="type" class="champ">
            <option value="scolarite">Scolarité</option>
            <option value="inscription">Inscription</option>
            <option value="transport">Transport</option>
            <option value="cantine">Cantine</option>
            <option value="autre">Autre</option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Montant (FCFA)</label>
          <input [(ngModel)]="montant" name="montant" type="number" min="0" required class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Montant payé (FCFA)</label>
          <input [(ngModel)]="montantPaye" name="montantPaye" type="number" min="0" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Échéance</label>
          <input [(ngModel)]="dateEcheance" name="dateEcheance" type="date" required class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Date de paiement</label>
          <input [(ngModel)]="datePaiement" name="datePaiement" type="date" class="champ" />
        </div>
        <div>
          <label class="mb-1 block text-sm font-medium text-slate-700">Commentaire</label>
          <input [(ngModel)]="commentaire" name="commentaire" maxlength="255" class="champ" />
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
              <th class="px-4 py-3">Élève</th>
              <th class="px-4 py-3">Type</th>
              <th class="px-4 py-3">Montant</th>
              <th class="px-4 py-3">Payé</th>
              <th class="px-4 py-3">Échéance</th>
              <th class="px-4 py-3">Statut</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @for (item of items(); track item.id) {
              <tr>
                <td class="px-4 py-3 font-medium text-slate-900">{{ nomEleve(item.eleveId) }}</td>
                <td class="px-4 py-3 text-slate-600">{{ item.type }}</td>
                <td class="px-4 py-3 text-slate-600">{{ formatMontant(item.montant) }}</td>
                <td class="px-4 py-3 text-slate-600">{{ formatMontant(item.montantPaye) }}</td>
                <td class="px-4 py-3 text-slate-600">{{ item.dateEcheance }}</td>
                <td class="px-4 py-3">
                  <span [class]="badgeStatut(item.statut)">{{ item.statut }}</span>
                </td>
                <td class="px-4 py-3 text-right">
                  <button (click)="editer(item)" class="lien">Modifier</button>
                  <button (click)="supprimer(item)" class="lien text-red-600">Supprimer</button>
                </td>
              </tr>
            } @empty {
              <tr>
                <td colspan="7" class="px-4 py-6 text-center text-slate-400">Aucun paiement.</td>
              </tr>
            }
          </tbody>
        </table>
      </div>
    </div>
  `,
})
export class PaiementsPage {
  private readonly http = inject(HttpClient);
  private readonly api = crudApi<Paiement>(this.http, `${environment.apiUrl}/paiements`);
  private readonly elevesApi = crudApi<Eleve>(this.http, `${environment.apiUrl}/eleves`);
  private readonly anneesApi = crudApi<AnneeScolaire>(this.http, `${environment.apiUrl}/annees-scolaires`);

  readonly items = signal<Paiement[]>([]);
  readonly eleves = signal<Eleve[]>([]);
  readonly annees = signal<AnneeScolaire[]>([]);
  readonly stats = signal<Stats | null>(null);
  readonly enCours = signal(false);
  readonly enCoursRappels = signal(false);
  readonly messageRappels = signal<string | null>(null);
  readonly erreur = signal<string | null>(null);
  readonly enEdition = signal<string | null>(null);

  eleveId = '';
  anneeScolaireId = '';
  type = 'scolarite';
  montant: number | null = null;
  montantPaye: number | null = null;
  dateEcheance = '';
  datePaiement = '';
  commentaire = '';

  constructor() {
    void this.charger();
  }

  formatMontant(valeur: number): string {
    return new Intl.NumberFormat('fr-FR').format(valeur);
  }

  nomEleve(eleveId: string): string {
    const eleve = this.eleves().find((e) => e.id === eleveId);
    return eleve ? `${eleve.prenom} ${eleve.nom}` : '—';
  }

  badgeStatut(statut: Paiement['statut']): string {
    const base = 'rounded-full px-2 py-0.5 text-xs font-medium';
    switch (statut) {
      case 'paye':
        return `${base} bg-emerald-50 text-emerald-700`;
      case 'partiel':
        return `${base} bg-amber-50 text-amber-700`;
      case 'retard':
        return `${base} bg-red-50 text-red-700`;
      default:
        return `${base} bg-slate-100 text-slate-600`;
    }
  }

  private async charger(): Promise<void> {
    const [items, eleves, annees, stats] = await Promise.all([
      this.api.lister(),
      this.elevesApi.lister(),
      this.anneesApi.lister(),
      firstValueFrom(this.http.get<Stats>(`${environment.apiUrl}/paiements/stats`)),
    ]);
    this.items.set(items);
    this.eleves.set(eleves);
    this.annees.set(annees);
    this.stats.set(stats);
  }

  editer(item: Paiement): void {
    this.enEdition.set(item.id);
    this.eleveId = item.eleveId;
    this.anneeScolaireId = item.anneeScolaireId;
    this.type = item.type;
    this.montant = item.montant;
    this.montantPaye = item.montantPaye;
    this.dateEcheance = versDateInput(item.dateEcheance);
    this.datePaiement = item.datePaiement ? versDateInput(item.datePaiement) : '';
    this.commentaire = item.commentaire ?? '';
  }

  annuler(): void {
    this.enEdition.set(null);
    this.eleveId = '';
    this.anneeScolaireId = '';
    this.type = 'scolarite';
    this.montant = null;
    this.montantPaye = null;
    this.dateEcheance = '';
    this.datePaiement = '';
    this.commentaire = '';
  }

  async valider(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    const payload = {
      eleveId: this.eleveId,
      anneeScolaireId: this.anneeScolaireId,
      type: this.type,
      montant: Number(this.montant),
      montantPaye: this.montantPaye !== null ? Number(this.montantPaye) : undefined,
      dateEcheance: this.dateEcheance,
      datePaiement: this.datePaiement || undefined,
      commentaire: this.commentaire || undefined,
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

  async supprimer(item: Paiement): Promise<void> {
    if (!confirm(`Supprimer ce paiement de ${this.nomEleve(item.eleveId)} ?`)) return;
    await this.api.supprimer(item.id);
    await this.charger();
  }

  async declencherRappels(): Promise<void> {
    this.enCoursRappels.set(true);
    this.messageRappels.set(null);
    try {
      const resultat = await firstValueFrom(
        this.http.post<{ envoyes: number }>(`${environment.apiUrl}/paiements/rappels`, {}),
      );
      this.messageRappels.set(`${resultat.envoyes} relance(s) envoyée(s).`);
      await this.charger();
    } finally {
      this.enCoursRappels.set(false);
    }
  }

  async telechargerRapport(): Promise<void> {
    await telechargerPdf(this.http, `${environment.apiUrl}/paiements/rapport`, 'rapport-paiements.pdf');
  }
}
