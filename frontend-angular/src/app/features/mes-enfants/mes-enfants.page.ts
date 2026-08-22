import { HttpClient } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';

interface Enfant {
  id: string;
  nom: string;
  prenom: string;
  matricule: string;
  classeNom: string | null;
}
interface MatiereBulletin {
  nom: string;
  moyenne: number | null;
  coefficient: number;
}
interface Presence {
  id: string;
  date: string;
  statut: 'present' | 'absent' | 'retard';
}
interface Paiement {
  id: string;
  type: string;
  montant: number;
  montantPaye: number;
  dateEcheance: string;
  statut: string;
}
interface EnfantDetail {
  eleve: Enfant;
  donnees: { matieres: MatiereBulletin[]; moyenneGenerale: number | null } | null;
  tauxPresence: number | null;
  presences: Presence[];
  paiements: Paiement[];
}

@Component({
  selector: 'app-mes-enfants-page',
  standalone: true,
  template: `
    <div class="p-8">
      <h1 class="mb-6 text-xl font-semibold text-slate-900">Mes enfants</h1>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-2">
          @for (enfant of enfants(); track enfant.id) {
            <button
              (click)="selectionner(enfant)"
              class="block w-full rounded-xl bg-white p-4 text-left shadow-sm ring-1 hover:ring-brand-300"
              [class.ring-brand-400]="selection()?.id === enfant.id"
              [class.ring-slate-200]="selection()?.id !== enfant.id"
            >
              <p class="text-sm font-semibold text-slate-900">{{ enfant.prenom }} {{ enfant.nom }}</p>
              <p class="mt-0.5 text-xs text-slate-500">{{ enfant.matricule }} · {{ enfant.classeNom ?? 'Sans classe' }}</p>
            </button>
          } @empty {
            <p class="text-sm text-slate-400">Aucun enfant rattaché à votre compte.</p>
          }
        </div>

        <div class="lg:col-span-2">
          @if (detail(); as d) {
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                  <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Moyenne générale</p>
                  <p class="mt-1 text-2xl font-semibold text-slate-900">
                    {{ d.donnees?.moyenneGenerale !== null && d.donnees?.moyenneGenerale !== undefined ? d.donnees?.moyenneGenerale + '/20' : 'N/A' }}
                  </p>
                </div>
                <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
                  <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Taux de présence</p>
                  <p class="mt-1 text-2xl font-semibold text-slate-900">
                    {{ d.tauxPresence !== null ? d.tauxPresence + '%' : 'N/A' }}
                  </p>
                </div>
              </div>

              <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <h2 class="border-b border-slate-200 p-4 text-sm font-semibold text-slate-900">Notes du trimestre</h2>
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                  <tbody class="divide-y divide-slate-100">
                    @for (m of d.donnees?.matieres ?? []; track m.nom) {
                      <tr>
                        <td class="px-4 py-2 text-slate-900">{{ m.nom }}</td>
                        <td class="px-4 py-2 text-right text-slate-600">{{ m.moyenne ?? '—' }}/20</td>
                      </tr>
                    } @empty {
                      <tr>
                        <td class="px-4 py-4 text-center text-slate-400">Aucune note.</td>
                      </tr>
                    }
                  </tbody>
                </table>
              </div>

              <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <h2 class="border-b border-slate-200 p-4 text-sm font-semibold text-slate-900">
                  Présences récentes
                </h2>
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                  <tbody class="divide-y divide-slate-100">
                    @for (p of d.presences; track p.id) {
                      <tr>
                        <td class="px-4 py-2 text-slate-600">{{ p.date }}</td>
                        <td class="px-4 py-2 text-right text-slate-900">{{ p.statut }}</td>
                      </tr>
                    } @empty {
                      <tr>
                        <td class="px-4 py-4 text-center text-slate-400">Aucune présence enregistrée.</td>
                      </tr>
                    }
                  </tbody>
                </table>
              </div>

              <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
                <h2 class="border-b border-slate-200 p-4 text-sm font-semibold text-slate-900">Paiements</h2>
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                  <tbody class="divide-y divide-slate-100">
                    @for (p of d.paiements; track p.id) {
                      <tr>
                        <td class="px-4 py-2 text-slate-900">{{ p.type }}</td>
                        <td class="px-4 py-2 text-slate-600">{{ p.montantPaye }} / {{ p.montant }}</td>
                        <td class="px-4 py-2 text-slate-600">{{ p.dateEcheance }}</td>
                        <td class="px-4 py-2 text-right text-slate-600">{{ p.statut }}</td>
                      </tr>
                    } @empty {
                      <tr>
                        <td class="px-4 py-4 text-center text-slate-400">Aucun paiement enregistré.</td>
                      </tr>
                    }
                  </tbody>
                </table>
              </div>
            </div>
          } @else {
            <p class="text-sm text-slate-400">Sélectionnez un enfant pour voir son suivi.</p>
          }
        </div>
      </div>
    </div>
  `,
})
export class MesEnfantsPage {
  private readonly http = inject(HttpClient);

  readonly enfants = signal<Enfant[]>([]);
  readonly selection = signal<Enfant | null>(null);
  readonly detail = signal<EnfantDetail | null>(null);

  constructor() {
    void this.charger();
  }

  async charger(): Promise<void> {
    this.enfants.set(await firstValueFrom(this.http.get<Enfant[]>(`${environment.apiUrl}/mes-enfants`)));
  }

  async selectionner(enfant: Enfant): Promise<void> {
    this.selection.set(enfant);
    this.detail.set(await firstValueFrom(this.http.get<EnfantDetail>(`${environment.apiUrl}/mes-enfants/${enfant.id}`)));
  }
}
