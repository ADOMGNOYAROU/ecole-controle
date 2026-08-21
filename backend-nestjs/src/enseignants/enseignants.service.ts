import { Inject, Injectable } from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue } from 'firebase-admin/firestore';
import { ecoleCollection, getDocOrThrow } from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { EnseignantDto } from './dto/enseignant.dto';

@Injectable()
export class EnseignantsService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  async lister(ecoleId: string) {
    const snap = await ecoleCollection(this.db, ecoleId, 'enseignants').get();
    return snap.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
  }

  async trouver(ecoleId: string, id: string) {
    const snap = await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'enseignants').doc(id),
      'Enseignant introuvable.',
    );
    return { id: snap.id, ...snap.data() };
  }

  async creer(ecoleId: string, dto: EnseignantDto) {
    const ref = await ecoleCollection(this.db, ecoleId, 'enseignants').add({
      nom: dto.nom,
      prenom: dto.prenom,
      telephone: dto.telephone ?? null,
      email: dto.email ?? null,
      specialite: dto.specialite ?? null,
      dateEmbauche: dto.dateEmbauche ? new Date(dto.dateEmbauche) : null,
      matiereIds: dto.matiereIds ?? [],
      classeIds: dto.classeIds ?? [],
      userId: null,
      createdAt: FieldValue.serverTimestamp(),
    });
    return { id: ref.id };
  }

  async modifier(
    ecoleId: string,
    id: string,
    dto: EnseignantDto,
  ): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'enseignants').doc(id);
    await getDocOrThrow(ref, 'Enseignant introuvable.');
    await ref.update({
      nom: dto.nom,
      prenom: dto.prenom,
      telephone: dto.telephone ?? null,
      email: dto.email ?? null,
      specialite: dto.specialite ?? null,
      dateEmbauche: dto.dateEmbauche ? new Date(dto.dateEmbauche) : null,
      matiereIds: dto.matiereIds ?? [],
      classeIds: dto.classeIds ?? [],
    });
  }

  async supprimer(ecoleId: string, id: string): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'enseignants').doc(id);
    await getDocOrThrow(ref, 'Enseignant introuvable.');
    await ref.delete();
  }
}
