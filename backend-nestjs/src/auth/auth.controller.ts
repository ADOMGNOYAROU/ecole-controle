import {
  Controller,
  Get,
  Inject,
  NotFoundException,
  UseGuards,
} from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { FIRESTORE } from '../firebase/firebase.constants';
import { CurrentUser } from './current-user.decorator';
import { FirebaseAuthGuard } from './firebase-auth.guard';
import type { AuthenticatedUser } from './types';

@Controller('auth')
@UseGuards(FirebaseAuthGuard)
export class AuthController {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  @Get('me')
  async moi(@CurrentUser() user: AuthenticatedUser) {
    const snap = await this.db.collection('users').doc(user.uid).get();
    if (!snap.exists) {
      throw new NotFoundException('Profil introuvable.');
    }
    return { id: snap.id, ...snap.data() };
  }
}
