import { BadRequestException, Inject, Injectable } from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue } from 'firebase-admin/firestore';
import { ecoleCollection, getDocOrThrow } from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { CreneauHoraireDto } from './dto/creneau-horaire.dto';

@Injectable()
export class CreneauxHorairesService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  async lister(ecoleId: string, classeId?: string) {
    const base = ecoleCollection(this.db, ecoleId, 'creneauxHoraires');
    const snap = await (
      classeId ? base.where('classeId', '==', classeId) : base
    ).get();

    return snap.docs
      .map(
        (doc) =>
          ({ id: doc.id, ...doc.data() }) as {
            id: string;
            jourSemaine: number;
            heureDebut: string;
          },
      )
      .sort(
        (a, b) =>
          a.jourSemaine - b.jourSemaine ||
          a.heureDebut.localeCompare(b.heureDebut),
      );
  }

  async creer(ecoleId: string, dto: CreneauHoraireDto) {
    this.verifierPlageHoraire(dto);
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'classes').doc(dto.classeId),
      'Classe introuvable.',
    );
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'matieres').doc(dto.matiereId),
      'Matière introuvable.',
    );
    if (dto.enseignantId) {
      await getDocOrThrow(
        ecoleCollection(this.db, ecoleId, 'enseignants').doc(dto.enseignantId),
        'Enseignant introuvable.',
      );
    }

    const ref = await ecoleCollection(this.db, ecoleId, 'creneauxHoraires').add(
      {
        classeId: dto.classeId,
        matiereId: dto.matiereId,
        enseignantId: dto.enseignantId ?? null,
        jourSemaine: dto.jourSemaine,
        heureDebut: dto.heureDebut,
        heureFin: dto.heureFin,
        salle: dto.salle ?? null,
        createdAt: FieldValue.serverTimestamp(),
      },
    );
    return { id: ref.id };
  }

  async supprimer(ecoleId: string, id: string): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'creneauxHoraires').doc(id);
    await getDocOrThrow(ref, 'Créneau introuvable.');
    await ref.delete();
  }

  private verifierPlageHoraire(dto: CreneauHoraireDto): void {
    if (dto.heureFin <= dto.heureDebut) {
      throw new BadRequestException(
        "L'heure de fin doit être postérieure à l'heure de début.",
      );
    }
  }
}
