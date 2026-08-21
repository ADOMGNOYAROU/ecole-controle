import { BadRequestException, Inject, Injectable } from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue } from 'firebase-admin/firestore';
import { ecoleCollection, getDocOrThrow } from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { TrimestreDto } from './dto/trimestre.dto';

@Injectable()
export class TrimestresService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  async lister(ecoleId: string) {
    const snap = await ecoleCollection(this.db, ecoleId, 'trimestres').get();
    return snap.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
  }

  async creer(ecoleId: string, dto: TrimestreDto) {
    this.verifierPeriode(dto);
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'anneesScolaires').doc(
        dto.anneeScolaireId,
      ),
      'Année scolaire introuvable.',
    );

    const ref = await ecoleCollection(this.db, ecoleId, 'trimestres').add({
      anneeScolaireId: dto.anneeScolaireId,
      nom: dto.nom,
      ordre: dto.ordre,
      dateDebut: new Date(dto.dateDebut),
      dateFin: new Date(dto.dateFin),
      createdAt: FieldValue.serverTimestamp(),
    });
    return { id: ref.id };
  }

  async modifier(
    ecoleId: string,
    id: string,
    dto: TrimestreDto,
  ): Promise<void> {
    this.verifierPeriode(dto);
    const ref = ecoleCollection(this.db, ecoleId, 'trimestres').doc(id);
    await getDocOrThrow(ref, 'Trimestre introuvable.');
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'anneesScolaires').doc(
        dto.anneeScolaireId,
      ),
      'Année scolaire introuvable.',
    );

    await ref.update({
      anneeScolaireId: dto.anneeScolaireId,
      nom: dto.nom,
      ordre: dto.ordre,
      dateDebut: new Date(dto.dateDebut),
      dateFin: new Date(dto.dateFin),
    });
  }

  async supprimer(ecoleId: string, id: string): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'trimestres').doc(id);
    await getDocOrThrow(ref, 'Trimestre introuvable.');
    await ref.delete();
  }

  private verifierPeriode(dto: TrimestreDto): void {
    if (new Date(dto.dateFin) <= new Date(dto.dateDebut)) {
      throw new BadRequestException(
        'La date de fin doit être postérieure à la date de début.',
      );
    }
  }
}
