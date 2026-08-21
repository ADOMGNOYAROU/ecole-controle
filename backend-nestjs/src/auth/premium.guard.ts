import {
  CanActivate,
  ExecutionContext,
  ForbiddenException,
  Inject,
  Injectable,
} from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { Timestamp } from 'firebase-admin/firestore';
import { FIRESTORE } from '../firebase/firebase.constants';
import { AuthenticatedUser } from './types';

@Injectable()
export class PremiumGuard implements CanActivate {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const request = context
      .switchToHttp()
      .getRequest<{ user: AuthenticatedUser }>();
    const user = request.user;

    if (user.role === 'super_admin') {
      return true;
    }

    if (!user.ecoleId) {
      throw new ForbiddenException('Aucune école associée à ce compte.');
    }

    const ecoleSnap = await this.db
      .collection('ecoles')
      .doc(user.ecoleId)
      .get();
    if (!ecoleSnap.exists) {
      throw new ForbiddenException('École introuvable.');
    }

    if (await this.aAccesPremium(user.ecoleId, ecoleSnap.data()!)) {
      return true;
    }

    throw new ForbiddenException(
      "Cette fonctionnalité fait partie de l'offre Premium.",
    );
  }

  // Reproduit Ecole::aAccesPremium() de l'app Laravel d'origine.
  private async aAccesPremium(
    ecoleId: string,
    ecole: FirebaseFirestore.DocumentData,
  ): Promise<boolean> {
    if (ecole['statut'] === 'suspendu') {
      return false;
    }

    if (ecole['plan'] !== 'premium') {
      return false;
    }

    const trialEndsAt = this.versDate(ecole['trialEndsAt']);
    const enEssai =
      ecole['statut'] === 'essai' &&
      trialEndsAt !== undefined &&
      trialEndsAt > new Date();

    if (enEssai) {
      return true;
    }

    // Filtre sur un seul champ pour éviter un index composite Firestore ;
    // le filtre sur dateFin se fait en mémoire (peu de lignes par école).
    const abonnementsActifs = await this.db
      .collection('ecoles')
      .doc(ecoleId)
      .collection('abonnements')
      .where('statut', '==', 'actif')
      .get();

    const maintenant = new Date();
    return abonnementsActifs.docs.some((doc) => {
      const dateFin = this.versDate(doc.data()['dateFin']);
      return dateFin !== undefined && dateFin >= maintenant;
    });
  }

  private versDate(valeur: unknown): Date | undefined {
    if (valeur instanceof Timestamp) {
      return valeur.toDate();
    }
    return undefined;
  }
}
