import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import { versDateAffichee, versDateInput } from '../../core/dates';

interface EcoleResume {
  id: string;
  nom: string;
  ville: string | null;
  statut: string;
  plan: string;
  createdAt: unknown;
}
interface FactureUrgente {
  id: string;
  ecoleId: string;
  ecoleNom: string;
  montant: number;
  dateEcheance: unknown;
}
interface Dashboard {
  stats: {
    ecolesTotal: number;
    ecolesActives: number;
    ecolesEssai: number;
    ecolesSuspendues: number;
    nouvellesCeMois: number;
  };
  abonnementsActifs: number;
  mrrEstime: number;
  revenuCeMois: number;
  facturesEnAttente: number;
  facturesEnRetard: number;
  montantEnAttente: number;
  dernieresEcoles: EcoleResume[];
  facturesUrgentes: FactureUrgente[];
}

@Component({
  selector: 'app-super-admin-dashboard-page',
  standalone: true,
  imports: [RouterLink],
  template: `
    <div class="p-8">
      <h1 class="mb-6 text-xl font-semibold text-slate-900">Tableau de bord SaaS</h1>

      @if (donnees(); as d) {
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-5">
          <div class="carte">
            <p class="libelle">Écoles</p>
            <p class="valeur">{{ d.stats.ecolesTotal }}</p>
            <p class="mt-1 text-xs text-slate-500">
              {{ d.stats.ecolesActives }} actives · {{ d.stats.ecolesEssai }} en essai ·
              {{ d.stats.ecolesSuspendues }} suspendues
            </p>
          </div>
          <div class="carte">
            <p class="libelle">Nouvelles ce mois</p>
            <p class="valeur">{{ d.stats.nouvellesCeMois }}</p>
          </div>
          <div class="carte">
            <p class="libelle">MRR estimé</p>
            <p class="valeur text-emerald-700">{{ formatMontant(d.mrrEstime) }} FCFA</p>
            <p class="mt-1 text-xs text-slate-500">{{ d.abonnementsActifs }} abonnement(s) actif(s)</p>
          </div>
          <div class="carte">
            <p class="libelle">Revenu ce mois</p>
            <p class="valeur text-emerald-700">{{ formatMontant(d.revenuCeMois) }} FCFA</p>
          </div>
          <div class="carte">
            <p class="libelle">Factures en attente</p>
            <p class="valeur">{{ d.facturesEnAttente }}</p>
            <p class="mt-1 text-xs text-red-600">{{ d.facturesEnRetard }} en retard</p>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-3 flex items-center justify-between">
              <h2 class="text-sm font-semibold text-slate-900">Dernières écoles inscrites</h2>
              <a routerLink="/super-admin/ecoles" class="lien">Voir tout</a>
            </div>
            <ul class="divide-y divide-slate-100 text-sm">
              @for (ecole of d.dernieresEcoles; track ecole.id) {
                <li class="flex items-center justify-between py-2">
                  <div>
                    <a [routerLink]="['/super-admin/ecoles', ecole.id]" class="font-medium text-slate-900 hover:underline">{{
                      ecole.nom
                    }}</a>
                    <p class="text-xs text-slate-500">{{ ecole.ville ?? '—' }} · {{ dateAffichee(ecole.createdAt) }}</p>
                  </div>
                  <span [class]="badgeStatut(ecole.statut)">{{ ecole.statut }}</span>
                </li>
              } @empty {
                <li class="py-4 text-center text-slate-400">Aucune école.</li>
              }
            </ul>
          </div>

          <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="mb-3 flex items-center justify-between">
              <h2 class="text-sm font-semibold text-slate-900">Factures les plus urgentes</h2>
              <a routerLink="/super-admin/factures" class="lien">Voir tout</a>
            </div>
            <ul class="divide-y divide-slate-100 text-sm">
              @for (facture of d.facturesUrgentes; track facture.id) {
                <li class="flex items-center justify-between py-2">
                  <div>
                    <a [routerLink]="['/super-admin/ecoles', facture.ecoleId]" class="font-medium text-slate-900 hover:underline">{{
                      facture.ecoleNom
                    }}</a>
                    <p class="text-xs text-slate-500">Échéance {{ dateAffichee(facture.dateEcheance) }}</p>
                  </div>
                  <span class="font-medium text-slate-700">{{ formatMontant(facture.montant) }} FCFA</span>
                </li>
              } @empty {
                <li class="py-4 text-center text-slate-400">Aucune facture en attente.</li>
              }
            </ul>
          </div>
        </div>
      }
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
export class SuperAdminDashboardPage {
  private readonly http = inject(HttpClient);
  readonly donnees = signal<Dashboard | null>(null);

  constructor() {
    void this.charger();
  }

  private async charger(): Promise<void> {
    const donnees = await firstValueFrom(
      this.http.get<Dashboard>(`${environment.apiUrl}/super-admin/dashboard`),
    );
    this.donnees.set(donnees);
  }

  formatMontant(valeur: number): string {
    return new Intl.NumberFormat('fr-FR').format(valeur);
  }

  dateAffichee(valeur: unknown): string {
    return versDateAffichee(valeur as Parameters<typeof versDateInput>[0]);
  }

  badgeStatut(statut: string): string {
    const base = 'rounded-full px-2 py-0.5 text-xs font-medium';
    switch (statut) {
      case 'actif':
        return `${base} bg-emerald-50 text-emerald-700`;
      case 'essai':
        return `${base} bg-amber-50 text-amber-700`;
      case 'suspendu':
        return `${base} bg-red-50 text-red-700`;
      default:
        return `${base} bg-slate-100 text-slate-600`;
    }
  }
}
