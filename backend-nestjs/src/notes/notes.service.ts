import {
  BadRequestException,
  ForbiddenException,
  Inject,
  Injectable,
} from '@nestjs/common';
import type { Firestore, Query } from 'firebase-admin/firestore';
import {
  ecoleCollection,
  getDocOrThrow,
  trouverEnseignantIdParUid,
} from '../common/firestore.helpers';
import type { AuthenticatedUser } from '../auth/types';
import { FIRESTORE } from '../firebase/firebase.constants';
import { NoteBulkDto } from './dto/note-bulk.dto';
import { NoteDto } from './dto/note.dto';

export interface NoteFiltres {
  classeId?: string;
  matiereId?: string;
  trimestreId?: string;
}

@Injectable()
export class NotesService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  async lister(ecoleId: string, user: AuthenticatedUser, filtres: NoteFiltres) {
    let query: Query = ecoleCollection(this.db, ecoleId, 'notes');

    if (user.role === 'enseignant') {
      const enseignantId = await trouverEnseignantIdParUid(
        this.db,
        ecoleId,
        user.uid,
      );
      if (!enseignantId) return [];
      query = query.where('enseignantId', '==', enseignantId);
    }
    if (filtres.classeId)
      query = query.where('classeId', '==', filtres.classeId);
    if (filtres.matiereId)
      query = query.where('matiereId', '==', filtres.matiereId);
    if (filtres.trimestreId)
      query = query.where('trimestreId', '==', filtres.trimestreId);

    const snap = await query.get();
    return snap.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
  }

  async elevesPourSaisie(ecoleId: string, classeId: string) {
    const snap = await ecoleCollection(this.db, ecoleId, 'eleves')
      .where('classeId', '==', classeId)
      .where('statut', '==', 'actif')
      .get();

    return snap.docs
      .map((doc) => {
        const data = doc.data();
        return {
          id: doc.id,
          nom: data['nom'] as string,
          prenom: data['prenom'] as string,
          matricule: data['matricule'] as string,
        };
      })
      .sort((a, b) => a.nom.localeCompare(b.nom));
  }

  async creer(ecoleId: string, user: AuthenticatedUser, dto: NoteDto) {
    this.verifierValeur(dto.valeur, dto.bareme);
    this.verifierDate(dto.dateEvaluation);
    await this.verifierReferences(
      ecoleId,
      dto.eleveId,
      dto.matiereId,
      dto.classeId,
      dto.trimestreId,
    );

    const enseignantId = await this.resoudreEnseignantId(
      ecoleId,
      user,
      dto.enseignantId,
    );

    const ref = await ecoleCollection(this.db, ecoleId, 'notes').add({
      eleveId: dto.eleveId,
      matiereId: dto.matiereId,
      classeId: dto.classeId,
      trimestreId: dto.trimestreId,
      enseignantId,
      type: dto.type,
      valeur: dto.valeur,
      bareme: dto.bareme,
      coefficient: dto.coefficient,
      dateEvaluation: dto.dateEvaluation,
      commentaire: dto.commentaire ?? null,
    });
    return { id: ref.id };
  }

  async bulkCreer(ecoleId: string, user: AuthenticatedUser, dto: NoteBulkDto) {
    this.verifierDate(dto.dateEvaluation);
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'classes').doc(dto.classeId),
      'Classe introuvable.',
    );
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'matieres').doc(dto.matiereId),
      'Matière introuvable.',
    );
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'trimestres').doc(dto.trimestreId),
      'Trimestre introuvable.',
    );

    const enseignantId = await this.resoudreEnseignantId(
      ecoleId,
      user,
      dto.enseignantId,
    );
    const batch = this.db.batch();

    for (const entree of dto.notes) {
      this.verifierValeur(entree.valeur, dto.bareme);
      const id = [
        dto.classeId,
        dto.matiereId,
        dto.trimestreId,
        dto.type,
        dto.dateEvaluation,
        entree.eleveId,
      ].join('_');
      batch.set(ecoleCollection(this.db, ecoleId, 'notes').doc(id), {
        eleveId: entree.eleveId,
        matiereId: dto.matiereId,
        classeId: dto.classeId,
        trimestreId: dto.trimestreId,
        enseignantId,
        type: dto.type,
        valeur: entree.valeur,
        bareme: dto.bareme,
        coefficient: dto.coefficient,
        dateEvaluation: dto.dateEvaluation,
        commentaire: null,
      });
    }

    await batch.commit();
  }

  async modifier(
    ecoleId: string,
    user: AuthenticatedUser,
    id: string,
    dto: NoteDto,
  ): Promise<void> {
    this.verifierValeur(dto.valeur, dto.bareme);
    this.verifierDate(dto.dateEvaluation);
    const ref = ecoleCollection(this.db, ecoleId, 'notes').doc(id);
    const snap = await getDocOrThrow(ref, 'Note introuvable.');
    await this.verifierProprietaire(
      ecoleId,
      user,
      snap.data()!['enseignantId'] as string | null,
    );
    await this.verifierReferences(
      ecoleId,
      dto.eleveId,
      dto.matiereId,
      dto.classeId,
      dto.trimestreId,
    );

    await ref.update({
      eleveId: dto.eleveId,
      matiereId: dto.matiereId,
      classeId: dto.classeId,
      trimestreId: dto.trimestreId,
      type: dto.type,
      valeur: dto.valeur,
      bareme: dto.bareme,
      coefficient: dto.coefficient,
      dateEvaluation: dto.dateEvaluation,
      commentaire: dto.commentaire ?? null,
    });
  }

  async supprimer(
    ecoleId: string,
    user: AuthenticatedUser,
    id: string,
  ): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'notes').doc(id);
    const snap = await getDocOrThrow(ref, 'Note introuvable.');
    await this.verifierProprietaire(
      ecoleId,
      user,
      snap.data()!['enseignantId'] as string | null,
    );
    await ref.delete();
  }

  private async resoudreEnseignantId(
    ecoleId: string,
    user: AuthenticatedUser,
    enseignantIdFourni?: string,
  ): Promise<string | null> {
    if (user.role === 'enseignant') {
      return trouverEnseignantIdParUid(this.db, ecoleId, user.uid);
    }
    return enseignantIdFourni ?? null;
  }

  private async verifierProprietaire(
    ecoleId: string,
    user: AuthenticatedUser,
    enseignantIdDeLaNote: string | null,
  ): Promise<void> {
    if (user.role === 'admin') return;

    if (user.role === 'enseignant') {
      const monEnseignantId = await trouverEnseignantIdParUid(
        this.db,
        ecoleId,
        user.uid,
      );
      if (monEnseignantId && monEnseignantId === enseignantIdDeLaNote) return;
    }

    throw new ForbiddenException('Accès non autorisé.');
  }

  private verifierValeur(valeur: number, bareme: number): void {
    if (valeur > bareme) {
      throw new BadRequestException(
        'La valeur ne peut pas dépasser le barème.',
      );
    }
  }

  private verifierDate(dateEvaluation: string): void {
    if (new Date(dateEvaluation) > new Date()) {
      throw new BadRequestException(
        "La date d'évaluation ne peut pas être dans le futur.",
      );
    }
  }

  private async verifierReferences(
    ecoleId: string,
    eleveId: string,
    matiereId: string,
    classeId: string,
    trimestreId: string,
  ): Promise<void> {
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'eleves').doc(eleveId),
      'Élève introuvable.',
    );
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'matieres').doc(matiereId),
      'Matière introuvable.',
    );
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'classes').doc(classeId),
      'Classe introuvable.',
    );
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'trimestres').doc(trimestreId),
      'Trimestre introuvable.',
    );
  }
}
