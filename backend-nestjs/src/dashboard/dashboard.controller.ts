import { Controller, Get, UseGuards } from '@nestjs/common';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import type { AuthenticatedUser } from '../auth/types';
import { DashboardService } from './dashboard.service';

// Pas de PremiumGuard : la route /dashboard n'est pas dans le groupe
// 'premium' de l'app Laravel d'origine.
@Controller('dashboard')
@UseGuards(FirebaseAuthGuard)
export class DashboardController {
  constructor(private readonly service: DashboardService) {}

  @Get()
  obtenir(@CurrentUser() user: AuthenticatedUser) {
    return this.service.pourUtilisateur(user.ecoleId, user);
  }
}
