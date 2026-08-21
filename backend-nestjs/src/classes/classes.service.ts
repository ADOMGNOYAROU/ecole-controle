import { Inject, Injectable } from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue } from 'firebase-admin/firestore';
import { ecoleCollection, getDocOrThrow } from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { ClasseDto } from './dto/classe.dto';

@Injectable()
export class ClassesService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  async lister(ecoleId: string) {
    const snap = await ecoleCollection(this.db, ecoleId, 'classes').get();
    return snap.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
  }

  async trouver(ecoleId: string, id: string) {
    const snap = await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'classes').doc(id),
      'Classe introuvable.',
    );
    return { id: snap.id, ...snap.data() };
  }

  async creer(ecoleId: string, dto: ClasseDto) {
    await this.verifierReferences(ecoleId, dto);
    const ref = await ecoleCollection(this.db, ecoleId, 'classes').add({
      nom: dto.nom,
      niveau: dto.niveau ?? null,
      anneeScolaireId: dto.anneeScolaireId,
      enseignantPrincipalId: dto.enseignantPrincipalId ?? null,
      capacite: dto.capacite ?? null,
      createdAt: FieldValue.serverTimestamp(),
    });
    return { id: ref.id };
  }

  async modifier(ecoleId: string, id: string, dto: ClasseDto): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'classes').doc(id);
    await getDocOrThrow(ref, 'Classe introuvable.');
    await this.verifierReferences(ecoleId, dto);
    await ref.update({
      nom: dto.nom,
      niveau: dto.niveau ?? null,
      anneeScolaireId: dto.anneeScolaireId,
      enseignantPrincipalId: dto.enseignantPrincipalId ?? null,
      capacite: dto.capacite ?? null,
    });
  }

  async supprimer(ecoleId: string, id: string): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'classes').doc(id);
    await getDocOrThrow(ref, 'Classe introuvable.');
    await ref.delete();
  }

  private async verifierReferences(
    ecoleId: string,
    dto: ClasseDto,
  ): Promise<void> {
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'anneesScolaires').doc(
        dto.anneeScolaireId,
      ),
      'Année scolaire introuvable.',
    );
    if (dto.enseignantPrincipalId) {
      await getDocOrThrow(
        ecoleCollection(this.db, ecoleId, 'enseignants').doc(
          dto.enseignantPrincipalId,
        ),
        'Enseignant introuvable.',
      );
    }
  }
}
