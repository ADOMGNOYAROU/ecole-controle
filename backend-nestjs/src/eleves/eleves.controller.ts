import {
  Body,
  Controller,
  Delete,
  Get,
  Param,
  Post,
  Put,
  Res,
  UseGuards,
} from '@nestjs/common';
import type { Response } from 'express';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import { PdfService } from '../pdf/pdf.service';
import { PremiumGuard } from '../auth/premium.guard';
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { EleveDto } from './dto/eleve.dto';
import { ElevesService } from './eleves.service';

// Consultation (index/show) ouverte à admin + enseignant ; la
// modification reste réservée à l'admin, comme dans l'app d'origine.
@Controller('eleves')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class ElevesController {
  constructor(
    private readonly service: ElevesService,
    private readonly pdfService: PdfService,
  ) {}

  @Get()
  @Roles('admin', 'enseignant')
  lister(@CurrentUser() user: AuthenticatedUser) {
    return this.service.lister(user.ecoleId!);
  }

  @Get('rapport')
  @UseGuards(PremiumGuard)
  @Roles('admin', 'enseignant')
  async rapport(
    @CurrentUser() user: AuthenticatedUser,
    @Res() res: Response,
  ): Promise<void> {
    const lignes = await this.service.pourRapport(user.ecoleId!);
    const pdf = await this.pdfService.genererListePdf({
      titre: 'Liste des élèves',
      colonnes: ['Matricule', 'Nom complet', 'Classe', 'Sexe', 'Statut'],
      lignes,
    });
    res.setHeader('Content-Type', 'application/pdf');
    res.setHeader(
      'Content-Disposition',
      `attachment; filename="rapport-eleves-${Date.now()}.pdf"`,
    );
    res.send(pdf);
  }

  @Get(':id')
  @Roles('admin', 'enseignant')
  trouver(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.trouver(user.ecoleId!, id);
  }

  @Post()
  @Roles('admin')
  creer(@CurrentUser() user: AuthenticatedUser, @Body() dto: EleveDto) {
    return this.service.creer(user.ecoleId!, dto);
  }

  @Put(':id')
  @Roles('admin')
  modifier(
    @CurrentUser() user: AuthenticatedUser,
    @Param('id') id: string,
    @Body() dto: EleveDto,
  ) {
    return this.service.modifier(user.ecoleId!, id, dto);
  }

  @Delete(':id')
  @Roles('admin')
  supprimer(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.supprimer(user.ecoleId!, id);
  }
}
