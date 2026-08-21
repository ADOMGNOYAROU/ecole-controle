import {
  Body,
  Controller,
  Delete,
  Get,
  Param,
  Patch,
  Post,
  UseGuards,
} from '@nestjs/common';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import { PremiumGuard } from '../auth/premium.guard';
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { GenererCompteDto } from './dto/generer-compte.dto';
import { ReinitialiserMotDePasseDto } from './dto/reinitialiser-mot-de-passe.dto';
import { UsersService } from './users.service';

// Équivalent de UserAccountController — routes /comptes, réservées à
// l'admin d'une école Premium (mêmes contraintes que dans Laravel).
@Controller('comptes')
@UseGuards(FirebaseAuthGuard, RolesGuard, PremiumGuard)
@Roles('admin')
export class UsersController {
  constructor(private readonly usersService: UsersService) {}

  @Get()
  lister(@CurrentUser() user: AuthenticatedUser) {
    return this.usersService.lister(user.ecoleId!);
  }

  @Post('generer')
  generer(
    @CurrentUser() user: AuthenticatedUser,
    @Body() dto: GenererCompteDto,
  ) {
    return this.usersService.genererCompte(user, dto);
  }

  @Patch(':uid/reinitialiser-mot-de-passe')
  reinitialiser(
    @CurrentUser() user: AuthenticatedUser,
    @Param('uid') uid: string,
    @Body() dto: ReinitialiserMotDePasseDto,
  ) {
    return this.usersService.reinitialiserMotDePasse(user, uid, dto);
  }

  @Delete(':uid')
  supprimer(@CurrentUser() user: AuthenticatedUser, @Param('uid') uid: string) {
    return this.usersService.supprimer(user, uid);
  }
}
