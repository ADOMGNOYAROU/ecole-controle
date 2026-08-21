import { Inject, Injectable } from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue } from 'firebase-admin/firestore';
import { ecoleCollection, getDocOrThrow } from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { MatiereBulletin, PdfService } from '../pdf/pdf.service';

interface DonneesBulletin {
  matieres: MatiereBulletin[];
  moyenneGenerale: number | null;
}

export interface BulletinSnapshot {
  id: string;
  eleveId: string;
  classeId: string | null;
  trimestreId: string;
  moyenneGenerale: number | null;
  rang: number | null;
  appreciation: string;
  tauxPresence: number | null;
  matieresDetail: MatiereBulletin[];
}

@Injectable()
export class BulletinsService {
  constructor(
    @Inject(FIRESTORE) private readonly db: Firestore,
    private readonly pdfService: PdfService,
  ) {}

  async lister(
    ecoleId: string,
    filtres: { classeId?: string; trimestreId?: string },
  ) {
    let query = ecoleCollection(
      this.db,
      ecoleId,
      'bulletins',
    ) as FirebaseFirestore.Query;
    if (filtres.classeId)
      query = query.where('classeId', '==', filtres.classeId);
    if (filtres.trimestreId)
      query = query.where('trimestreId', '==', filtres.trimestreId);
    const snap = await query.get();
    return snap.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
  }

  // Reproduit BulletinController::genererClasse() : calcule la moyenne de
  // chaque élève actif de la classe, les classe par moyenne décroissante
  // pour établir le rang, puis enregistre un instantané par élève.
  async genererPourClasse(
    ecoleId: string,
    classeId: string,
    trimestreId: string,
  ): Promise<{ genere: number }> {
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'classes').doc(classeId),
      'Classe introuvable.',
    );
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'trimestres').doc(trimestreId),
      'Trimestre introuvable.',
    );

    const elevesSnap = await ecoleCollection(this.db, ecoleId, 'eleves')
      .where('classeId', '==', classeId)
      .where('statut', '==', 'actif')
      .get();

    const entrees = await Promise.all(
      elevesSnap.docs.map(async (doc) => {
        const donnees = await this.calculerDonnees(
          ecoleId,
          doc.id,
          trimestreId,
        );
        return { eleveId: doc.id, donnees };
      }),
    );

    entrees.sort(
      (a, b) =>
        (b.donnees.moyenneGenerale ?? -Infinity) -
        (a.donnees.moyenneGenerale ?? -Infinity),
    );

    const batch = this.db.batch();
    for (const [index, entree] of entrees.entries()) {
      const tauxPresence = await this.calculerTauxPresence(
        ecoleId,
        entree.eleveId,
        trimestreId,
      );
      const ref = ecoleCollection(this.db, ecoleId, 'bulletins').doc(
        `${entree.eleveId}_${trimestreId}`,
      );
      batch.set(ref, {
        eleveId: entree.eleveId,
        classeId,
        trimestreId,
        moyenneGenerale: entree.donnees.moyenneGenerale,
        rang: index + 1,
        appreciation: this.appreciation(entree.donnees.moyenneGenerale),
        tauxPresence,
        matieresDetail: entree.donnees.matieres,
        genereLe: FieldValue.serverTimestamp(),
      });
    }
    await batch.commit();

    return { genere: entrees.length };
  }

  // Reproduit BulletinController::show() : sert l'instantané existant, ou
  // le calcule à la volée (y compris le rang) si aucun bulletin n'a encore
  // été généré pour cet élève sur ce trimestre.
  async obtenirOuGenerer(
    ecoleId: string,
    eleveId: string,
    trimestreId: string,
  ): Promise<BulletinSnapshot> {
    const ref = ecoleCollection(this.db, ecoleId, 'bulletins').doc(
      `${eleveId}_${trimestreId}`,
    );
    const snap = await ref.get();
    if (snap.exists) {
      return { id: snap.id, ...snap.data() } as BulletinSnapshot;
    }

    const eleveSnap = await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'eleves').doc(eleveId),
      'Élève introuvable.',
    );
    const classeId = (eleveSnap.data()!['classeId'] as string | null) ?? null;

    const donnees = await this.calculerDonnees(ecoleId, eleveId, trimestreId);
    const tauxPresence = await this.calculerTauxPresence(
      ecoleId,
      eleveId,
      trimestreId,
    );
    const rang = classeId
      ? await this.calculerRang(ecoleId, classeId, eleveId, trimestreId)
      : null;

    const snapshot: BulletinSnapshot = {
      id: ref.id,
      eleveId,
      classeId,
      trimestreId,
      moyenneGenerale: donnees.moyenneGenerale,
      rang,
      appreciation: this.appreciation(donnees.moyenneGenerale),
      tauxPresence,
      matieresDetail: donnees.matieres,
    };

    await ref.set({ ...snapshot, genereLe: FieldValue.serverTimestamp() });
    return snapshot;
  }

  async genererPdf(
    ecoleId: string,
    eleveId: string,
    trimestreId: string,
  ): Promise<Buffer> {
    const snapshot = await this.obtenirOuGenerer(ecoleId, eleveId, trimestreId);

    const [eleveSnap, trimestreSnap] = await Promise.all([
      getDocOrThrow(
        ecoleCollection(this.db, ecoleId, 'eleves').doc(eleveId),
        'Élève introuvable.',
      ),
      getDocOrThrow(
        ecoleCollection(this.db, ecoleId, 'trimestres').doc(trimestreId),
        'Trimestre introuvable.',
      ),
    ]);
    const eleve = eleveSnap.data()!;
    const trimestre = trimestreSnap.data()!;

    let classeNom = '—';
    let anneeScolaireLibelle = '—';
    if (snapshot.classeId) {
      const classeSnap = await ecoleCollection(this.db, ecoleId, 'classes')
        .doc(snapshot.classeId)
        .get();
      classeNom = (classeSnap.data()?.['nom'] as string | undefined) ?? '—';
    }
    const anneeSnap = await ecoleCollection(this.db, ecoleId, 'anneesScolaires')
      .doc(trimestre['anneeScolaireId'] as string)
      .get();
    anneeScolaireLibelle =
      (anneeSnap.data()?.['libelle'] as string | undefined) ?? '—';

    return this.pdfService.genererBulletinPdf({
      eleveNom: `${eleve['prenom']} ${eleve['nom']}`,
      eleveMatricule: eleve['matricule'] as string,
      classeNom,
      trimestreNom: trimestre['nom'] as string,
      anneeScolaireLibelle,
      matieres: snapshot.matieresDetail,
      moyenneGenerale: snapshot.moyenneGenerale,
      tauxPresence: snapshot.tauxPresence,
      rang: snapshot.rang,
      appreciation: snapshot.appreciation,
    });
  }

  async calculerDonnees(
    ecoleId: string,
    eleveId: string,
    trimestreId: string,
  ): Promise<DonneesBulletin> {
    const notesSnap = await ecoleCollection(this.db, ecoleId, 'notes')
      .where('eleveId', '==', eleveId)
      .where('trimestreId', '==', trimestreId)
      .get();

    const parMatiere = new Map<
      string,
      { totalPondere: number; totalCoefficient: number }
    >();
    for (const doc of notesSnap.docs) {
      const note = doc.data();
      const matiereId = note['matiereId'] as string;
      const valeur = note['valeur'] as number;
      const bareme = note['bareme'] as number;
      const coefficient = note['coefficient'] as number;
      const noteSur20 = (valeur / bareme) * 20;

      const cumul = parMatiere.get(matiereId) ?? {
        totalPondere: 0,
        totalCoefficient: 0,
      };
      cumul.totalPondere += noteSur20 * coefficient;
      cumul.totalCoefficient += coefficient;
      parMatiere.set(matiereId, cumul);
    }

    const matieres: MatiereBulletin[] = [];
    for (const [matiereId, cumul] of parMatiere.entries()) {
      if (cumul.totalCoefficient <= 0) continue;
      const matiereSnap = await ecoleCollection(this.db, ecoleId, 'matieres')
        .doc(matiereId)
        .get();
      const nom =
        (matiereSnap.data()?.['nom'] as string | undefined) ?? 'Matière';
      const coefficientDefaut =
        (matiereSnap.data()?.['coefficientDefaut'] as number | undefined) ?? 1;
      matieres.push({
        nom,
        moyenne:
          Math.round((cumul.totalPondere / cumul.totalCoefficient) * 100) / 100,
        coefficient: coefficientDefaut,
      });
    }

    const totalPondereGeneral = matieres.reduce(
      (acc, m) => acc + (m.moyenne ?? 0) * m.coefficient,
      0,
    );
    const totalCoefficientGeneral = matieres.reduce(
      (acc, m) => acc + m.coefficient,
      0,
    );
    const moyenneGenerale =
      totalCoefficientGeneral > 0
        ? Math.round((totalPondereGeneral / totalCoefficientGeneral) * 100) /
          100
        : null;

    return { matieres, moyenneGenerale };
  }

  async calculerTauxPresence(
    ecoleId: string,
    eleveId: string,
    trimestreId: string,
  ): Promise<number | null> {
    const snap = await ecoleCollection(this.db, ecoleId, 'presences')
      .where('eleveId', '==', eleveId)
      .where('trimestreId', '==', trimestreId)
      .get();

    if (snap.empty) return null;

    const presents = snap.docs.filter(
      (doc) => doc.data()['statut'] === 'present',
    ).length;
    return Math.round((presents / snap.size) * 1000) / 10;
  }

  private async calculerRang(
    ecoleId: string,
    classeId: string,
    eleveId: string,
    trimestreId: string,
  ): Promise<number | null> {
    const elevesSnap = await ecoleCollection(this.db, ecoleId, 'eleves')
      .where('classeId', '==', classeId)
      .where('statut', '==', 'actif')
      .get();

    const moyennes = await Promise.all(
      elevesSnap.docs.map(async (doc) => {
        const donnees = await this.calculerDonnees(
          ecoleId,
          doc.id,
          trimestreId,
        );
        return { eleveId: doc.id, moyenne: donnees.moyenneGenerale };
      }),
    );

    const moyenneEleve =
      moyennes.find((m) => m.eleveId === eleveId)?.moyenne ?? null;
    if (moyenneEleve === null) return null;

    const meilleures = moyennes
      .map((m) => m.moyenne)
      .filter((m): m is number => m !== null)
      .sort((a, b) => b - a);

    return meilleures.indexOf(moyenneEleve) + 1;
  }

  private appreciation(moyenne: number | null): string {
    if (moyenne === null) return 'Aucune note';
    if (moyenne >= 16) return 'Excellent';
    if (moyenne >= 14) return 'Très bien';
    if (moyenne >= 12) return 'Bien';
    if (moyenne >= 10) return 'Passable';
    return 'Insuffisant';
  }
}
