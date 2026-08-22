import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import { messageErreur } from '../../core/crud';
import { versDateAffichee } from '../../core/dates';

interface Ecole {
  nom: string;
  statut: string;
  plan: string;
  trialEndsAt: unknown;
}
interface Facture {
  id: string;
  montant: number;
  dateEcheance: unknown;
  statut: string;
  payeeLe: unknown;
}
interface Abonnement {
  ecole: Ecole;
  factures: Facture[];
  tarif: number;
}

@Component({
  selector: 'app-abonnement-page',
  standalone: true,
  imports: [],
  template: `
    <div class="mx-auto max-w-3xl p-8">
      <h1 class="mb-6 text-xl font-semibold text-slate-900">Abonnement</h1>

      @if (donnees(); as d) {
        @if (d.ecole.statut === 'suspendu') {
          <p class="mb-6 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
            L'accès de votre école est suspendu (abonnement impayé). Réglez une facture en attente puis attendez sa
            confirmation pour être réactivé.
          </p>
        }

        <div class="mb-6 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
          <p class="text-sm text-slate-500">Plan actuel</p>
          <p class="mt-1 text-lg font-semibold text-slate-900">{{ d.ecole.plan === 'premium' ? 'Premium' : 'Gratuit' }}</p>
          @if (d.ecole.trialEndsAt) {
            <p class="mt-1 text-sm text-amber-700">Essai Premium jusqu'au {{ dateAffichee(d.ecole.trialEndsAt) }}</p>
          }

          <hr class="my-4 border-slate-200" />

          <p class="text-sm text-slate-600">
            Offre Premium : <span class="font-semibold text-slate-900">{{ formatMontant(d.tarif) }} FCFA</span> / trimestre
          </p>
          <p class="mt-1 text-xs text-slate-500">
            Après souscription, réglez par Mobile Money (Flooz / T-Money) et communiquez la référence à l'administration
            pour activation.
          </p>

          @if (erreur()) {
            <p class="mt-3 text-sm text-red-600">{{ erreur() }}</p>
          }
          @if (message()) {
            <p class="mt-3 text-sm text-emerald-700">{{ message() }}</p>
          }

          <button (click)="souscrire()" [disabled]="enCours()" class="btn-primary mt-4">
            {{ enCours() ? 'Création…' : 'Souscrire / renouveler' }}
          </button>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
          <h2 class="mb-3 text-sm font-semibold text-slate-900">Historique des factures</h2>
          <ul class="divide-y divide-slate-100 text-sm">
            @for (f of d.factures; track f.id) {
              <li class="flex items-center justify-between py-2">
                <div>
                  <p>Échéance {{ dateAffichee(f.dateEcheance) }}</p>
                  <span [class]="badgeStatut(f.statut)">{{ f.statut }}</span>
                </div>
                <span class="font-medium text-slate-700">{{ formatMontant(f.montant) }} FCFA</span>
              </li>
            } @empty {
              <li class="py-4 text-center text-slate-400">Aucune facture pour l'instant.</li>
            }
          </ul>
        </div>
      }
    </div>
  `,
})
export class AbonnementPage {
  private readonly http = inject(HttpClient);
  readonly donnees = signal<Abonnement | null>(null);
  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);
  readonly message = signal<string | null>(null);

  constructor() {
    void this.charger();
  }

  private async charger(): Promise<void> {
    const donnees = await firstValueFrom(
      this.http.get<Abonnement>(`${environment.apiUrl}/abonnement`),
    );
    this.donnees.set(donnees);
  }

  async souscrire(): Promise<void> {
    this.erreur.set(null);
    this.message.set(null);
    this.enCours.set(true);
    try {
      await firstValueFrom(this.http.post(`${environment.apiUrl}/abonnement/souscrire`, {}));
      this.message.set(
        "Facture créée. Réglez-la par Mobile Money puis communiquez la référence à l'administration pour activation.",
      );
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
