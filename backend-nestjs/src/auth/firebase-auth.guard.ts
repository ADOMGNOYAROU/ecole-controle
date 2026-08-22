import {
  CanActivate,
  ExecutionContext,
  ForbiddenException,
  Inject,
  Injectable,
  UnauthorizedException,
} from '@nestjs/common';
import type { Request } from 'express';
import type { Auth } from 'firebase-admin/auth';
import type { Firestore } from 'firebase-admin/firestore';
import { FIREBASE_AUTH, FIRESTORE } from '../firebase/firebase.constants';
import { AuthenticatedUser } from './types';

// Chemins accessibles même si l'école est suspendue, pour que l'admin
// puisse toujours consulter/payer son abonnement (reproduit l'exemption
// routeIs('abonnement.*', 'logout') de EnsureEcoleActive dans Laravel).
const CHEMINS_EXEMPTES_SUSPENSION = ['/abonnement', '/auth/me'];

@Injectable()
export class FirebaseAuthGuard implements CanActivate {
  constructor(
    @Inject(FIREBASE_AUTH) private readonly auth: Auth,
    @Inject(FIRESTORE) private readonly db: Firestore,
  ) {}

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const request = context
      .switchToHttp()
      .getRequest<Request & { user?: AuthenticatedUser }>();
    const header = request.headers.authorization;

    if (!header?.startsWith('Bearer ')) {
      throw new UnauthorizedException('Token manquant.');
    }

    const token = header.slice('Bearer '.length);

    let role: AuthenticatedUser['role'];
    let ecoleId: string | null;
    let uid: string;
    let email: string | null;
    try {
      const decoded = await this.auth.verifyIdToken(token);
      role = decoded['role'] as AuthenticatedUser['role'];
      ecoleId = (decoded['ecoleId'] as string | undefined) ?? null;
      uid = decoded.uid;
      email = decoded.email ?? null;
    } catch {
      throw new UnauthorizedException('Token invalide ou expiré.');
    }

    request.user = { uid, email, role, ecoleId };

    // Reproduit EnsureEcoleActive : une école suspendue (abonnement
    // impayé) ne peut plus rien faire, à part gérer son abonnement.
    if (
      role !== 'super_admin' &&
      ecoleId &&
      !CHEMINS_EXEMPTES_SUSPENSION.some((chemin) =>
        request.path.startsWith(chemin),
      )
    ) {
      const ecoleSnap = await this.db.collection('ecoles').doc(ecoleId).get();
      if (ecoleSnap.data()?.['statut'] === 'suspendu') {
        throw new ForbiddenException(
          "L'accès de votre école est suspendu (abonnement impayé). Contactez l'administration pour le réactiver.",
        );
      }
    }

    return true;
  }
}
