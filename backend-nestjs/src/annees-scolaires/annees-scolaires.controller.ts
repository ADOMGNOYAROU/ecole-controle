import {
  Body,
  Controller,
  Delete,
  Get,
  Param,
  Post,
  Put,
  UseGuards,
} from '@nestjs/common';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { AnneesScolairesService } from './annees-scolaires.service';
import { AnneeScolaireDto } from './dto/annee-scolaire.dto';

@Controller('annees-scolaires')
@UseGuards(FirebaseAuthGuard, RolesGuard)
@Roles('admin')
export class AnneesScolairesController {
  constructor(private readonly service: AnneesScolairesService) {}

  @Get()
  lister(@CurrentUser() user: AuthenticatedUser) {
    return this.service.lister(user.ecoleId!);
  }

  @Post()
  creer(@CurrentUser() user: AuthenticatedUser, @Body() dto: AnneeScolaireDto) {
    return this.service.creer(user.ecoleId!, dto);
  }

  @Put(':id')
  modifier(
    @CurrentUser() user: AuthenticatedUser,
    @Param('id') id: string,
    @Body() dto: AnneeScolaireDto,
  ) {
    return this.service.modifier(user.ecoleId!, id, dto);
  }

  @Delete(':id')
  supprimer(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.supprimer(user.ecoleId!, id);
  }
}
