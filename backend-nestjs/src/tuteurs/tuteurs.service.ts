import { Inject, Injectable } from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue } from 'firebase-admin/firestore';
import { ecoleCollection, getDocOrThrow } from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { TuteurDto } from './dto/tuteur.dto';

@Injectable()
export class TuteursService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  async lister(ecoleId: string) {
    const snap = await ecoleCollection(this.db, ecoleId, 'tuteurs').get();
    return snap.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
  }

  async trouver(ecoleId: string, id: string) {
    const snap = await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'tuteurs').doc(id),
      'Tuteur introuvable.',
    );
    return { id: snap.id, ...snap.data() };
  }

  async creer(ecoleId: string, dto: TuteurDto) {
    const eleves = dto.eleves ?? [];
    await this.verifierEleves(ecoleId, eleves);

    const ref = ecoleCollection(this.db, ecoleId, 'tuteurs').doc();
    const batch = this.db.batch();

    batch.set(ref, {
      nom: dto.nom,
      prenom: dto.prenom,
      telephone: dto.telephone,
      email: dto.email ?? null,
      profession: dto.profession ?? null,
      adresse: dto.adresse ?? null,
      eleves: this.versEleves(eleves),
      userId: null,
      createdAt: FieldValue.serverTimestamp(),
    });

    for (const { id: eleveId } of eleves) {
      batch.update(ecoleCollection(this.db, ecoleId, 'eleves').doc(eleveId), {
        tuteurIds: FieldValue.arrayUnion(ref.id),
      });
    }

    await batch.commit();
    return { id: ref.id };
  }

  async modifier(ecoleId: string, id: string, dto: TuteurDto): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'tuteurs').doc(id);
    const snap = await getDocOrThrow(ref, 'Tuteur introuvable.');

    const nouveauxEleves = dto.eleves ?? [];
    await this.verifierEleves(ecoleId, nouveauxEleves);

    const anciensEleveIds = new Set(
      ((snap.data()?.['eleves'] as { id: string }[] | undefined) ?? []).map(
        (e) => e.id,
      ),
    );
    const nouveauxEleveIds = new Set(nouveauxEleves.map((e) => e.id));

    const batch = this.db.batch();

    batch.update(ref, {
      nom: dto.nom,
      prenom: dto.prenom,
      telephone: dto.telephone,
      email: dto.email ?? null,
      profession: dto.profession ?? null,
      adresse: dto.adresse ?? null,
      eleves: this.versEleves(nouveauxEleves),
    });

    for (const eleveId of nouveauxEleveIds) {
      if (!anciensEleveIds.has(eleveId)) {
        batch.update(ecoleCollection(this.db, ecoleId, 'eleves').doc(eleveId), {
          tuteurIds: FieldValue.arrayUnion(id),
        });
      }
    }
    for (const eleveId of anciensEleveIds) {
      if (!nouveauxEleveIds.has(eleveId)) {
        batch.update(ecoleCollection(this.db, ecoleId, 'eleves').doc(eleveId), {
          tuteurIds: FieldValue.arrayRemove(id),
        });
      }
    }

    await batch.commit();
  }

  async supprimer(ecoleId: string, id: string): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'tuteurs').doc(id);
    const snap = await getDocOrThrow(ref, 'Tuteur introuvable.');
    const eleves =
      (snap.data()?.['eleves'] as { id: string }[] | undefined) ?? [];

    const batch = this.db.batch();
    batch.delete(ref);
    for (const { id: eleveId } of eleves) {
      batch.update(ecoleCollection(this.db, ecoleId, 'eleves').doc(eleveId), {
        tuteurIds: FieldValue.arrayRemove(id),
      });
    }
    await batch.commit();
  }

  private versEleves(
    eleves: { id: string; lienParente: string }[],
  ): { id: string; lienParente: string }[] {
    return eleves.map(({ id, lienParente }) => ({ id, lienParente }));
  }

  private async verifierEleves(
    ecoleId: string,
    eleves: { id: string }[],
  ): Promise<void> {
    for (const { id } of eleves) {
      await getDocOrThrow(
        ecoleCollection(this.db, ecoleId, 'eleves').doc(id),
        'Élève introuvable.',
      );
    }
  }
}
