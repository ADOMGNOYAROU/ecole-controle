import {
  ForbiddenException,
  Inject,
  Injectable,
  NotFoundException,
} from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import type { AuthenticatedUser } from '../auth/types';
import { BulletinsService } from '../bulletins/bulletins.service';
import {
  ecoleCollection,
  trouverEleveIdParUid,
  trouverEnseignantIdParUid,
  trouverTrimestreActuelId,
  trouverTuteurIdParUid,
  versDate,
} from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { PaiementsService } from '../paiements/paiements.service';
import { ProgressionService } from '../progression/progression.service';

interface Paiement extends Record<string, unknown> {
  statut: string;
  montant: number;
  montantPaye: number;
}

@Injectable()
export class DashboardService {
  constructor(
    @Inject(FIRESTORE) private readonly db: Firestore,
    private readonly bulletinsService: BulletinsService,
    private readonly paiementsService: PaiementsService,
    private readonly progressionService: ProgressionService,
  ) {}

  // Reproduit DashboardController::index() : le contenu dépend entièrement
  // du rôle connecté, comme dans l'app Laravel d'origine (super_admin est
  // géré côté frontend par une redirection directe vers /super-admin).
  async pourUtilisateur(ecoleId: string | null, user: AuthenticatedUser) {
    if (!ecoleId) {
      throw new ForbiddenException('Aucune école associée à ce compte.');
    }
    switch (user.role) {
      case 'admin':
        return this.pourAdmin(ecoleId);
      case 'enseignant':
        return this.pourEnseignant(ecoleId, user.uid);
      case 'eleve':
        return this.pourEleve(ecoleId, user.uid);
      case 'parent':
        return this.pourParent(ecoleId, user.uid);
      default:
        throw new ForbiddenException('Rôle non pris en charge.');
    }
  }

  private async pourAdmin(ecoleId: string) {
    const [
      ecoleSnap,
      elevesSnap,
      enseignantsSnap,
      classesSnap,
      paiementsSnap,
      trimestreId,
    ] = await Promise.all([
      this.db.collection('ecoles').doc(ecoleId).get(),
      ecoleCollection(this.db, ecoleId, 'eleves').get(),
      ecoleCollection(this.db, ecoleId, 'enseignants').get(),
      ecoleCollection(this.db, ecoleId, 'classes').get(),
      ecoleCollection(this.db, ecoleId, 'paiements').get(),
      trouverTrimestreActuelId(this.db, ecoleId),
    ]);

    const maintenant = new Date();
    const debutMois = new Date(
      maintenant.getFullYear(),
      maintenant.getMonth(),
      1,
    );

    const eleves = elevesSnap.docs.map((d) => d.data());
    const enseignants = enseignantsSnap.docs.map((d) => d.data());
    const classes = classesSnap.docs.map((d) => d.data());
    const paiements = paiementsSnap.docs.map((d) => d.data());

    const paiementsEnRetard = paiements.filter(
      (p) =>
        p['statut'] !== 'paye' &&
        new Date(p['dateEcheance'] as string) < maintenant,
    );

    const creeDepuis = (
      item: Record<string, unknown>,
      seuil: Date,
    ): boolean => {
      const cree = versDate(item['createdAt']);
      return cree !== undefined && cree >= seuil;
    };

    const stats = {
      eleves: eleves.filter((e) => e['statut'] === 'actif').length,
      enseignants: enseignants.length,
      classes: classes.length,
      paiementsEnRetard: paiementsEnRetard.length,
    };

    const deltas = {
      eleves: eleves.filter((e) => creeDepuis(e, debutMois)).length,
      enseignants: enseignants.filter((e) => creeDepuis(e, debutMois)).length,
      classes: classes.filter((e) => creeDepuis(e, debutMois)).length,
      paiementsEnRetard: paiements.filter((p) => {
        if (p['statut'] === 'paye') return false;
        const echeance = new Date(p['dateEcheance'] as string);
        return echeance >= debutMois && echeance <= maintenant;
      }).length,
    };

    let tauxPresenceGlobal: number | null = null;
    let trimestreNom: string | null = null;
    if (trimestreId) {
      const trimestreSnap = await ecoleCollection(
        this.db,
        ecoleId,
        'trimestres',
      )
        .doc(trimestreId)
        .get();
      trimestreNom =
        (trimestreSnap.data()?.['nom'] as string | undefined) ?? null;

      const presencesSnap = await ecoleCollection(this.db, ecoleId, 'presences')
        .where('trimestreId', '==', trimestreId)
        .get();
      if (!presencesSnap.empty) {
        const presents = presencesSnap.docs.filter(
          (d) => d.data()['statut'] === 'present',
        ).length;
        tauxPresenceGlobal =
          Math.round((presents / presencesSnap.size) * 1000) / 10;
      }
    }

    const [dernieresNotes, annonces, prochainesEcheances, elevesARisqueBruts] =
      await Promise.all([
        this.dernieresNotesEcole(ecoleId, 7),
        this.dernieresAnnonces(ecoleId, 4),
        this.prochainesEcheances(ecoleId, trimestreId),
        this.progressionService.listerElevesARisque(ecoleId, 6),
      ]);

    const elevesARisque = await Promise.all(
      elevesARisqueBruts.map(async (e) => {
        let classeNom: string | null = null;
        if (e.classeId) {
          const classeSnap = await ecoleCollection(this.db, ecoleId, 'classes')
            .doc(e.classeId)
            .get();
          classeNom =
            (classeSnap.data()?.['nom'] as string | undefined) ?? null;
        }
        return { id: e.id, nom: e.nom, prenom: e.prenom, classeNom };
      }),
    );

    return {
      ecoleNom: (ecoleSnap.data()?.['nom'] as string | undefined) ?? null,
      trimestre: trimestreNom,
      stats,
      deltas,
      tauxPresenceGlobal,
      dernieresNotes,
      annonces,
      prochainesEcheances,
      elevesARisque,
    };
  }

  private async pourEnseignant(ecoleId: string, uid: string) {
    const enseignantId = await trouverEnseignantIdParUid(this.db, ecoleId, uid);
    if (!enseignantId) {
      return {
        classes: [],
        dernieresNotes: [],
        annonces: await this.dernieresAnnonces(ecoleId, 5),
      };
    }

    const enseignantSnap = await ecoleCollection(
      this.db,
      ecoleId,
      'enseignants',
    )
      .doc(enseignantId)
      .get();
    const classeIds =
      (enseignantSnap.data()?.['classeIds'] as string[] | undefined) ?? [];

    const classes = await Promise.all(
      classeIds.map(async (id) => {
        const [classeSnap, elevesSnap] = await Promise.all([
          ecoleCollection(this.db, ecoleId, 'classes').doc(id).get(),
          ecoleCollection(this.db, ecoleId, 'eleves')
            .where('classeId', '==', id)
            .get(),
        ]);
        return {
          id,
          nom: (classeSnap.data()?.['nom'] as string | undefined) ?? '—',
          effectif: elevesSnap.size,
        };
      }),
    );

    // Filtre sur enseignantId (un seul champ) + tri par date en mémoire,
    // pour éviter un index composite Firestore.
    const notesSnap = await ecoleCollection(this.db, ecoleId, 'notes')
      .where('enseignantId', '==', enseignantId)
      .get();
    const notesTriees = notesSnap.docs
      .map((d) => d.data())
      .sort((a, b) =>
        (b['dateEvaluation'] as string).localeCompare(
          a['dateEvaluation'] as string,
        ),
      )
      .slice(0, 8);

    const dernieresNotes = await Promise.all(
      notesTriees.map((n) => this.formaterNote(ecoleId, n, true)),
    );

    return {
      classes,
      dernieresNotes,
      annonces: await this.dernieresAnnonces(ecoleId, 5),
    };
  }

  private async pourEleve(ecoleId: string, uid: string) {
    const eleveId = await trouverEleveIdParUid(this.db, ecoleId, uid);
    if (!eleveId) {
      throw new NotFoundException("Aucune fiche élève n'est liée à ce compte.");
    }

    const trimestreId = await trouverTrimestreActuelId(this.db, ecoleId);
    let trimestreNom: string | null = null;
    let moyenne: number | null = null;
    let tauxPresence: number | null = null;
    if (trimestreId) {
      const [trimestreSnap, donnees, taux] = await Promise.all([
        ecoleCollection(this.db, ecoleId, 'trimestres').doc(trimestreId).get(),
        this.bulletinsService.calculerDonnees(ecoleId, eleveId, trimestreId),
        this.bulletinsService.calculerTauxPresence(
          ecoleId,
          eleveId,
          trimestreId,
        ),
      ]);
      trimestreNom =
        (trimestreSnap.data()?.['nom'] as string | undefined) ?? null;
      moyenne = donnees.moyenneGenerale;
      tauxPresence = taux;
    }

    const notesSnap = await ecoleCollection(this.db, ecoleId, 'notes')
      .where('eleveId', '==', eleveId)
      .get();
    const notesTriees = notesSnap.docs
      .map((d) => d.data())
      .sort((a, b) =>
        (b['dateEvaluation'] as string).localeCompare(
          a['dateEvaluation'] as string,
        ),
      )
      .slice(0, 8);
    const dernieresNotes = await Promise.all(
      notesTriees.map((n) => this.formaterNote(ecoleId, n, false)),
    );

    const solde = await this.soldeDu(ecoleId, eleveId);

    return {
      trimestre: trimestreNom,
      moyenne,
      tauxPresence,
      solde,
      dernieresNotes,
      annonces: await this.dernieresAnnonces(ecoleId, 5),
    };
  }

  private async pourParent(ecoleId: string, uid: string) {
    const tuteurId = await trouverTuteurIdParUid(this.db, ecoleId, uid);
    if (!tuteurId) {
      throw new NotFoundException(
        "Aucune fiche tuteur n'est liée à ce compte.",
      );
    }

    const tuteurSnap = await ecoleCollection(this.db, ecoleId, 'tuteurs')
      .doc(tuteurId)
      .get();
    const references =
      (tuteurSnap.data()?.['eleves'] as { id: string }[] | undefined) ?? [];
    const trimestreId = await trouverTrimestreActuelId(this.db, ecoleId);

    const enfants = await Promise.all(
      references.map(async ({ id }) => {
        const eleveSnap = await ecoleCollection(this.db, ecoleId, 'eleves')
          .doc(id)
          .get();
        if (!eleveSnap.exists) return null;
        const eleve = eleveSnap.data()!;

        let classeNom: string | null = null;
        if (eleve['classeId']) {
          const classeSnap = await ecoleCollection(this.db, ecoleId, 'classes')
            .doc(eleve['classeId'] as string)
            .get();
          classeNom =
            (classeSnap.data()?.['nom'] as string | undefined) ?? null;
        }

        const [donnees, tauxPresenceCourant, soldeDu] = await Promise.all([
          trimestreId
            ? this.bulletinsService.calculerDonnees(ecoleId, id, trimestreId)
            : Promise.resolve(null),
          trimestreId
            ? this.bulletinsService.calculerTauxPresence(
                ecoleId,
                id,
                trimestreId,
              )
            : Promise.resolve(null),
          this.soldeDu(ecoleId, id),
        ]);

        return {
          id,
          nom: eleve['nom'] as string,
          prenom: eleve['prenom'] as string,
          classeNom,
          moyenneCourante: donnees?.moyenneGenerale ?? null,
          tauxPresenceCourant,
          soldeDu,
        };
      }),
    );

    return {
      enfants: enfants.filter((e): e is NonNullable<typeof e> => e !== null),
      annonces: await this.dernieresAnnonces(ecoleId, 5),
    };
  }

  private async soldeDu(ecoleId: string, eleveId: string): Promise<number> {
    const paiements = (await this.paiementsService.lister(ecoleId, {
      eleveId,
    })) as unknown as Paiement[];
    return paiements
      .filter((p) => p.statut !== 'paye')
      .reduce((total, p) => total + (p.montant - p.montantPaye), 0);
  }

  private async formaterNote(
    ecoleId: string,
    note: Record<string, unknown>,
    avecEleve: boolean,
  ) {
    const [matiereSnap, eleveSnap] = await Promise.all([
      ecoleCollection(this.db, ecoleId, 'matieres')
        .doc(note['matiereId'] as string)
        .get(),
      avecEleve
        ? ecoleCollection(this.db, ecoleId, 'eleves')
            .doc(note['eleveId'] as string)
            .get()
        : Promise.resolve(null),
    ]);

    return {
      eleveNom: eleveSnap
        ? `${eleveSnap.data()?.['prenom'] as string} ${eleveSnap.data()?.['nom'] as string}`
        : undefined,
      matiereNom: (matiereSnap.data()?.['nom'] as string | undefined) ?? '—',
      noteSur20:
        Math.round(
          ((note['valeur'] as number) / (note['bareme'] as number)) * 20 * 100,
        ) / 100,
      dateEvaluation: note['dateEvaluation'] as string,
    };
  }

  // Single-field orderBy + limit : pas d'index composite nécessaire.
  private async dernieresNotesEcole(ecoleId: string, limite: number) {
    const snap = await ecoleCollection(this.db, ecoleId, 'notes')
      .orderBy('dateEvaluation', 'desc')
      .limit(limite)
      .get();
    return Promise.all(
      snap.docs.map((doc) => this.formaterNote(ecoleId, doc.data(), true)),
    );
  }

  private async dernieresAnnonces(ecoleId: string, limite: number) {
    const snap = await ecoleCollection(this.db, ecoleId, 'annonces')
      .orderBy('datePublication', 'desc')
      .limit(limite)
      .get();
    return Promise.all(
      snap.docs.map(async (doc) => {
        const a = doc.data();
        const auteurSnap = await this.db
          .collection('users')
          .doc(a['auteurId'] as string)
          .get();
        return {
          id: doc.id,
          titre: a['titre'] as string,
          datePublication: a['datePublication'] as unknown,
          auteurNom:
            (auteurSnap.data()?.['name'] as string | undefined) ?? null,
        };
      }),
    );
  }

  // Reproduit DashboardController::prochainesEcheances().
  private async prochainesEcheances(
    ecoleId: string,
    trimestreId: string | null,
  ) {
    const maintenant = new Date();
    const echeances: { date: Date; titre: string }[] = [];

    if (trimestreId) {
      const trimestreSnap = await ecoleCollection(
        this.db,
        ecoleId,
        'trimestres',
      )
        .doc(trimestreId)
        .get();
      const dateFin = versDate(trimestreSnap.data()?.['dateFin']);
      if (dateFin && dateFin > maintenant) {
        echeances.push({
          date: dateFin,
          titre: `Fin du ${trimestreSnap.data()!['nom'] as string}`,
        });
      }
    }

    const anneeSnap = await ecoleCollection(this.db, ecoleId, 'anneesScolaires')
      .where('active', '==', true)
      .limit(1)
      .get();
    if (!anneeSnap.empty) {
      const annee = anneeSnap.docs[0].data();
      const dateFin = versDate(annee['dateFin']);
      if (dateFin && dateFin > maintenant) {
        echeances.push({
          date: dateFin,
          titre: `Fin de l'année scolaire ${annee['libelle'] as string}`,
        });
      }
    }

    const facturesSnap = await ecoleCollection(this.db, ecoleId, 'factures')
      .where('statut', '==', 'en_attente')
      .get();
    const prochaineFacture = facturesSnap.docs
      .map((d) => versDate(d.data()['dateEcheance']))
      .filter((d): d is Date => d !== undefined)
      .sort((a, b) => a.getTime() - b.getTime())[0];
    if (prochaineFacture) {
      echeances.push({
        date: prochaineFacture,
        titre: 'Facture abonnement Premium à régler',
      });
    }

    return echeances
      .sort((a, b) => a.date.getTime() - b.date.getTime())
      .slice(0, 3);
  }
}
