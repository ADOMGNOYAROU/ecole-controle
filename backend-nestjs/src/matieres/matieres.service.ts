import { ConflictException, Inject, Injectable } from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue } from 'firebase-admin/firestore';
import { ecoleCollection, getDocOrThrow } from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { MatiereDto } from './dto/matiere.dto';

@Injectable()
export class MatieresService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  async lister(ecoleId: string) {
    const snap = await ecoleCollection(this.db, ecoleId, 'matieres').get();
    return snap.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
  }

  async trouver(ecoleId: string, id: string) {
    const snap = await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'matieres').doc(id),
      'Matière introuvable.',
    );
    return { id: snap.id, ...snap.data() };
  }

  async creer(ecoleId: string, dto: MatiereDto) {
    await this.verifierCodeUnique(ecoleId, dto.code);
    const ref = await ecoleCollection(this.db, ecoleId, 'matieres').add({
      nom: dto.nom,
      code: dto.code,
      coefficientDefaut: dto.coefficientDefaut,
      createdAt: FieldValue.serverTimestamp(),
    });
    return { id: ref.id };
  }

  async modifier(ecoleId: string, id: string, dto: MatiereDto): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'matieres').doc(id);
    await getDocOrThrow(ref, 'Matière introuvable.');
    await this.verifierCodeUnique(ecoleId, dto.code, id);
    await ref.update({
      nom: dto.nom,
      code: dto.code,
      coefficientDefaut: dto.coefficientDefaut,
    });
  }

  async supprimer(ecoleId: string, id: string): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'matieres').doc(id);
    await getDocOrThrow(ref, 'Matière introuvable.');
    await ref.delete();
  }

  private async verifierCodeUnique(
    ecoleId: string,
    code: string,
    ignorerId?: string,
  ): Promise<void> {
    const snap = await ecoleCollection(this.db, ecoleId, 'matieres')
      .where('code', '==', code)
      .get();
    if (snap.docs.some((doc) => doc.id !== ignorerId)) {
      throw new ConflictException('Ce code est déjà utilisé.');
    }
  }
}
