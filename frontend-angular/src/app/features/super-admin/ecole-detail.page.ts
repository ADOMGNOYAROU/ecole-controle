import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import { versDateAffichee } from '../../core/dates';

interface EcoleDetail {
  id: string;
  nom: string;
  ville: string | null;
  telephone: string | null;
  emailContact: string | null;
  statut: string;
  plan: string;
  trialEndsAt: unknown;
  createdAt: unknown;
  stats: { eleves: number; enseignants: number; classes: number; utilisateurs: number };
  abonnements: { id: string; dateDebut: unknown; dateFin: unknown; statut: string; montant: number }[];
  factures: { id: string; dateEcheance: unknown; montant: number; statut: string; payeeLe: unknown }[];
  abonnementActif: { dateFin: unknown } | null;
}

@Component({
  selector: 'app-super-admin-ecole-detail-page',
  standalone: true,
  imports: [RouterLink],
  template: `
    <div class="p-8">
      <a routerLink="/super-admin/ecoles" class="lien mb-4 inline-block">← Retour aux écoles</a>

      @if (ecole(); as e) {
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
          <div>
            <h1 class="text-xl font-semibold text-slate-900">{{ e.nom }}</h1>
            <p class="mt-1 text-sm text-slate-500">
              {{ e.ville ?? '—' }} · {{ e.emailContact ?? '—' }} · {{ e.telephone ?? '—' }}
            </p>
            <div class="mt-2 flex gap-2">
              <span [class]="badgeStatut(e.statut)">{{ e.statut }}</span>
              <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ e.plan }}</span>
            </div>
          </div>
          <div class="flex gap-2">
            @if (e.statut !== 'suspendu') {
              <button (click)="suspendre()" [disabled]="enCours()" class="btn-ghost text-red-600">Suspendre</button>
            } @else {
              <button (click)="activer()" [disabled]="enCours()" class="btn-primary">Réactiver</button>
            }
          </div>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
          <div class="carte"><p class="libelle">Élèves</p><p class="valeur">{{ e.stats.eleves }}</p></div>
          <div class="carte"><p class="libelle">Enseignants</p><p class="valeur">{{ e.stats.enseignants }}</p></div>
          <div class="carte"><p class="libelle">Classes</p><p class="valeur">{{ e.stats.classes }}</p></div>
          <div class="carte"><p class="libelle">Utilisateurs</p><p class="valeur">{{ e.stats.utilisateurs }}</p></div>
        </div>

        @if (e.abonnementActif) {
          <p class="mb-6 rounded-lg bg-emerald-50 px-4 py-2 text-sm text-emerald-700">
            Abonnement Premium actif jusqu'au {{ dateAffichee(e.abonnementActif.dateFin) }}.
          </p>
        } @else if (e.trialEndsAt) {
          <p class="mb-6 rounded-lg bg-amber-50 px-4 py-2 text-sm text-amber-700">
            Essai Premium jusqu'au {{ dateAffichee(e.trialEndsAt) }}.
          </p>
        }

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
          <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-3 text-sm font-semibold text-slate-900">Historique des abonnements</h2>
            <ul class="divide-y divide-slate-100 text-sm">
              @for (a of e.abonnements; track a.id) {
                <li class="flex items-center justify-between py-2">
                  <span>{{ dateAffichee(a.dateDebut) }} → {{ dateAffichee(a.dateFin) }}</span>
                  <span class="font-medium text-slate-700">{{ formatMontant(a.montant) }} FCFA</span>
                </li>
              } @empty {
                <li class="py-4 text-center text-slate-400">Aucun abonnement.</li>
              }
            </ul>
          </div>

          <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="mb-3 text-sm font-semibold text-slate-900">Factures</h2>
            <ul class="divide-y divide-slate-100 text-sm">
              @for (f of e.factures; track f.id) {
                <li class="flex items-center justify-between py-2">
                  <div>
                    <p>Échéance {{ dateAffichee(f.dateEcheance) }}</p>
                    <span [class]="badgeStatutFacture(f.statut)">{{ f.statut }}</span>
                  </div>
                  <span class="font-medium text-slate-700">{{ formatMontant(f.montant) }} FCFA</span>
                </li>
              } @empty {
                <li class="py-4 text-center text-slate-400">Aucune facture.</li>
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
export class SuperAdminEcoleDetailPage {
  private readonly http = inject(HttpClient);
  private readonly route = inject(ActivatedRoute);
  readonly ecole = signal<EcoleDetail | null>(null);
  readonly enCours = signal(false);

  constructor() {
    void this.charger();
  }

  private get ecoleId(): string {
    return this.route.snapshot.paramMap.get('id')!;
  }

  private async charger(): Promise<void> {
    const ecole = await firstValueFrom(
      this.http.get<EcoleDetail>(`${environment.apiUrl}/super-admin/ecoles/${this.ecoleId}`),
    );
    this.ecole.set(ecole);
  }

  async suspendre(): Promise<void> {
    if (!confirm(`Suspendre l'accès de ${this.ecole()?.nom} ?`)) return;
    this.enCours.set(true);
    try {
      await firstValueFrom(
        this.http.patch(`${environment.apiUrl}/super-admin/ecoles/${this.ecoleId}/suspendre`, {}),
      );
      await this.charger();
    } finally {
      this.enCours.set(false);
    }
  }

  async activer(): Promise<void> {
    this.enCours.set(true);
    try {
      await firstValueFrom(
        this.http.patch(`${environment.apiUrl}/super-admin/ecoles/${this.ecoleId}/activer`, {}),
      );
      await this.charger();
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

  badgeStatutFacture(statut: string): string {
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
