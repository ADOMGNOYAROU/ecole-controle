import {
  ForbiddenException,
  Inject,
  Injectable,
  NotFoundException,
} from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { BulletinsService } from '../bulletins/bulletins.service';
import {
  ecoleCollection,
  trouverTrimestreActuelId,
  trouverTuteurIdParUid,
} from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { PaiementsService } from '../paiements/paiements.service';

@Injectable()
export class EspaceParentService {
  constructor(
    @Inject(FIRESTORE) private readonly db: Firestore,
    private readonly bulletinsService: BulletinsService,
    private readonly paiementsService: PaiementsService,
  ) {}

  async enfants(ecoleId: string, uid: string) {
    const tuteurId = await this.monTuteurId(ecoleId, uid);
    const tuteurSnap = await ecoleCollection(this.db, ecoleId, 'tuteurs')
      .doc(tuteurId)
      .get();
    const eleves =
      (tuteurSnap.data()?.['eleves'] as { id: string }[] | undefined) ?? [];

    const enfants: Record<string, unknown>[] = [];
    for (const { id } of eleves) {
      const eleveSnap = await ecoleCollection(this.db, ecoleId, 'eleves')
        .doc(id)
        .get();
      if (!eleveSnap.exists) continue;
      const eleve = eleveSnap.data()!;
      let classeNom: string | null = null;
      if (eleve['classeId']) {
        const classeSnap = await ecoleCollection(this.db, ecoleId, 'classes')
          .doc(eleve['classeId'] as string)
          .get();
        classeNom = (classeSnap.data()?.['nom'] as string | undefined) ?? null;
      }
      enfants.push({ id: eleveSnap.id, ...eleve, classeNom });
    }
    return enfants;
  }

  async enfant(ecoleId: string, uid: string, eleveId: string) {
    const tuteurId = await this.monTuteurId(ecoleId, uid);
    const tuteurSnap = await ecoleCollection(this.db, ecoleId, 'tuteurs')
      .doc(tuteurId)
      .get();
    const eleves =
      (tuteurSnap.data()?.['eleves'] as { id: string }[] | undefined) ?? [];
    if (!eleves.some((e) => e.id === eleveId)) {
      throw new ForbiddenException('Accès non autorisé.');
    }

    const eleveSnap = await ecoleCollection(this.db, ecoleId, 'eleves')
      .doc(eleveId)
      .get();
    if (!eleveSnap.exists) {
      throw new NotFoundException('Élève introuvable.');
    }

    const trimestreId = await trouverTrimestreActuelId(this.db, ecoleId);
    const donnees = trimestreId
      ? await this.bulletinsService.calculerDonnees(
          ecoleId,
          eleveId,
          trimestreId,
        )
      : null;
    const tauxPresence = trimestreId
      ? await this.bulletinsService.calculerTauxPresence(
          ecoleId,
          eleveId,
          trimestreId,
        )
      : null;

    const presencesSnap = await ecoleCollection(this.db, ecoleId, 'presences')
      .where('eleveId', '==', eleveId)
      .get();
    const presences = presencesSnap.docs
      .map(
        (doc) =>
          ({ id: doc.id, ...doc.data() }) as { id: string; date: string },
      )
      .sort((a, b) => b.date.localeCompare(a.date))
      .slice(0, 15);

    const paiements = (
      (await this.paiementsService.lister(ecoleId, { eleveId })) as unknown as {
        dateEcheance: string;
      }[]
    ).sort((a, b) => b.dateEcheance.localeCompare(a.dateEcheance));

    return {
      eleve: { id: eleveSnap.id, ...eleveSnap.data() },
      trimestreId,
      donnees,
      tauxPresence,
      presences,
      paiements,
    };
  }

  private async monTuteurId(ecoleId: string, uid: string): Promise<string> {
    const tuteurId = await trouverTuteurIdParUid(this.db, ecoleId, uid);
    if (!tuteurId) {
      throw new NotFoundException(
        "Aucune fiche tuteur n'est liée à ce compte.",
      );
    }
    return tuteurId;
  }
}
