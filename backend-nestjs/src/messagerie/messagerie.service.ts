import { ForbiddenException, Inject, Injectable } from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue, Timestamp } from 'firebase-admin/firestore';
import type { AuthenticatedUser } from '../auth/types';
import {
  creerNotification,
  ecoleCollection,
  trouverEnseignantIdParUid,
  trouverTuteurIdParUid,
} from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';

export interface Interlocuteur {
  userId: string;
  nom: string;
  email: string | null;
}

export interface Message {
  id: string;
  expediteurId: string;
  destinataireId: string;
  contenu: string;
  lu: boolean;
  createdAt?: Timestamp;
}

@Injectable()
export class MessagerieService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  // Reproduit MessagerieService::interlocuteursPossibles() : un enseignant
  // ne peut contacter que les parents des élèves de ses classes, et
  // réciproquement — pas d'accès libre à l'ensemble des comptes de l'école.
  async interlocuteursPossibles(
    ecoleId: string,
    user: AuthenticatedUser,
  ): Promise<Interlocuteur[]> {
    if (user.role === 'enseignant') {
      const enseignantId = await trouverEnseignantIdParUid(
        this.db,
        ecoleId,
        user.uid,
      );
      if (!enseignantId) return [];
      return this.parentsDesClassesDe(ecoleId, enseignantId);
    }
    if (user.role === 'parent') {
      const tuteurId = await trouverTuteurIdParUid(this.db, ecoleId, user.uid);
      if (!tuteurId) return [];
      return this.enseignantsDesEnfantsDe(ecoleId, tuteurId);
    }
    return [];
  }

  async conversations(ecoleId: string, user: AuthenticatedUser) {
    const interlocuteurs = await this.interlocuteursPossibles(ecoleId, user);

    const conversations = await Promise.all(
      interlocuteurs.map(async (interlocuteur) => {
        const messages = await this.messagesEntre(
          ecoleId,
          user.uid,
          interlocuteur.userId,
        );
        const dernierMessage = messages[messages.length - 1] ?? null;
        const nonLus = messages.filter(
          (m) => m.destinataireId === user.uid && !m.lu,
        ).length;
        return { interlocuteur, dernierMessage, nonLus };
      }),
    );

    return conversations.sort(
      (a, b) =>
        (b.dernierMessage?.createdAt?.toMillis() ?? 0) -
        (a.dernierMessage?.createdAt?.toMillis() ?? 0),
    );
  }

  async historique(
    ecoleId: string,
    user: AuthenticatedUser,
    autreUserId: string,
  ): Promise<Message[]> {
    await this.verifierPeuventEchanger(ecoleId, user, autreUserId);

    const messages = await this.messagesEntre(ecoleId, user.uid, autreUserId);

    const batch = this.db.batch();
    for (const message of messages) {
      if (message.destinataireId === user.uid && !message.lu) {
        batch.update(
          ecoleCollection(this.db, ecoleId, 'messages').doc(message.id),
          { lu: true },
        );
      }
    }
    await batch.commit();

    return messages;
  }

  async envoyer(
    ecoleId: string,
    user: AuthenticatedUser,
    autreUserId: string,
    contenu: string,
  ): Promise<{ id: string }> {
    await this.verifierPeuventEchanger(ecoleId, user, autreUserId);

    const ref = await ecoleCollection(this.db, ecoleId, 'messages').add({
      expediteurId: user.uid,
      destinataireId: autreUserId,
      contenu,
      lu: false,
      createdAt: FieldValue.serverTimestamp(),
    });

    const expediteurSnap = await this.db
      .collection('users')
      .doc(user.uid)
      .get();
    const expediteurNom =
      (expediteurSnap.data()?.['name'] as string | undefined) ??
      'Un utilisateur';

    await creerNotification(this.db, ecoleId, {
      utilisateurId: autreUserId,
      titre: `Nouveau message de ${expediteurNom}`,
      message: contenu.length > 100 ? `${contenu.slice(0, 100)}…` : contenu,
      type: 'message',
    });

    return { id: ref.id };
  }

  private async verifierPeuventEchanger(
    ecoleId: string,
    user: AuthenticatedUser,
    autreUserId: string,
  ): Promise<void> {
    const possibles = await this.interlocuteursPossibles(ecoleId, user);
    if (!possibles.some((i) => i.userId === autreUserId)) {
      throw new ForbiddenException(
        'Vous ne pouvez pas contacter cet utilisateur.',
      );
    }
  }

  private async messagesEntre(
    ecoleId: string,
    userIdA: string,
    userIdB: string,
  ): Promise<Message[]> {
    const [aVersB, bVersA] = await Promise.all([
      ecoleCollection(this.db, ecoleId, 'messages')
        .where('expediteurId', '==', userIdA)
        .where('destinataireId', '==', userIdB)
        .get(),
      ecoleCollection(this.db, ecoleId, 'messages')
        .where('expediteurId', '==', userIdB)
        .where('destinataireId', '==', userIdA)
        .get(),
    ]);

    const messages = [...aVersB.docs, ...bVersA.docs].map(
      (doc) => ({ id: doc.id, ...doc.data() }) as Message,
    );
    return messages.sort(
      (a, b) => (a.createdAt?.toMillis() ?? 0) - (b.createdAt?.toMillis() ?? 0),
    );
  }

  private async classesDe(
    ecoleId: string,
    enseignantId: string,
  ): Promise<string[]> {
    const enseignantSnap = await ecoleCollection(
      this.db,
      ecoleId,
      'enseignants',
    )
      .doc(enseignantId)
      .get();
    const classeIds = new Set(
      (enseignantSnap.data()?.['classeIds'] as string[] | undefined) ?? [],
    );

    const principalSnap = await ecoleCollection(this.db, ecoleId, 'classes')
      .where('enseignantPrincipalId', '==', enseignantId)
      .get();
    for (const doc of principalSnap.docs) classeIds.add(doc.id);

    return [...classeIds];
  }

  private async parentsDesClassesDe(
    ecoleId: string,
    enseignantId: string,
  ): Promise<Interlocuteur[]> {
    const classeIds = await this.classesDe(ecoleId, enseignantId);
    if (classeIds.length === 0) return [];

    const tuteurIds = new Set<string>();
    for (const classeId of classeIds) {
      const elevesSnap = await ecoleCollection(this.db, ecoleId, 'eleves')
        .where('classeId', '==', classeId)
        .get();
      for (const doc of elevesSnap.docs) {
        for (const tuteurId of (doc.data()['tuteurIds'] as
          string[] | undefined) ?? []) {
          tuteurIds.add(tuteurId);
        }
      }
    }

    const interlocuteurs: Interlocuteur[] = [];
    for (const tuteurId of tuteurIds) {
      const tuteurSnap = await ecoleCollection(this.db, ecoleId, 'tuteurs')
        .doc(tuteurId)
        .get();
      const userId = tuteurSnap.data()?.['userId'] as string | undefined;
      if (!userId) continue;
      const userSnap = await this.db.collection('users').doc(userId).get();
      if (!userSnap.exists) continue;
      interlocuteurs.push({
        userId,
        nom: userSnap.data()!['name'] as string,
        email: (userSnap.data()!['email'] as string | null) ?? null,
      });
    }
    return interlocuteurs.sort((a, b) => a.nom.localeCompare(b.nom));
  }

  private async enseignantsDesEnfantsDe(
    ecoleId: string,
    tuteurId: string,
  ): Promise<Interlocuteur[]> {
    const tuteurSnap = await ecoleCollection(this.db, ecoleId, 'tuteurs')
      .doc(tuteurId)
      .get();
    const eleves =
      (tuteurSnap.data()?.['eleves'] as { id: string }[] | undefined) ?? [];

    const classeIds = new Set<string>();
    for (const { id } of eleves) {
      const eleveSnap = await ecoleCollection(this.db, ecoleId, 'eleves')
        .doc(id)
        .get();
      const classeId = eleveSnap.data()?.['classeId'] as string | null;
      if (classeId) classeIds.add(classeId);
    }

    const enseignantIds = new Set<string>();
    for (const classeId of classeIds) {
      const classeSnap = await ecoleCollection(this.db, ecoleId, 'classes')
        .doc(classeId)
        .get();
      const principalId = classeSnap.data()?.['enseignantPrincipalId'] as
        string | null;
      if (principalId) enseignantIds.add(principalId);

      const enseignantsSnap = await ecoleCollection(
        this.db,
        ecoleId,
        'enseignants',
      )
        .where('classeIds', 'array-contains', classeId)
        .get();
      for (const doc of enseignantsSnap.docs) enseignantIds.add(doc.id);
    }

    const interlocuteurs: Interlocuteur[] = [];
    for (const enseignantId of enseignantIds) {
      const enseignantSnap = await ecoleCollection(
        this.db,
        ecoleId,
        'enseignants',
      )
        .doc(enseignantId)
        .get();
      const userId = enseignantSnap.data()?.['userId'] as string | undefined;
      if (!userId) continue;
      const userSnap = await this.db.collection('users').doc(userId).get();
      if (!userSnap.exists) continue;
      interlocuteurs.push({
        userId,
        nom: userSnap.data()!['name'] as string,
        email: (userSnap.data()!['email'] as string | null) ?? null,
      });
    }
    return interlocuteurs.sort((a, b) => a.nom.localeCompare(b.nom));
  }
}
