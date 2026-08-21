import {
  BadRequestException,
  ConflictException,
  Inject,
  Injectable,
} from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue } from 'firebase-admin/firestore';
import { ecoleCollection, getDocOrThrow } from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { AnneeScolaireDto } from './dto/annee-scolaire.dto';

@Injectable()
export class AnneesScolairesService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  async lister(ecoleId: string) {
    const snap = await ecoleCollection(
      this.db,
      ecoleId,
      'anneesScolaires',
    ).get();
    return snap.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
  }

  async creer(ecoleId: string, dto: AnneeScolaireDto) {
    this.verifierPeriode(dto);
    await this.verifierLibelleUnique(ecoleId, dto.libelle);

    const ref = await ecoleCollection(this.db, ecoleId, 'anneesScolaires').add({
      libelle: dto.libelle,
      dateDebut: new Date(dto.dateDebut),
      dateFin: new Date(dto.dateFin),
      active: dto.active ?? false,
      createdAt: FieldValue.serverTimestamp(),
    });
    return { id: ref.id };
  }

  async modifier(
    ecoleId: string,
    id: string,
    dto: AnneeScolaireDto,
  ): Promise<void> {
    this.verifierPeriode(dto);
    const ref = ecoleCollection(this.db, ecoleId, 'anneesScolaires').doc(id);
    await getDocOrThrow(ref, 'Année scolaire introuvable.');
    await this.verifierLibelleUnique(ecoleId, dto.libelle, id);

    await ref.update({
      libelle: dto.libelle,
      dateDebut: new Date(dto.dateDebut),
      dateFin: new Date(dto.dateFin),
      active: dto.active ?? false,
    });
  }

  async supprimer(ecoleId: string, id: string): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'anneesScolaires').doc(id);
    await getDocOrThrow(ref, 'Année scolaire introuvable.');
    await ref.delete();
  }

  private verifierPeriode(dto: AnneeScolaireDto): void {
    if (new Date(dto.dateFin) <= new Date(dto.dateDebut)) {
      throw new BadRequestException(
        'La date de fin doit être postérieure à la date de début.',
      );
    }
  }

  private async verifierLibelleUnique(
    ecoleId: string,
    libelle: string,
    ignorerId?: string,
  ): Promise<void> {
    const snap = await ecoleCollection(this.db, ecoleId, 'anneesScolaires')
      .where('libelle', '==', libelle)
      .get();
    if (snap.docs.some((doc) => doc.id !== ignorerId)) {
      throw new ConflictException('Ce libellé est déjà utilisé.');
    }
  }
}
