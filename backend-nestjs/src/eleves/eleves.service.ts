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
import { EleveDto } from './dto/eleve.dto';

@Injectable()
export class ElevesService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  async lister(ecoleId: string) {
    const snap = await ecoleCollection(this.db, ecoleId, 'eleves').get();
    return snap.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
  }

  async trouver(ecoleId: string, id: string) {
    const snap = await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'eleves').doc(id),
      'Élève introuvable.',
    );
    return { id: snap.id, ...snap.data() };
  }

  async creer(ecoleId: string, dto: EleveDto) {
    this.verifierDateNaissance(dto.dateNaissance);
    await this.verifierMatriculeUnique(ecoleId, dto.matricule);
    if (dto.classeId) {
      await getDocOrThrow(
        ecoleCollection(this.db, ecoleId, 'classes').doc(dto.classeId),
        'Classe introuvable.',
      );
    }

    const ref = await ecoleCollection(this.db, ecoleId, 'eleves').add({
      ...this.versDocument(dto),
      tuteurIds: [],
      userId: null,
      createdAt: FieldValue.serverTimestamp(),
    });
    return { id: ref.id };
  }

  async modifier(ecoleId: string, id: string, dto: EleveDto): Promise<void> {
    this.verifierDateNaissance(dto.dateNaissance);
    const ref = ecoleCollection(this.db, ecoleId, 'eleves').doc(id);
    await getDocOrThrow(ref, 'Élève introuvable.');
    await this.verifierMatriculeUnique(ecoleId, dto.matricule, id);
    if (dto.classeId) {
      await getDocOrThrow(
        ecoleCollection(this.db, ecoleId, 'classes').doc(dto.classeId),
        'Classe introuvable.',
      );
    }

    await ref.update(this.versDocument(dto));
  }

  async supprimer(ecoleId: string, id: string): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'eleves').doc(id);
    await getDocOrThrow(ref, 'Élève introuvable.');
    await ref.delete();
  }

  async pourRapport(ecoleId: string): Promise<string[][]> {
    const elevesSnap = await ecoleCollection(this.db, ecoleId, 'eleves')
      .orderBy('nom')
      .get();
    const classesCache = new Map<string, string>();

    const lignes: string[][] = [];
    for (const doc of elevesSnap.docs) {
      const eleve = doc.data();
      const classeId = eleve['classeId'] as string | null;
      let classeNom = 'Sans classe';
      if (classeId) {
        if (!classesCache.has(classeId)) {
          const classeSnap = await ecoleCollection(this.db, ecoleId, 'classes')
            .doc(classeId)
            .get();
          classesCache.set(
            classeId,
            (classeSnap.data()?.['nom'] as string | undefined) ?? '—',
          );
        }
        classeNom = classesCache.get(classeId)!;
      }

      lignes.push([
        eleve['matricule'] as string,
        `${eleve['prenom']} ${eleve['nom']}`,
        classeNom,
        eleve['sexe'] === 'M' ? 'Masculin' : 'Féminin',
        eleve['statut'] as string,
      ]);
    }

    return lignes;
  }

  private versDocument(dto: EleveDto) {
    return {
      matricule: dto.matricule,
      nom: dto.nom,
      prenom: dto.prenom,
      sexe: dto.sexe,
      dateNaissance: new Date(dto.dateNaissance),
      lieuNaissance: dto.lieuNaissance ?? null,
      adresse: dto.adresse ?? null,
      telephone: dto.telephone ?? null,
      email: dto.email ?? null,
      classeId: dto.classeId ?? null,
      statut: dto.statut,
      dateInscription: new Date(dto.dateInscription),
      contactUrgenceNom: dto.contactUrgenceNom ?? null,
      contactUrgenceTelephone: dto.contactUrgenceTelephone ?? null,
    };
  }

  private verifierDateNaissance(dateNaissance: string): void {
    if (new Date(dateNaissance) >= new Date()) {
      throw new BadRequestException(
        'La date de naissance doit être antérieure à aujourd’hui.',
      );
    }
  }

  private async verifierMatriculeUnique(
    ecoleId: string,
    matricule: string,
    ignorerId?: string,
  ): Promise<void> {
    const snap = await ecoleCollection(this.db, ecoleId, 'eleves')
      .where('matricule', '==', matricule)
      .get();
    if (snap.docs.some((doc) => doc.id !== ignorerId)) {
      throw new ConflictException('Ce matricule est déjà utilisé.');
    }
  }
}
