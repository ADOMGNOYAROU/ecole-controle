import { Controller, Get, Param, UseGuards } from '@nestjs/common';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import { PremiumGuard } from '../auth/premium.guard';
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { EspaceParentService } from './espace-parent.service';

// Fonctionnalité Premium réservée au rôle parent, comme dans l'app d'origine.
@Controller('mes-enfants')
@UseGuards(FirebaseAuthGuard, RolesGuard, PremiumGuard)
@Roles('parent')
export class EspaceParentController {
  constructor(private readonly service: EspaceParentService) {}

  @Get()
  enfants(@CurrentUser() user: AuthenticatedUser) {
    return this.service.enfants(user.ecoleId!, user.uid);
  }

  @Get(':eleveId')
  enfant(
    @CurrentUser() user: AuthenticatedUser,
    @Param('eleveId') eleveId: string,
  ) {
    return this.service.enfant(user.ecoleId!, user.uid, eleveId);
  }
}
