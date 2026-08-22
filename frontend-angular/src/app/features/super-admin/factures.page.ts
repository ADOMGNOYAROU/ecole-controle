import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import { messageErreur } from '../../core/crud';
import { versDateAffichee } from '../../core/dates';

interface Facture {
  id: string;
  ecoleId: string;
  ecoleNom: string;
  montant: number;
  dateEcheance: unknown;
  statut: string;
  methodePaiement: string | null;
  referenceTransaction: string | null;
  payeeLe: unknown;
}
interface EcoleOption {
  id: string;
  nom: string;
}
interface Stats {
  enAttente: number;
  enRetard: number;
  payeesCeMois: number;
  montantEnAttente: number;
}

@Component({
  selector: 'app-super-admin-factures-page',
  standalone: true,
  imports: [FormsModule, RouterLink],
  template: `
    <div class="p-8">
      <h1 class="mb-6 text-xl font-semibold text-slate-900">Factures</h1>

      @if (stats(); as s) {
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-4">
          <div class="carte"><p class="libelle">En attente</p><p class="valeur">{{ s.enAttente }}</p></div>
          <div class="carte"><p class="libelle">En retard</p><p class="valeur text-red-700">{{ s.enRetard }}</p></div>
          <div class="carte">
            <p class="libelle">Payées ce mois</p>
            <p class="valeur text-emerald-700">{{ formatMontant(s.payeesCeMois) }} FCFA</p>
          </div>
          <div class="carte">
            <p class="libelle">Montant en attente</p>
            <p class="valeur">{{ formatMontant(s.montantEnAttente) }} FCFA</p>
          </div>
        </div>
      }

      <div class="mb-4 flex flex-wrap gap-3">
        <select [(ngModel)]="statut" (ngModelChange)="charger()" class="champ w-48">
          <option value="">Tous statuts</option>
          <option value="en_attente">En attente</option>
          <option value="payee">Payée</option>
          <option value="annulee">Annulée</option>
        </select>
        <select [(ngModel)]="ecoleId" (ngModelChange)="charger()" class="champ w-64">
          <option value="">Toutes les écoles</option>
          @for (ecole of ecoles(); track ecole.id) {
            <option [value]="ecole.id">{{ ecole.nom }}</option>
          }
        </select>
      </div>

      @if (erreur()) {
        <p class="mb-4 text-sm text-red-600">{{ erreur() }}</p>
      }

      <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
          <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
              <th class="px-4 py-3">École</th>
              <th class="px-4 py-3">Montant</th>
              <th class="px-4 py-3">Échéance</th>
              <th class="px-4 py-3">Statut</th>
              <th class="px-4 py-3">Paiement</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @for (f of factures(); track f.id) {
              <tr>
                <td class="px-4 py-3 font-medium text-slate-900">
                  <a [routerLink]="['/super-admin/ecoles', f.ecoleId]" class="hover:underline">{{ f.ecoleNom }}</a>
                </td>
                <td class="px-4 py-3 text-slate-600">{{ formatMontant(f.montant) }} FCFA</td>
                <td class="px-4 py-3 text-slate-600">{{ dateAffichee(f.dateEcheance) }}</td>
                <td class="px-4 py-3"><span [class]="badgeStatut(f.statut)">{{ f.statut }}</span></td>
                <td class="px-4 py-3 text-slate-600">{{ f.methodePaiement ?? '—' }}</td>
                <td class="px-4 py-3 text-right">
                  @if (f.statut === 'en_attente') {
                    @if (enConfirmation() === f.id) {
                      <div class="flex items-center justify-end gap-1">
                        <select [(ngModel)]="methodePaiement" name="methode-{{ f.id }}" class="champ w-28 text-xs">
                          <option value="flooz">Flooz</option>
                          <option value="tmoney">T-Money</option>
                          <option value="virement">Virement</option>
                          <option value="especes">Espèces</option>
                          <option value="autre">Autre</option>
                        </select>
                        <input
                          [(ngModel)]="referenceTransaction"
                          name="reference-{{ f.id }}"
                          placeholder="Référence"
                          class="champ w-24 text-xs"
                        />
                        <button (click)="confirmer(f)" [disabled]="enCours()" class="lien">Valider</button>
                        <button (click)="enConfirmation.set(null)" class="lien text-slate-500">Annuler</button>
                      </div>
                    } @else {
                      <button (click)="ouvrirConfirmation(f)" class="lien">Confirmer paiement</button>
                    }
                  }
                </td>
              </tr>
            } @empty {
              <tr>
                <td colspan="6" class="px-4 py-6 text-center text-slate-400">Aucune facture.</td>
              </tr>
            }
          </tbody>
        </table>
      </div>
    </div>
  `,
  styles: [
    `
      .carte {
        border-radius: 0.75rem;
        background: white;
        padding: 1rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        border: 1px solid rgb(226 232 240);
      }
      .libelle {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: rgb(100 116 139);
      }
      .valeur {
        margin-top: 0.25rem;
        font-size: 1.5rem;
        font-weight: 600;
        color: rgb(15 23 42);
      }
    `,
  ],
})
export class SuperAdminFacturesPage {
  private readonly http = inject(HttpClient);
  readonly factures = signal<Facture[]>([]);
  readonly ecoles = signal<EcoleOption[]>([]);
  readonly stats = signal<Stats | null>(null);
  readonly enCours = signal(false);
  readonly enConfirmation = signal<string | null>(null);
  readonly erreur = signal<string | null>(null);

  statut = '';
  ecoleId = '';
  methodePaiement = 'flooz';
  referenceTransaction = '';

  constructor() {
    void this.charger();
  }

  async charger(): Promise<void> {
    const params: Record<string, string> = {};
    if (this.statut) params['statut'] = this.statut;
    if (this.ecoleId) params['ecoleId'] = this.ecoleId;

    const resultat = await firstValueFrom(
      this.http.get<{ factures: Facture[]; stats: Stats; ecoles: EcoleOption[] }>(
        `${environment.apiUrl}/super-admin/factures`,
        { params },
      ),
    );
    this.factures.set(resultat.factures);
    this.stats.set(resultat.stats);
    this.ecoles.set(resultat.ecoles);
  }

  ouvrirConfirmation(facture: Facture): void {
    this.enConfirmation.set(facture.id);
    this.methodePaiement = 'flooz';
    this.referenceTransaction = '';
    this.erreur.set(null);
  }

  async confirmer(facture: Facture): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    try {
      await firstValueFrom(
        this.http.post(`${environment.apiUrl}/super-admin/factures/${facture.id}/confirmer`, {
          methodePaiement: this.methodePaiement,
          referenceTransaction: this.referenceTransaction || undefined,
        }),
      );
      this.enConfirmation.set(null);
      await this.charger();
    } catch (error) {
      this.erreur.set(messageErreur(error));
    } finally {
      this.enCours.set(false);
    }
  }

  formatMontant(valeur: number): string {
    return new Intl.NumberFormat('fr-FR').format(valeur);
  }

  dateAffichee(valeur: unknown): string {
    return versDateAffichee(valeur as Parameters<typeof versDateAffichee>[0]);
  }

  badgeStatut(statut: string): string {
    const base = 'rounded-full px-2 py-0.5 text-xs font-medium';
    switch (statut) {
      case 'payee':
        return `${base} bg-emerald-50 text-emerald-700`;
      case 'en_attente':
        return `${base} bg-amber-50 text-amber-700`;
      default:
        return `${base} bg-slate-100 text-slate-600`;
    }
  }
}
