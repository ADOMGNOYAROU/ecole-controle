import {
  Body,
  Controller,
  Delete,
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
import { CreneauxHorairesService } from './creneaux-horaires.service';
import { CreneauHoraireDto } from './dto/creneau-horaire.dto';

// La consultation de l'emploi du temps est ouverte à tous les rôles
// authentifiés (admin, enseignant, parent, élève) ; seule l'admin peut
// le modifier, comme dans l'app Laravel d'origine.
@Controller('emploi-du-temps')
@UseGuards(FirebaseAuthGuard)
export class CreneauxHorairesController {
  constructor(private readonly service: CreneauxHorairesService) {}

  @Get()
  lister(
    @CurrentUser() user: AuthenticatedUser,
    @Query('classeId') classeId?: string,
  ) {
    return this.service.lister(user.ecoleId!, classeId);
  }

  @Post()
  @UseGuards(RolesGuard)
  @Roles('admin')
  creer(
    @CurrentUser() user: AuthenticatedUser,
    @Body() dto: CreneauHoraireDto,
  ) {
    return this.service.creer(user.ecoleId!, dto);
  }

  @Delete(':id')
  @UseGuards(RolesGuard)
  @Roles('admin')
  supprimer(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.supprimer(user.ecoleId!, id);
  }
}
