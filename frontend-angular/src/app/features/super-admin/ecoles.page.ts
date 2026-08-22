import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import { versDateAffichee } from '../../core/dates';

interface Ecole {
  id: string;
  nom: string;
  ville: string | null;
  statut: string;
  plan: string;
  createdAt: unknown;
  utilisateursCount: number;
}

@Component({
  selector: 'app-super-admin-ecoles-page',
  standalone: true,
  imports: [FormsModule, RouterLink],
  template: `
    <div class="p-8">
      <h1 class="mb-6 text-xl font-semibold text-slate-900">Écoles</h1>

      <div class="mb-4 flex flex-wrap gap-3">
        <input
          [(ngModel)]="recherche"
          (ngModelChange)="charger()"
          placeholder="Rechercher (nom, ville)…"
          class="champ w-64"
        />
        <select [(ngModel)]="statut" (ngModelChange)="charger()" class="champ w-40">
          <option value="">Tous statuts</option>
          <option value="essai">Essai</option>
          <option value="actif">Actif</option>
          <option value="suspendu">Suspendu</option>
        </select>
        <select [(ngModel)]="plan" (ngModelChange)="charger()" class="champ w-40">
          <option value="">Tous plans</option>
          <option value="gratuit">Gratuit</option>
          <option value="premium">Premium</option>
        </select>
      </div>

      <div class="overflow-x-auto rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
          <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
              <th class="px-4 py-3">École</th>
              <th class="px-4 py-3">Ville</th>
              <th class="px-4 py-3">Statut</th>
              <th class="px-4 py-3">Plan</th>
              <th class="px-4 py-3">Utilisateurs</th>
              <th class="px-4 py-3">Inscrite le</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @for (ecole of ecoles(); track ecole.id) {
              <tr>
                <td class="px-4 py-3 font-medium text-slate-900">{{ ecole.nom }}</td>
                <td class="px-4 py-3 text-slate-600">{{ ecole.ville ?? '—' }}</td>
                <td class="px-4 py-3"><span [class]="badgeStatut(ecole.statut)">{{ ecole.statut }}</span></td>
                <td class="px-4 py-3 text-slate-600">{{ ecole.plan }}</td>
                <td class="px-4 py-3 text-slate-600">{{ ecole.utilisateursCount }}</td>
                <td class="px-4 py-3 text-slate-600">{{ dateAffichee(ecole.createdAt) }}</td>
                <td class="px-4 py-3 text-right">
                  <a [routerLink]="['/super-admin/ecoles', ecole.id]" class="lien">Détail</a>
                </td>
              </tr>
            } @empty {
              <tr>
                <td colspan="7" class="px-4 py-6 text-center text-slate-400">Aucune école.</td>
              </tr>
            }
          </tbody>
        </table>
      </div>
    </div>
  `,
})
export class SuperAdminEcolesPage {
  private readonly http = inject(HttpClient);
  readonly ecoles = signal<Ecole[]>([]);

  recherche = '';
  statut = '';
  plan = '';

  constructor() {
    void this.charger();
  }

  async charger(): Promise<void> {
    const params: Record<string, string> = {};
    if (this.recherche) params['recherche'] = this.recherche;
    if (this.statut) params['statut'] = this.statut;
    if (this.plan) params['plan'] = this.plan;

    const ecoles = await firstValueFrom(
      this.http.get<Ecole[]>(`${environment.apiUrl}/super-admin/ecoles`, { params }),
    );
    this.ecoles.set(ecoles);
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
}
