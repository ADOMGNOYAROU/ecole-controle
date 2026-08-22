import { Controller, Get, Query, UseGuards } from '@nestjs/common';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import { PremiumGuard } from '../auth/premium.guard';
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { EspaceEleveService } from './espace-eleve.service';

// Fonctionnalité Premium réservée au rôle élève, comme dans l'app d'origine.
@Controller('mon-espace')
@UseGuards(FirebaseAuthGuard, RolesGuard, PremiumGuard)
@Roles('eleve')
export class EspaceEleveController {
  constructor(private readonly service: EspaceEleveService) {}

  @Get('notes')
  notes(
    @CurrentUser() user: AuthenticatedUser,
    @Query('trimestreId') trimestreId?: string,
  ) {
    return this.service.notes(user.ecoleId!, user.uid, trimestreId);
  }

  @Get('presences')
  presences(@CurrentUser() user: AuthenticatedUser) {
    return this.service.presences(user.ecoleId!, user.uid);
  }

  @Get('paiements')
  paiements(@CurrentUser() user: AuthenticatedUser) {
    return this.service.paiements(user.ecoleId!, user.uid);
  }
}
