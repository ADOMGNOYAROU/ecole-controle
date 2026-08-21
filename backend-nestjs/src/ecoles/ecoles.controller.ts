import {
  Body,
  Controller,
  Get,
  Param,
  Patch,
  Post,
  UseGuards,
} from '@nestjs/common';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import { RolesGuard } from '../auth/roles.guard';
import { Roles } from '../auth/roles.decorator';
import { EcolesService } from './ecoles.service';
import { InscriptionEcoleDto } from './dto/inscription-ecole.dto';

@Controller()
export class EcolesController {
  constructor(private readonly ecolesService: EcolesService) {}

  @Post('inscription')
  inscrire(@Body() dto: InscriptionEcoleDto) {
    return this.ecolesService.inscrire(dto);
  }

  @Get('super-admin/ecoles')
  @UseGuards(FirebaseAuthGuard, RolesGuard)
  @Roles('super_admin')
  lister() {
    return this.ecolesService.lister();
  }

  @Get('super-admin/ecoles/:id')
  @UseGuards(FirebaseAuthGuard, RolesGuard)
  @Roles('super_admin')
  trouver(@Param('id') id: string) {
    return this.ecolesService.trouver(id);
  }

  @Patch('super-admin/ecoles/:id/suspendre')
  @UseGuards(FirebaseAuthGuard, RolesGuard)
  @Roles('super_admin')
  suspendre(@Param('id') id: string) {
    return this.ecolesService.suspendre(id);
  }

  @Patch('super-admin/ecoles/:id/activer')
  @UseGuards(FirebaseAuthGuard, RolesGuard)
  @Roles('super_admin')
  activer(@Param('id') id: string) {
    return this.ecolesService.activer(id);
  }
}
