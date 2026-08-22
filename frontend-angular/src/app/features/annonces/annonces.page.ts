import { HttpClient } from '@angular/common/http';
import { Component, computed, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { toSignal } from '@angular/core/rxjs-interop';
import { firstValueFrom } from 'rxjs';
import { environment } from '../../../environments/environment';
import { AuthService } from '../../core/auth.service';
import { crudApi, messageErreur } from '../../core/crud';

interface Classe {
  id: string;
  nom: string;
}
interface Annonce {
  id: string;
  titre: string;
  contenu: string;
  cible: 'tous' | 'parents' | 'enseignants' | 'eleves' | 'classe';
  classeId: string | null;
  auteurId: string;
  datePublication: { _seconds: number } | null;
}

@Component({
  selector: 'app-annonces-page',
  standalone: true,
  imports: [FormsModule],
  template: `
    <div class="p-8">
      <h1 class="mb-6 text-xl font-semibold text-slate-900">Annonces</h1>

      @if (peutPublier()) {
        <form
          (ngSubmit)="publier()"
          class="mb-6 grid grid-cols-1 gap-4 rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:grid-cols-4"
        >
          <div class="sm:col-span-2">
            <label class="mb-1 block text-sm font-medium text-slate-700">Titre</label>
            <input [(ngModel)]="titre" name="titre" required maxlength="150" class="champ" />
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Cible</label>
            <select [(ngModel)]="cible" name="cible" class="champ">
              <option value="tous">Tous</option>
              <option value="parents">Parents</option>
              <option value="enseignants">Enseignants</option>
              <option value="eleves">Élèves</option>
              <option value="classe">Une classe</option>
            </select>
          </div>
          @if (cible === 'classe') {
            <div>
              <label class="mb-1 block text-sm font-medium text-slate-700">Classe</label>
              <select [(ngModel)]="classeId" name="classeId" class="champ">
                <option value="" disabled>Choisir…</option>
                @for (classe of classes(); track classe.id) {
                  <option [value]="classe.id">{{ classe.nom }}</option>
                }
              </select>
            </div>
          }
          <div class="sm:col-span-4">
            <label class="mb-1 block text-sm font-medium text-slate-700">Contenu</label>
            <textarea [(ngModel)]="contenu" name="contenu" required rows="3" maxlength="5000" class="champ"></textarea>
          </div>
          <div class="sm:col-span-4 flex justify-end">
            <button type="submit" [disabled]="enCours()" class="btn-primary">Publier</button>
          </div>
          @if (erreur()) {
            <p class="text-sm text-red-600 sm:col-span-4">{{ erreur() }}</p>
          }
        </form>
      }

      <div class="space-y-3">
        @for (annonce of items(); track annonce.id) {
          <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-semibold text-slate-900">{{ annonce.titre }}</p>
                <p class="mt-1 text-sm text-slate-600">{{ annonce.contenu }}</p>
                <p class="mt-2 text-xs text-slate-400">
                  {{ libelleCible(annonce) }} · {{ dateAffichee(annonce.datePublication) }}
                </p>
              </div>
              @if (peutSupprimer(annonce)) {
                <button (click)="supprimer(annonce)" class="lien text-red-600 shrink-0">Supprimer</button>
              }
            </div>
          </div>
        } @empty {
          <p class="text-sm text-slate-400">Aucune annonce.</p>
        }
      </div>
    </div>
  `,
})
export class AnnoncesPage {
  private readonly http = inject(HttpClient);
  private readonly authService = inject(AuthService);
  private readonly classesApi = crudApi<Classe>(this.http, `${environment.apiUrl}/classes`);

  readonly items = signal<Annonce[]>([]);
  readonly classes = signal<Classe[]>([]);
  readonly enCours = signal(false);
  readonly erreur = signal<string | null>(null);

  private readonly session = toSignal(this.authService.user$, { initialValue: null });
  readonly peutPublier = computed(() => {
    const role = this.session()?.role;
    return role === 'admin' || role === 'enseignant';
  });

  titre = '';
  contenu = '';
  cible: Annonce['cible'] = 'tous';
  classeId = '';

  constructor() {
    void this.initialiser();
  }

  libelleCible(annonce: Annonce): string {
    if (annonce.cible === 'classe') {
      return `Classe : ${this.classes().find((c) => c.id === annonce.classeId)?.nom ?? '—'}`;
    }
    return { tous: 'Tous', parents: 'Parents', enseignants: 'Enseignants', eleves: 'Élèves' }[annonce.cible];
  }

  dateAffichee(ts: { _seconds: number } | null): string {
    if (!ts) return '';
    return new Date(ts._seconds * 1000).toLocaleDateString('fr-FR');
  }

  peutSupprimer(annonce: Annonce): boolean {
    const session = this.session();
    return session?.role === 'admin' || session?.uid === annonce.auteurId;
  }

  private async initialiser(): Promise<void> {
    const [classes] = await Promise.all([this.classesApi.lister(), this.charger()]);
    this.classes.set(classes);
  }

  async charger(): Promise<void> {
    this.items.set(await firstValueFrom(this.http.get<Annonce[]>(`${environment.apiUrl}/annonces`)));
  }

  async publier(): Promise<void> {
    this.erreur.set(null);
    this.enCours.set(true);
    try {
      await firstValueFrom(
        this.http.post(`${environment.apiUrl}/annonces`, {
          titre: this.titre,
          contenu: this.contenu,
          cible: this.cible,
          classeId: this.cible === 'classe' ? this.classeId : undefined,
        }),
      );
      this.titre = '';
      this.contenu = '';
      this.cible = 'tous';
      this.classeId = '';
      await this.charger();
    } catch (error) {
      this.erreur.set(messageErreur(error));
    } finally {
      this.enCours.set(false);
    }
  }

  async supprimer(annonce: Annonce): Promise<void> {
    if (!confirm(`Supprimer l'annonce "${annonce.titre}" ?`)) return;
    await firstValueFrom(this.http.delete(`${environment.apiUrl}/annonces/${annonce.id}`));
    await this.charger();
  }
}
