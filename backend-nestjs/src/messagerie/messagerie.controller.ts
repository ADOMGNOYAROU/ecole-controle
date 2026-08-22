import { Body, Controller, Get, Param, Post, UseGuards } from '@nestjs/common';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import { PremiumGuard } from '../auth/premium.guard';
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { EnvoyerMessageDto } from './dto/envoyer-message.dto';
import { MessagerieService } from './messagerie.service';

// Fonctionnalité Premium réservée aux enseignants et parents, comme dans
// l'app Laravel d'origine.
@Controller('messagerie')
@UseGuards(FirebaseAuthGuard, RolesGuard, PremiumGuard)
@Roles('enseignant', 'parent')
export class MessagerieController {
  constructor(private readonly service: MessagerieService) {}

  @Get()
  conversations(@CurrentUser() user: AuthenticatedUser) {
    return this.service.conversations(user.ecoleId!, user);
  }

  @Get(':utilisateurId')
  historique(
    @CurrentUser() user: AuthenticatedUser,
    @Param('utilisateurId') utilisateurId: string,
  ) {
    return this.service.historique(user.ecoleId!, user, utilisateurId);
  }

  @Post(':utilisateurId')
  envoyer(
    @CurrentUser() user: AuthenticatedUser,
    @Param('utilisateurId') utilisateurId: string,
    @Body() dto: EnvoyerMessageDto,
  ) {
    return this.service.envoyer(
      user.ecoleId!,
      user,
      utilisateurId,
      dto.contenu,
    );
  }
}
