import { Inject, Injectable } from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { BulletinsService } from '../bulletins/bulletins.service';
import { ecoleCollection, getDocOrThrow } from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';

const SEUIL_RISQUE = 10.0;

interface MoyenneTrimestre {
  trimestreId: string;
  label: string;
  moyenne: number;
}

interface MoyenneMatiere {
  matiereId: string;
  nom: string;
  moyenne: number;
  nombreNotes: number;
}

interface TauxPresenceTrimestre {
  trimestreId: string;
  label: string;
  tauxPresence: number | null;
}

export interface AnalyseProgression {
  moyennesParTrimestre: MoyenneTrimestre[];
  moyennesParMatiere: MoyenneMatiere[];
  pointsForts: MoyenneMatiere[];
  pointsFaibles: MoyenneMatiere[];
  tauxPresence: TauxPresenceTrimestre[];
  tendance: 'hausse' | 'baisse' | 'stable';
  risque: {
    enRisque: boolean;
    niveau: 'faible' | 'moyen' | 'eleve';
    trimestresSousSeuil: number;
    seuil: number;
    derniereMoyenne: number | null;
  };
}

@Injectable()
export class ProgressionService {
  constructor(
    @Inject(FIRESTORE) private readonly db: Firestore,
    private readonly bulletinsService: BulletinsService,
  ) {}

  async analyser(
    ecoleId: string,
    eleveId: string,
  ): Promise<AnalyseProgression> {
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'eleves').doc(eleveId),
      'Élève introuvable.',
    );

    const trimestres = await this.trimestresAvecNotes(ecoleId, eleveId);

    const moyennesParTrimestre = await this.moyennesParTrimestre(
      ecoleId,
      eleveId,
      trimestres,
    );
    const moyennesParMatiere = await this.moyennesParMatiere(ecoleId, eleveId);
    const tauxPresence = await Promise.all(
      trimestres.map(async (t) => ({
        trimestreId: t.id,
        label: t.nom,
        tauxPresence: await this.bulletinsService.calculerTauxPresence(
          ecoleId,
          eleveId,
          t.id,
        ),
      })),
    );

    const parMoyenneDesc = [...moyennesParMatiere].sort(
      (a, b) => b.moyenne - a.moyenne,
    );
    const pointsForts = parMoyenneDesc.slice(0, 3);
    const pointsFaibles = [...moyennesParMatiere]
      .sort((a, b) => a.moyenne - b.moyenne)
      .slice(0, 3);

    return {
      moyennesParTrimestre,
      moyennesParMatiere,
      pointsForts,
      pointsFaibles,
      tauxPresence,
      tendance: this.calculerTendance(moyennesParTrimestre),
      risque: this.evaluerRisque(moyennesParTrimestre),
    };
  }

  private async trimestresAvecNotes(
    ecoleId: string,
    eleveId: string,
  ): Promise<
    { id: string; nom: string; ordre: number; anneeDateDebut: number }[]
  > {
    const notesSnap = await ecoleCollection(this.db, ecoleId, 'notes')
      .where('eleveId', '==', eleveId)
      .get();
    const trimestreIds = [
      ...new Set(
        notesSnap.docs.map((doc) => doc.data()['trimestreId'] as string),
      ),
    ];

    const trimestres = await Promise.all(
      trimestreIds.map(async (id) => {
        const snap = await ecoleCollection(this.db, ecoleId, 'trimestres')
          .doc(id)
          .get();
        const data = snap.data();
        if (!data) return null;

        const anneeSnap = await ecoleCollection(
          this.db,
          ecoleId,
          'anneesScolaires',
        )
          .doc(data['anneeScolaireId'] as string)
          .get();
        const anneeDateDebut =
          (
            anneeSnap.data()?.['dateDebut'] as
              FirebaseFirestore.Timestamp | undefined
          )?.toMillis() ?? 0;

        return {
          id,
          nom: data['nom'] as string,
          ordre: data['ordre'] as number,
          anneeDateDebut,
        };
      }),
    );

    return trimestres
      .filter((t): t is NonNullable<typeof t> => t !== null)
      .sort((a, b) => a.anneeDateDebut - b.anneeDateDebut || a.ordre - b.ordre);
  }

  private async moyennesParTrimestre(
    ecoleId: string,
    eleveId: string,
    trimestres: { id: string; nom: string }[],
  ): Promise<MoyenneTrimestre[]> {
    const resultats: MoyenneTrimestre[] = [];

    for (const trimestre of trimestres) {
      const bulletinSnap = await ecoleCollection(this.db, ecoleId, 'bulletins')
        .doc(`${eleveId}_${trimestre.id}`)
        .get();
      const moyenne = bulletinSnap.exists
        ? (bulletinSnap.data()!['moyenneGenerale'] as number | null)
        : (
            await this.bulletinsService.calculerDonnees(
              ecoleId,
              eleveId,
              trimestre.id,
            )
          ).moyenneGenerale;

      if (moyenne !== null) {
        resultats.push({
          trimestreId: trimestre.id,
          label: trimestre.nom,
          moyenne,
        });
      }
    }

    return resultats;
  }

  private async moyennesParMatiere(
    ecoleId: string,
    eleveId: string,
  ): Promise<MoyenneMatiere[]> {
    const notesSnap = await ecoleCollection(this.db, ecoleId, 'notes')
      .where('eleveId', '==', eleveId)
      .get();

    const parMatiere = new Map<
      string,
      { totalPondere: number; totalCoefficient: number; nombreNotes: number }
    >();
    for (const doc of notesSnap.docs) {
      const note = doc.data();
      const matiereId = note['matiereId'] as string;
      const noteSur20 =
        ((note['valeur'] as number) / (note['bareme'] as number)) * 20;
      const coefficient = note['coefficient'] as number;

      const cumul = parMatiere.get(matiereId) ?? {
        totalPondere: 0,
        totalCoefficient: 0,
        nombreNotes: 0,
      };
      cumul.totalPondere += noteSur20 * coefficient;
      cumul.totalCoefficient += coefficient;
      cumul.nombreNotes += 1;
      parMatiere.set(matiereId, cumul);
    }

    const resultats: MoyenneMatiere[] = [];
    for (const [matiereId, cumul] of parMatiere.entries()) {
      if (cumul.totalCoefficient <= 0) continue;
      const matiereSnap = await ecoleCollection(this.db, ecoleId, 'matieres')
        .doc(matiereId)
        .get();
      resultats.push({
        matiereId,
        nom: (matiereSnap.data()?.['nom'] as string | undefined) ?? 'Matière',
        moyenne:
          Math.round((cumul.totalPondere / cumul.totalCoefficient) * 100) / 100,
        nombreNotes: cumul.nombreNotes,
      });
    }

    return resultats;
  }

  private calculerTendance(
    moyennes: MoyenneTrimestre[],
  ): 'hausse' | 'baisse' | 'stable' {
    if (moyennes.length < 2) return 'stable';

    const diff =
      moyennes[moyennes.length - 1].moyenne -
      moyennes[moyennes.length - 2].moyenne;
    if (diff > 0.5) return 'hausse';
    if (diff < -0.5) return 'baisse';
    return 'stable';
  }

  private evaluerRisque(
    moyennes: MoyenneTrimestre[],
  ): AnalyseProgression['risque'] {
    let consecutifs = 0;
    let maxConsecutifs = 0;

    for (const { moyenne } of moyennes) {
      if (moyenne < SEUIL_RISQUE) {
        consecutifs += 1;
        maxConsecutifs = Math.max(maxConsecutifs, consecutifs);
      } else {
        consecutifs = 0;
      }
    }

    const derniereMoyenne =
      moyennes.length > 0 ? moyennes[moyennes.length - 1].moyenne : null;
    const enRisque =
      maxConsecutifs >= 2 ||
      (derniereMoyenne !== null && derniereMoyenne < SEUIL_RISQUE);

    let niveau: 'faible' | 'moyen' | 'eleve' = 'faible';
    if (
      maxConsecutifs >= 3 ||
      (derniereMoyenne !== null && derniereMoyenne < 7.0)
    ) {
      niveau = 'eleve';
    } else if (
      maxConsecutifs >= 2 ||
      (derniereMoyenne !== null && derniereMoyenne < SEUIL_RISQUE)
    ) {
      niveau = 'moyen';
    }

    return {
      enRisque,
      niveau,
      trimestresSousSeuil: maxConsecutifs,
      seuil: SEUIL_RISQUE,
      derniereMoyenne,
    };
  }
}
