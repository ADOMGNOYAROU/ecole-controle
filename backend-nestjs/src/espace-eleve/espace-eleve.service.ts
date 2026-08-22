import { Inject, Injectable, NotFoundException } from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { BulletinsService } from '../bulletins/bulletins.service';
import {
  ecoleCollection,
  trouverEleveIdParUid,
  trouverTrimestreActuelId,
} from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { PaiementsService } from '../paiements/paiements.service';

@Injectable()
export class EspaceEleveService {
  constructor(
    @Inject(FIRESTORE) private readonly db: Firestore,
    private readonly bulletinsService: BulletinsService,
    private readonly paiementsService: PaiementsService,
  ) {}

  async notes(ecoleId: string, uid: string, trimestreIdDemande?: string) {
    const eleveId = await this.monEleveId(ecoleId, uid);
    const eleveSnap = await ecoleCollection(this.db, ecoleId, 'eleves')
      .doc(eleveId)
      .get();
    const classeId = eleveSnap.data()!['classeId'] as string | null;

    let trimestres: { id: string; nom: string; ordre: number }[] = [];
    if (classeId) {
      const classeSnap = await ecoleCollection(this.db, ecoleId, 'classes')
        .doc(classeId)
        .get();
      const anneeScolaireId = classeSnap.data()?.['anneeScolaireId'] as
        string | undefined;
      if (anneeScolaireId) {
        const trimestresSnap = await ecoleCollection(
          this.db,
          ecoleId,
          'trimestres',
        )
          .where('anneeScolaireId', '==', anneeScolaireId)
          .get();
        trimestres = trimestresSnap.docs
          .map((doc) => ({
            id: doc.id,
            nom: doc.data()['nom'] as string,
            ordre: doc.data()['ordre'] as number,
          }))
          .sort((a, b) => b.ordre - a.ordre);
      }
    }

    const trimestreActuelId = await trouverTrimestreActuelId(this.db, ecoleId);
    const trimestre =
      trimestres.find((t) => t.id === trimestreIdDemande) ??
      trimestres.find((t) => t.id === trimestreActuelId) ??
      trimestres[0] ??
      null;

    const donnees = trimestre
      ? await this.bulletinsService.calculerDonnees(
          ecoleId,
          eleveId,
          trimestre.id,
        )
      : null;

    return { trimestres, trimestre, donnees };
  }

  async presences(ecoleId: string, uid: string) {
    const eleveId = await this.monEleveId(ecoleId, uid);
    const snap = await ecoleCollection(this.db, ecoleId, 'presences')
      .where('eleveId', '==', eleveId)
      .get();
    return snap.docs
      .map(
        (doc) =>
          ({ id: doc.id, ...doc.data() }) as { id: string; date: string },
      )
      .sort((a, b) => b.date.localeCompare(a.date));
  }

  async paiements(ecoleId: string, uid: string) {
    const eleveId = await this.monEleveId(ecoleId, uid);
    const paiements = (await this.paiementsService.lister(ecoleId, {
      eleveId,
    })) as unknown as {
      dateEcheance: string;
    }[];
    return paiements.sort((a, b) =>
      b.dateEcheance.localeCompare(a.dateEcheance),
    );
  }

  private async monEleveId(ecoleId: string, uid: string): Promise<string> {
    const eleveId = await trouverEleveIdParUid(this.db, ecoleId, uid);
    if (!eleveId) {
      throw new NotFoundException("Aucune fiche élève n'est liée à ce compte.");
    }
    return eleveId;
  }
}
