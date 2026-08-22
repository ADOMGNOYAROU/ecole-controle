import {
  CanActivate,
  ExecutionContext,
  ForbiddenException,
  Inject,
  Injectable,
} from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { ecoleAAccesPremium } from '../common/firestore.helpers';
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

    if (await ecoleAAccesPremium(this.db, user.ecoleId, ecoleSnap.data()!)) {
      return true;
    }

    throw new ForbiddenException(
      "Cette fonctionnalité fait partie de l'offre Premium.",
    );
  }
}
