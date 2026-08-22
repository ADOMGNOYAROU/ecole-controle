import {
  Body,
  Controller,
  Get,
  Param,
  Post,
  Query,
  UseGuards,
} from '@nestjs/common';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { ConfirmerFactureDto } from './dto/confirmer-facture.dto';
import { FacturesService } from './factures.service';

// Volontairement pas de PremiumGuard sur les routes /abonnement : c'est la
// porte de sortie qui permet à une école non-Premium (ou suspendue) de
// consulter et régler son abonnement, comme dans l'app Laravel d'origine.
@Controller()
export class FacturesController {
  constructor(private readonly service: FacturesService) {}

  @Get('super-admin/dashboard')
  @UseGuards(FirebaseAuthGuard, RolesGuard)
  @Roles('super_admin')
  dashboard() {
    return this.service.dashboard();
  }

  @Get('super-admin/factures')
  @UseGuards(FirebaseAuthGuard, RolesGuard)
  @Roles('super_admin')
  listerToutes(
    @Query('statut') statut?: string,
    @Query('ecoleId') ecoleId?: string,
  ) {
    return this.service.listerToutes({ statut, ecoleId });
  }

  @Post('super-admin/factures/:id/confirmer')
  @UseGuards(FirebaseAuthGuard, RolesGuard)
  @Roles('super_admin')
  confirmer(
    @CurrentUser() user: AuthenticatedUser,
    @Param('id') id: string,
    @Body() dto: ConfirmerFactureDto,
  ) {
    return this.service.confirmer(id, dto, user.uid);
  }

  @Get('abonnement')
  @UseGuards(FirebaseAuthGuard, RolesGuard)
  @Roles('admin')
  monAbonnement(@CurrentUser() user: AuthenticatedUser) {
    return this.service.pourEcole(user.ecoleId!);
  }

  @Post('abonnement/souscrire')
  @UseGuards(FirebaseAuthGuard, RolesGuard)
  @Roles('admin')
  souscrire(@CurrentUser() user: AuthenticatedUser) {
    return this.service.souscrire(user.ecoleId!);
  }
}
