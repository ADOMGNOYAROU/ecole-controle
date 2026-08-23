import { HttpClient } from '@angular/common/http';
import { Component, computed, inject, signal } from '@angular/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { Router, RouterLink } from '@angular/router';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import { AuthService } from '../../core/auth.service';
import { versDateAffichee } from '../../core/dates';

interface NoteApercu {
  eleveNom?: string;
  matiereNom: string;
  noteSur20: number;
  dateEvaluation: string;
}
interface AnnonceApercu {
  id: string;
  titre: string;
  datePublication: unknown;
  auteurNom: string | null;
}
interface Echeance {
  date: string;
  titre: string;
}
interface EleveARisque {
  id: string;
  nom: string;
  prenom: string;
  classeNom: string | null;
}
interface TableauAdmin {
  ecoleNom: string | null;
  trimestre: string | null;
  stats: { eleves: number; enseignants: number; classes: number; paiementsEnRetard: number };
  deltas: { eleves: number; enseignants: number; classes: number; paiementsEnRetard: number };
  tauxPresenceGlobal: number | null;
  dernieresNotes: NoteApercu[];
  annonces: AnnonceApercu[];
  prochainesEcheances: Echeance[];
  elevesARisque: EleveARisque[];
}
interface ClasseApercu {
  id: string;
  nom: string;
  effectif: number;
}
interface TableauEnseignant {
  classes: ClasseApercu[];
  dernieresNotes: NoteApercu[];
  annonces: AnnonceApercu[];
}
interface TableauEleve {
  trimestre: string | null;
  moyenne: number | null;
  tauxPresence: number | null;
  solde: number;
  dernieresNotes: NoteApercu[];
  annonces: AnnonceApercu[];
}
interface EnfantApercu {
  id: string;
  nom: string;
  prenom: string;
  classeNom: string | null;
  moyenneCourante: number | null;
  tauxPresenceCourant: number | null;
  soldeDu: number;
}
interface TableauParent {
  enfants: EnfantApercu[];
  annonces: AnnonceApercu[];
}

@Component({
  selector: 'app-dashboard-page',
  standalone: true,
  imports: [RouterLink],
  template: `
    <div class="p-8">
      @if (estSuperAdmin()) {
        <p class="text-slate-500">Redirection…</p>
      } @else if (chargement()) {
        <p class="text-slate-400">Chargement…</p>
      } @else {
        @switch (role()) {
          @case ('admin') {
            @if (admin(); as d) {
              <h1 class="mb-1 text-xl font-semibold text-slate-900">{{ d.ecoleNom ?? 'Tableau de bord' }}</h1>
              <p class="mb-6 text-sm text-slate-500">
                Bonjour {{ email() }} @if (d.trimestre) {
                  — {{ d.trimestre }}
                }
              </p>

              <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="carte">
                  <p class="libelle">Élèves actifs</p>
                  <p class="valeur">{{ d.stats.eleves }}</p>
                  <p class="tendance">{{ d.deltas.eleves > 0 ? '+' + d.deltas.eleves + ' ce mois' : 'Stable' }}</p>
                </div>
                <div class="carte">
                  <p class="libelle">Enseignants</p>
                  <p class="valeur">{{ d.stats.enseignants }}</p>
                  <p class="tendance">
                    {{ d.deltas.enseignants > 0 ? '+' + d.deltas.enseignants + ' ce mois' : 'Stable' }}
                  </p>
                </div>
                <div class="carte">
                  <p class="libelle">Classes</p>
                  <p class="valeur">{{ d.stats.classes }}</p>
                  <p class="tendance">{{ d.deltas.classes > 0 ? '+' + d.deltas.classes + ' ce mois' : 'Stable' }}</p>
                </div>
                <div class="carte">
                  <p class="libelle">Paiements en retard</p>
                  <p class="valeur text-red-700">{{ d.stats.paiementsEnRetard }}</p>
                  <p class="tendance text-red-600">
                    {{ d.deltas.paiementsEnRetard > 0 ? '+' + d.deltas.paiementsEnRetard + ' ce mois' : 'Stable' }}
                  </p>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
                  <h2 class="mb-3 text-sm font-semibold text-slate-900">Dernières notes saisies</h2>
                  <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                      <tr>
                        <th class="py-2">Élève</th>
                        <th class="py-2">Matière</th>
                        <th class="py-2">Note</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                      @for (note of d.dernieresNotes; track $index) {
                        <tr>
                          <td class="py-2 font-medium text-slate-900">{{ note.eleveNom }}</td>
                          <td class="py-2 text-slate-600">{{ note.matiereNom }}</td>
                          <td class="py-2"><span [class]="badgeNote(note.noteSur20)">{{ note.noteSur20 }}/20</span></td>
                        </tr>
                      } @empty {
                        <tr><td colspan="3" class="py-4 text-center text-slate-400">Aucune note enregistrée.</td></tr>
                      }
                    </tbody>
                  </table>
                  <a routerLink="/notes" class="lien mt-3 inline-block">Voir toutes les notes</a>
                </div>

                <div class="space-y-6">
                  <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <h2 class="mb-2 text-sm font-semibold text-slate-900">Taux de présence</h2>
                    <p class="text-3xl font-bold text-emerald-600">
                      {{ d.tauxPresenceGlobal !== null ? d.tauxPresenceGlobal + '%' : '—' }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">Sur le trimestre en cours</p>
                  </div>

                  <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <h2 class="mb-2 text-sm font-semibold text-slate-900">Annonces récentes</h2>
                    <ul class="space-y-2 text-sm">
                      @for (annonce of d.annonces; track annonce.id) {
                        <li>
                          <p class="font-medium text-slate-800">{{ annonce.titre }}</p>
                          <p class="text-slate-500">{{ dateAffichee(annonce.datePublication) }} — {{ annonce.auteurNom }}</p>
                        </li>
                      } @empty {
                        <li class="text-slate-400">Aucune annonce.</li>
                      }
                    </ul>
                    <a routerLink="/annonces" class="lien mt-3 inline-block">Voir toutes les annonces</a>
                  </div>
                </div>
              </div>

              <div class="mt-6 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h2 class="mb-3 text-sm font-semibold text-slate-900">Prochaines échéances</h2>
                <ul class="space-y-2 text-sm">
                  @for (echeance of d.prochainesEcheances; track $index) {
                    <li class="flex items-center justify-between">
                      <span class="text-slate-800">{{ echeance.titre }}</span>
                      <span class="text-slate-500">{{ dateAffichee(echeance.date) }}</span>
                    </li>
                  } @empty {
                    <li class="text-slate-400">Aucune échéance à venir.</li>
                  }
                </ul>
              </div>

              @if (d.elevesARisque.length > 0) {
                <div class="mt-6 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                  <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900">Élèves à risque d'échec</h2>
                    <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700"
                      >{{ d.elevesARisque.length }} élève(s)</span
                    >
                  </div>
                  <ul class="divide-y divide-slate-100 text-sm">
                    @for (eleve of d.elevesARisque; track eleve.id) {
                      <li class="flex items-center justify-between py-2">
                        <span class="font-medium text-slate-900">{{ eleve.prenom }} {{ eleve.nom }}</span>
                        <span class="text-slate-500">{{ eleve.classeNom ?? '—' }}</span>
                      </li>
                    }
                  </ul>
                </div>
              }
            }
          }
          @case ('enseignant') {
            @if (enseignant(); as d) {
              <h1 class="mb-6 text-xl font-semibold text-slate-900">Tableau de bord</h1>
              <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
                  <h2 class="mb-3 text-sm font-semibold text-slate-900">Mes classes</h2>
                  <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                      <tr><th class="py-2">Classe</th><th class="py-2">Effectif</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                      @for (classe of d.classes; track classe.id) {
                        <tr><td class="py-2 font-medium text-slate-900">{{ classe.nom }}</td><td class="py-2 text-slate-600">{{ classe.effectif }}</td></tr>
                      } @empty {
                        <tr><td colspan="2" class="py-4 text-center text-slate-400">Aucune classe assignée.</td></tr>
                      }
                    </tbody>
                  </table>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                  <h2 class="mb-2 text-sm font-semibold text-slate-900">Annonces récentes</h2>
                  <ul class="space-y-2 text-sm">
                    @for (annonce of d.annonces; track annonce.id) {
                      <li>
                        <p class="font-medium text-slate-800">{{ annonce.titre }}</p>
                        <p class="text-slate-500">{{ dateAffichee(annonce.datePublication) }}</p>
                      </li>
                    } @empty {
                      <li class="text-slate-400">Aucune annonce.</li>
                    }
                  </ul>
                </div>
              </div>

              <div class="mt-6 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h2 class="mb-3 text-sm font-semibold text-slate-900">Dernières notes que j'ai saisies</h2>
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                  <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr><th class="py-2">Élève</th><th class="py-2">Matière</th><th class="py-2">Note</th><th class="py-2">Date</th></tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100">
                    @for (note of d.dernieresNotes; track $index) {
                      <tr>
                        <td class="py-2 font-medium text-slate-900">{{ note.eleveNom }}</td>
                        <td class="py-2 text-slate-600">{{ note.matiereNom }}</td>
                        <td class="py-2"><span [class]="badgeNote(note.noteSur20)">{{ note.noteSur20 }}/20</span></td>
                        <td class="py-2 text-slate-500">{{ note.dateEvaluation }}</td>
                      </tr>
                    } @empty {
                      <tr><td colspan="4" class="py-4 text-center text-slate-400">Aucune note saisie.</td></tr>
                    }
                  </tbody>
                </table>
              </div>
            }
          }
          @case ('eleve') {
            @if (eleve(); as d) {
              <h1 class="mb-6 text-xl font-semibold text-slate-900">Tableau de bord</h1>
              <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="carte">
                  <p class="libelle">Moyenne {{ d.trimestre ?? '' }}</p>
                  <p class="valeur">{{ d.moyenne !== null ? d.moyenne + '/20' : '—' }}</p>
                </div>
                <div class="carte">
                  <p class="libelle">Taux de présence</p>
                  <p class="valeur text-emerald-600">{{ d.tauxPresence !== null ? d.tauxPresence + '%' : '—' }}</p>
                </div>
                <div class="carte">
                  <p class="libelle">Solde dû</p>
                  <p [class]="d.solde > 0 ? 'valeur text-red-700' : 'valeur text-emerald-600'">
                    {{ formatMontant(d.solde) }} FCFA
                  </p>
                </div>
              </div>

              <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 lg:col-span-2">
                  <h2 class="mb-3 text-sm font-semibold text-slate-900">Dernières notes</h2>
                  <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                      <tr><th class="py-2">Matière</th><th class="py-2">Note</th><th class="py-2">Date</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                      @for (note of d.dernieresNotes; track $index) {
                        <tr>
                          <td class="py-2 font-medium text-slate-900">{{ note.matiereNom }}</td>
                          <td class="py-2"><span [class]="badgeNote(note.noteSur20)">{{ note.noteSur20 }}/20</span></td>
                          <td class="py-2 text-slate-500">{{ note.dateEvaluation }}</td>
                        </tr>
                      } @empty {
                        <tr><td colspan="3" class="py-4 text-center text-slate-400">Aucune note.</td></tr>
                      }
                    </tbody>
                  </table>
                </div>
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                  <h2 class="mb-2 text-sm font-semibold text-slate-900">Annonces</h2>
                  <ul class="space-y-2 text-sm">
                    @for (annonce of d.annonces; track annonce.id) {
                      <li>
                        <p class="font-medium text-slate-800">{{ annonce.titre }}</p>
                        <p class="text-slate-500">{{ dateAffichee(annonce.datePublication) }}</p>
                      </li>
                    } @empty {
                      <li class="text-slate-400">Aucune annonce.</li>
                    }
                  </ul>
                </div>
              </div>
            }
          }
          @case ('parent') {
            @if (parent(); as d) {
              <h1 class="mb-6 text-xl font-semibold text-slate-900">Tableau de bord</h1>
              <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @for (enfant of d.enfants; track enfant.id) {
                  <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <p class="font-semibold text-slate-900">{{ enfant.prenom }} {{ enfant.nom }}</p>
                    <p class="text-sm text-slate-500">{{ enfant.classeNom ?? 'Sans classe' }}</p>
                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                      <div class="rounded-lg bg-slate-50 p-2.5">
                        <p class="text-slate-500">Moyenne</p>
                        <p class="font-semibold text-slate-900">
                          {{ enfant.moyenneCourante !== null ? enfant.moyenneCourante + '/20' : '—' }}
                        </p>
                      </div>
                      <div class="rounded-lg bg-slate-50 p-2.5">
                        <p class="text-slate-500">Présence</p>
                        <p class="font-semibold text-slate-900">
                          {{ enfant.tauxPresenceCourant !== null ? enfant.tauxPresenceCourant + '%' : '—' }}
                        </p>
                      </div>
                    </div>
                    <a [routerLink]="['/mes-enfants']" class="lien mt-3 inline-block">Voir le détail</a>
                  </div>
                } @empty {
                  <p class="text-slate-400">Aucun enfant lié à votre compte.</p>
                }
              </div>

              <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h2 class="mb-2 text-sm font-semibold text-slate-900">Annonces récentes</h2>
                <ul class="space-y-2 text-sm">
                  @for (annonce of d.annonces; track annonce.id) {
                    <li>
                      <p class="font-medium text-slate-800">{{ annonce.titre }}</p>
                      <p class="text-slate-500">{{ dateAffichee(annonce.datePublication) }}</p>
                    </li>
                  } @empty {
                    <li class="text-slate-400">Aucune annonce.</li>
                  }
                </ul>
              </div>
            }
          }
        }

        <button
          (click)="seDeconnecter()"
          class="mt-8 rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
          Se déconnecter
        </button>
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
      .tendance {
        margin-top: 0.25rem;
        font-size: 0.75rem;
        color: rgb(100 116 139);
      }
    `,
  ],
})
export class DashboardPage {
  private readonly http = inject(HttpClient);
  private readonly authService = inject(AuthService);
  private readonly router = inject(Router);
  private readonly session = toSignal(this.authService.user$, { initialValue: null });

  readonly role = computed(() => this.session()?.role ?? null);
  readonly email = computed(() => this.session()?.email ?? '');
  readonly estSuperAdmin = computed(() => this.role() === 'super_admin');
  readonly chargement = signal(true);

  readonly admin = signal<TableauAdmin | null>(null);
  readonly enseignant = signal<TableauEnseignant | null>(null);
  readonly eleve = signal<TableauEleve | null>(null);
  readonly parent = signal<TableauParent | null>(null);

  constructor() {
    void this.charger();
  }

  private async charger(): Promise<void> {
    // Reproduit la redirection de DashboardController::index() pour super_admin.
    if (this.estSuperAdmin()) {
      await this.router.navigateByUrl('/super-admin');
      return;
    }

    const donnees = await firstValueFrom(this.http.get<unknown>(`${environment.apiUrl}/dashboard`));
    switch (this.role()) {
      case 'admin':
        this.admin.set(donnees as TableauAdmin);
        break;
      case 'enseignant':
        this.enseignant.set(donnees as TableauEnseignant);
        break;
      case 'eleve':
        this.eleve.set(donnees as TableauEleve);
        break;
      case 'parent':
        this.parent.set(donnees as TableauParent);
        break;
    }
    this.chargement.set(false);
  }

  formatMontant(valeur: number): string {
    return new Intl.NumberFormat('fr-FR').format(valeur);
  }

  dateAffichee(valeur: unknown): string {
    return versDateAffichee(valeur as Parameters<typeof versDateAffichee>[0]);
  }

  badgeNote(noteSur20: number): string {
    const base = 'rounded-full px-2 py-0.5 text-xs font-medium';
    if (noteSur20 >= 14) return `${base} bg-emerald-50 text-emerald-700`;
    if (noteSur20 >= 10) return `${base} bg-amber-50 text-amber-700`;
    return `${base} bg-red-50 text-red-700`;
  }

  async seDeconnecter(): Promise<void> {
    await this.authService.deconnexion();
    await this.router.navigateByUrl('/connexion');
  }
}
