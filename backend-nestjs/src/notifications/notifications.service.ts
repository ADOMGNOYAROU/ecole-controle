import { ForbiddenException, Inject, Injectable } from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { ecoleCollection, getDocOrThrow } from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';

@Injectable()
export class NotificationsService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  async lister(ecoleId: string, uid: string) {
    const snap = await ecoleCollection(this.db, ecoleId, 'notifications')
      .where('utilisateurId', '==', uid)
      .get();
    const avecTri = snap.docs.map((doc) => {
      const creeLe = doc.data()['creeLe'] as
        FirebaseFirestore.Timestamp | undefined;
      return {
        notification: { id: doc.id, ...doc.data() },
        tri: creeLe?.toMillis() ?? 0,
      };
    });
    return avecTri
      .sort((a, b) => b.tri - a.tri)
      .map((entree) => entree.notification);
  }

  async marquerLue(ecoleId: string, uid: string, id: string): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'notifications').doc(id);
    const snap = await getDocOrThrow(ref, 'Notification introuvable.');
    if (snap.data()!['utilisateurId'] !== uid) {
      throw new ForbiddenException('Accès non autorisé.');
    }
    await ref.update({ lu: true });
  }

  async marquerToutesLues(ecoleId: string, uid: string): Promise<void> {
    const snap = await ecoleCollection(this.db, ecoleId, 'notifications')
      .where('utilisateurId', '==', uid)
      .where('lu', '==', false)
      .get();

    const batch = this.db.batch();
    for (const doc of snap.docs) {
      batch.update(doc.ref, { lu: true });
    }
    await batch.commit();
  }
}
