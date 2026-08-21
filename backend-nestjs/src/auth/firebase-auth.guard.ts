import {
  CanActivate,
  ExecutionContext,
  Inject,
  Injectable,
  UnauthorizedException,
} from '@nestjs/common';
import type { Request } from 'express';
import type { Auth } from 'firebase-admin/auth';
import { FIREBASE_AUTH } from '../firebase/firebase.constants';
import { AuthenticatedUser } from './types';

@Injectable()
export class FirebaseAuthGuard implements CanActivate {
  constructor(@Inject(FIREBASE_AUTH) private readonly auth: Auth) {}

  async canActivate(context: ExecutionContext): Promise<boolean> {
    const request = context
      .switchToHttp()
      .getRequest<Request & { user?: AuthenticatedUser }>();
    const header = request.headers.authorization;

    if (!header?.startsWith('Bearer ')) {
      throw new UnauthorizedException('Token manquant.');
    }

    const token = header.slice('Bearer '.length);

    try {
      const decoded = await this.auth.verifyIdToken(token);
      const role = decoded['role'] as AuthenticatedUser['role'];
      const ecoleId = (decoded['ecoleId'] as string | undefined) ?? null;

      request.user = {
        uid: decoded.uid,
        email: decoded.email ?? null,
        role,
        ecoleId,
      };
      return true;
    } catch {
      throw new UnauthorizedException('Token invalide ou expiré.');
    }
  }
}
