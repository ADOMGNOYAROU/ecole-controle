import { ForbiddenException, Inject, Injectable } from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue } from 'firebase-admin/firestore';
import type { AuthenticatedUser } from '../auth/types';
import {
  creerNotification,
  ecoleCollection,
  getDocOrThrow,
  trouverTuteurIdParUid,
} from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { AnnonceDto } from './dto/annonce.dto';

export interface Annonce {
  id: string;
  titre: string;
  contenu: string;
  cible: 'tous' | 'parents' | 'enseignants' | 'eleves' | 'classe';
  classeId: string | null;
  auteurId: string;
  datePublication: unknown;
}

@Injectable()
export class AnnoncesService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  // Reproduit la visibilité d'AnnonceController::index() : chaque rôle ne
  // voit que les annonces qui le concernent.
  async lister(ecoleId: string, user: AuthenticatedUser): Promise<Annonce[]> {
    const snap = await ecoleCollection(this.db, ecoleId, 'annonces').get();
    const annonces = snap.docs.map(
      (doc) => ({ id: doc.id, ...doc.data() }) as Annonce,
    );

    if (user.role === 'admin') {
      return annonces;
    }
    if (user.role === 'enseignant') {
      return annonces.filter(
        (a) => a.cible === 'tous' || a.cible === 'enseignants',
      );
    }
    if (user.role === 'eleve') {
      const classeId = await this.classeDeLeleve(ecoleId, user.uid);
      return annonces.filter(
        (a) =>
          a.cible === 'tous' ||
          a.cible === 'eleves' ||
          (a.cible === 'classe' && a.classeId === classeId),
      );
    }
    if (user.role === 'parent') {
      const classeIds = await this.classesDesEnfantsDe(ecoleId, user.uid);
      return annonces.filter(
        (a) =>
          a.cible === 'tous' ||
          a.cible === 'parents' ||
          (a.cible === 'classe' &&
            a.classeId !== null &&
            classeIds.includes(a.classeId)),
      );
    }
    return [];
  }

  async creer(
    ecoleId: string,
    auteurId: string,
    dto: AnnonceDto,
  ): Promise<{ id: string }> {
    if (dto.cible === 'classe') {
      await getDocOrThrow(
        ecoleCollection(this.db, ecoleId, 'classes').doc(dto.classeId!),
        'Classe introuvable.',
      );
    }

    const ref = await ecoleCollection(this.db, ecoleId, 'annonces').add({
      titre: dto.titre,
      contenu: dto.contenu,
      cible: dto.cible,
      classeId: dto.cible === 'classe' ? dto.classeId : null,
      auteurId,
      datePublication: FieldValue.serverTimestamp(),
    });

    await this.notifierDestinataires(ecoleId, dto);
    return { id: ref.id };
  }

  async supprimer(
    ecoleId: string,
    user: AuthenticatedUser,
    id: string,
  ): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'annonces').doc(id);
    const snap = await getDocOrThrow(ref, 'Annonce introuvable.');
    if (user.role !== 'admin' && snap.data()!['auteurId'] !== user.uid) {
      throw new ForbiddenException('Accès non autorisé.');
    }
    await ref.delete();
  }

  private async notifierDestinataires(
    ecoleId: string,
    dto: AnnonceDto,
  ): Promise<void> {
    const destinataires = await this.trouverDestinataires(ecoleId, dto);
    for (const utilisateurId of destinataires) {
      await creerNotification(this.db, ecoleId, {
        utilisateurId,
        titre: 'Nouvelle annonce',
        message: dto.titre,
        type: 'annonce',
      });
    }
  }

  private async trouverDestinataires(
    ecoleId: string,
    dto: AnnonceDto,
  ): Promise<string[]> {
    if (dto.cible === 'classe') {
      // Seuls les élèves de la classe sont notifiés, comme dans l'app Laravel.
      const elevesSnap = await ecoleCollection(this.db, ecoleId, 'eleves')
        .where('classeId', '==', dto.classeId)
        .get();
      const userIds: string[] = [];
      for (const doc of elevesSnap.docs) {
        const userId = doc.data()['userId'] as string | null;
        if (userId) userIds.push(userId);
      }
      return userIds;
    }

    const roles =
      dto.cible === 'tous'
        ? ['parent', 'enseignant', 'eleve']
        : [this.roleDeCible(dto.cible)];
    const userIds: string[] = [];
    for (const role of roles) {
      const snap = await this.db
        .collection('users')
        .where('ecoleId', '==', ecoleId)
        .where('role', '==', role)
        .get();
      userIds.push(...snap.docs.map((doc) => doc.id));
    }
    return userIds;
  }

  private roleDeCible(cible: AnnonceDto['cible']): string {
    return {
      tous: '',
      parents: 'parent',
      enseignants: 'enseignant',
      eleves: 'eleve',
      classe: '',
    }[cible];
  }

  private async classeDeLeleve(
    ecoleId: string,
    uid: string,
  ): Promise<string | null> {
    const snap = await ecoleCollection(this.db, ecoleId, 'eleves')
      .where('userId', '==', uid)
      .limit(1)
      .get();
    if (snap.empty) return null;
    return (snap.docs[0].data()['classeId'] as string | null) ?? null;
  }

  private async classesDesEnfantsDe(
    ecoleId: string,
    uid: string,
  ): Promise<string[]> {
    const tuteurId = await trouverTuteurIdParUid(this.db, ecoleId, uid);
    if (!tuteurId) return [];

    const tuteurSnap = await ecoleCollection(this.db, ecoleId, 'tuteurs')
      .doc(tuteurId)
      .get();
    const eleves =
      (tuteurSnap.data()?.['eleves'] as { id: string }[] | undefined) ?? [];

    const classeIds: string[] = [];
    for (const { id } of eleves) {
      const eleveSnap = await ecoleCollection(this.db, ecoleId, 'eleves')
        .doc(id)
        .get();
      const classeId = eleveSnap.data()?.['classeId'] as string | null;
      if (classeId) classeIds.push(classeId);
    }
    return classeIds;
  }
}
