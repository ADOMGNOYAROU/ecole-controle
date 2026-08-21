import { Controller, Get, Param, UseGuards } from '@nestjs/common';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import { PremiumGuard } from '../auth/premium.guard';
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { ProgressionService } from './progression.service';

// Fonctionnalité Premium, comme dans l'app Laravel d'origine.
@Controller('eleves/:eleveId/progression')
@UseGuards(FirebaseAuthGuard, RolesGuard, PremiumGuard)
@Roles('admin', 'enseignant')
export class ProgressionController {
  constructor(private readonly service: ProgressionService) {}

  @Get()
  analyser(
    @CurrentUser() user: AuthenticatedUser,
    @Param('eleveId') eleveId: string,
  ) {
    return this.service.analyser(user.ecoleId!, eleveId);
  }
}
