import {
  Body,
  Controller,
  Delete,
  Get,
  Param,
  Post,
  UseGuards,
} from '@nestjs/common';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import { PremiumGuard } from '../auth/premium.guard';
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { AnnoncesService } from './annonces.service';
import { AnnonceDto } from './dto/annonce.dto';

// Fonctionnalité Premium ; la consultation est ouverte à tout rôle
// authentifié (filtrée par cible), la publication à admin + enseignant.
@Controller('annonces')
@UseGuards(FirebaseAuthGuard, PremiumGuard)
export class AnnoncesController {
  constructor(private readonly service: AnnoncesService) {}

  @Get()
  lister(@CurrentUser() user: AuthenticatedUser) {
    return this.service.lister(user.ecoleId!, user);
  }

  @Post()
  @UseGuards(RolesGuard)
  @Roles('admin', 'enseignant')
  creer(@CurrentUser() user: AuthenticatedUser, @Body() dto: AnnonceDto) {
    return this.service.creer(user.ecoleId!, user.uid, dto);
  }

  @Delete(':id')
  @UseGuards(RolesGuard)
  @Roles('admin', 'enseignant')
  supprimer(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.supprimer(user.ecoleId!, user, id);
  }
}
