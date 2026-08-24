import { Component, computed, inject } from '@angular/core';
import { toSignal } from '@angular/core/rxjs-interop';
import { RouterLink } from '@angular/router';
import { AuthService } from '../../core/auth.service';

interface Etape {
  titre: string;
  lien: string;
  texte: string;
}

@Component({
  selector: 'app-guide-page',
  standalone: true,
  imports: [RouterLink],
  template: `
    <div class="mx-auto max-w-3xl p-8">
      <h1 class="mb-1 text-xl font-semibold text-slate-900">Guide d'utilisation</h1>
      <p class="mb-6 text-sm text-slate-500">
        @switch (role()) {
          @case ('admin') { Les étapes pour mettre en place votre école sur Scolarix, dans l'ordre. }
          @case ('enseignant') { L'essentiel pour utiliser Scolarix au quotidien. }
          @case ('eleve') { Ce que vous pouvez consulter sur Scolarix. }
          @case ('parent') { Ce que vous pouvez consulter sur Scolarix. }
          @default { Bienvenue sur Scolarix. }
        }
      </p>

      @if (role() === 'admin') {
        <ol class="space-y-4">
          @for (etape of etapesAdmin; track etape.titre; let i = $index) {
            <li class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
              <div class="flex items-start gap-3">
                <span
                  class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white"
                  >{{ i + 1 }}</span
                >
                <div>
                  <p class="font-semibold text-slate-900">{{ etape.titre }}</p>
                  <p class="mt-1 text-sm text-slate-600">{{ etape.texte }}</p>
                  <a [routerLink]="etape.lien" class="mt-2 inline-block text-sm font-medium text-brand-600 hover:underline"
                    >Y aller →</a
                  >
                </div>
              </div>
            </li>
          }
        </ol>

        <div class="mt-6 rounded-xl bg-amber-50 p-5 text-sm text-amber-900 ring-1 ring-amber-200">
          <p class="font-semibold">Bon à savoir</p>
          <p class="mt-1">
            Élèves, enseignants et parents n'ont pas de compte de connexion tant que vous ne leur en avez pas généré
            un depuis <a routerLink="/comptes" class="underline">Comptes de connexion</a> — c'est ce qui leur donne
            leur email et mot de passe pour se connecter à Scolarix.
          </p>
          <p class="mt-2">
            Certaines fonctionnalités (bulletins, paiements, messagerie, espace élève/parent…) font partie de l'offre
            Premium. Votre école en bénéficie automatiquement pendant les 30 premiers jours ; ensuite, gérez votre
            abonnement depuis <a routerLink="/abonnement" class="underline">Abonnement</a>.
          </p>
        </div>
      }

      @if (role() === 'enseignant') {
        <ul class="space-y-3 text-sm text-slate-700">
          <li class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="font-semibold text-slate-900">Saisir les notes et présences</p>
            <p class="mt-1">
              Depuis <a routerLink="/notes" class="text-brand-600 hover:underline">Notes</a> et
              <a routerLink="/presences" class="text-brand-600 hover:underline">Présences</a>, choisissez une classe
              pour saisir en une fois toute la liste des élèves.
            </p>
          </li>
          <li class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="font-semibold text-slate-900">Suivre le risque d'échec</p>
            <p class="mt-1">
              La page <a routerLink="/progression" class="text-brand-600 hover:underline">Risque d'échec</a> repère
              automatiquement les élèves dont les moyennes décrochent.
            </p>
          </li>
          <li class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="font-semibold text-slate-900">Contacter les parents</p>
            <p class="mt-1">
              Depuis <a routerLink="/messagerie" class="text-brand-600 hover:underline">Messagerie</a>, vous pouvez
              contacter les parents des élèves de vos classes.
            </p>
          </li>
        </ul>
      }

      @if (role() === 'eleve') {
        <ul class="space-y-3 text-sm text-slate-700">
          <li class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="font-semibold text-slate-900">Mon espace</p>
            <p class="mt-1">
              Depuis <a routerLink="/mon-espace" class="text-brand-600 hover:underline">Mon espace</a>, retrouvez vos
              notes, votre bulletin du trimestre, vos présences et vos paiements.
            </p>
          </li>
        </ul>
      }

      @if (role() === 'parent') {
        <ul class="space-y-3 text-sm text-slate-700">
          <li class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="font-semibold text-slate-900">Mes enfants</p>
            <p class="mt-1">
              Depuis <a routerLink="/mes-enfants" class="text-brand-600 hover:underline">Mes enfants</a>, suivez la
              moyenne, la présence et les paiements de chacun de vos enfants.
            </p>
          </li>
          <li class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <p class="font-semibold text-slate-900">Contacter les enseignants</p>
            <p class="mt-1">
              Depuis <a routerLink="/messagerie" class="text-brand-600 hover:underline">Messagerie</a>, contactez les
              enseignants des classes de vos enfants.
            </p>
          </li>
        </ul>
      }
    </div>
  `,
})
export class GuidePage {
  private readonly authService = inject(AuthService);
  private readonly session = toSignal(this.authService.user$, { initialValue: null });
  readonly role = computed(() => this.session()?.role ?? null);

  readonly etapesAdmin: Etape[] = [
    {
      titre: 'Créer l\'année scolaire et les trimestres',
      lien: '/annees-scolaires',
      texte: 'Définissez l\'année en cours (marquez-la « active ») puis ses trimestres dans Trimestres.',
    },
    {
      titre: 'Créer les matières',
      lien: '/matieres',
      texte: 'La liste des matières enseignées dans votre école, avec leur coefficient par défaut.',
    },
    {
      titre: 'Ajouter les enseignants',
      lien: '/enseignants',
      texte: 'Renseignez chaque enseignant avec son email — il en aura besoin pour se connecter plus tard.',
    },
    {
      titre: 'Créer les classes',
      lien: '/classes',
      texte: 'Rattachez chaque classe à l\'année scolaire, et désignez son enseignant principal si besoin.',
    },
    {
      titre: 'Inscrire les élèves',
      lien: '/eleves',
      texte: 'Ajoutez chaque élève et affectez-le à sa classe.',
    },
    {
      titre: 'Ajouter les parents / tuteurs',
      lien: '/tuteurs',
      texte: 'Renseignez chaque parent avec son email, et liez-le à son ou ses enfants.',
    },
    {
      titre: 'Générer les comptes de connexion',
      lien: '/comptes',
      texte: 'Créez l\'identifiant et le mot de passe de chaque enseignant, élève et parent qui doit se connecter.',
    },
    {
      titre: 'Mettre en place l\'emploi du temps',
      lien: '/emploi-du-temps',
      texte: 'Définissez les créneaux horaires de chaque classe (jour, matière, enseignant, salle).',
    },
    {
      titre: 'Suivre les notes et présences',
      lien: '/notes',
      texte: 'Une fois les enseignants connectés, ils saisiront eux-mêmes leurs notes et présences au quotidien.',
    },
    {
      titre: 'Suivre les paiements',
      lien: '/paiements',
      texte: 'Enregistrez les frais de scolarité ; des relances automatiques sont envoyées avant chaque échéance.',
    },
  ];
}
