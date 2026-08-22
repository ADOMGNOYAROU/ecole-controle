import { Controller, Get, Param, Patch, UseGuards } from '@nestjs/common';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import { PremiumGuard } from '../auth/premium.guard';
import type { AuthenticatedUser } from '../auth/types';
import { NotificationsService } from './notifications.service';

// Fonctionnalité Premium, ouverte à tout rôle authentifié (pas de
// restriction par rôle côté Laravel au-delà de la connexion).
@Controller('notifications')
@UseGuards(FirebaseAuthGuard, PremiumGuard)
export class NotificationsController {
  constructor(private readonly service: NotificationsService) {}

  @Get()
  lister(@CurrentUser() user: AuthenticatedUser) {
    return this.service.lister(user.ecoleId!, user.uid);
  }

  @Patch(':id/lue')
  marquerLue(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.marquerLue(user.ecoleId!, user.uid, id);
  }

  @Patch('lues')
  marquerToutesLues(@CurrentUser() user: AuthenticatedUser) {
    return this.service.marquerToutesLues(user.ecoleId!, user.uid);
  }
}
