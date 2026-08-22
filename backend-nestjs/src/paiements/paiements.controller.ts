import {
  Body,
  Controller,
  Delete,
  Get,
  Param,
  Post,
  Put,
  Query,
  Res,
  UseGuards,
} from '@nestjs/common';
import type { Response } from 'express';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import { PremiumGuard } from '../auth/premium.guard';
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { PdfService } from '../pdf/pdf.service';
import { PaiementDto } from './dto/paiement.dto';
import { PaiementsRappelsService } from './paiements-rappels.service';
import { PaiementsService } from './paiements.service';

// Fonctionnalité Premium réservée à l'admin, comme dans l'app d'origine.
@Controller('paiements')
@UseGuards(FirebaseAuthGuard, RolesGuard, PremiumGuard)
@Roles('admin')
export class PaiementsController {
  constructor(
    private readonly service: PaiementsService,
    private readonly rappelsService: PaiementsRappelsService,
    private readonly pdfService: PdfService,
  ) {}

  @Get()
  lister(
    @CurrentUser() user: AuthenticatedUser,
    @Query('statut') statut?: string,
    @Query('eleveId') eleveId?: string,
  ) {
    return this.service.lister(user.ecoleId!, { statut, eleveId });
  }

  @Get('stats')
  stats(@CurrentUser() user: AuthenticatedUser) {
    return this.service.stats(user.ecoleId!);
  }

  @Get('rapport')
  async rapport(
    @CurrentUser() user: AuthenticatedUser,
    @Res() res: Response,
    @Query('statut') statut?: string,
    @Query('eleveId') eleveId?: string,
  ): Promise<void> {
    const lignes = await this.service.pourRapport(user.ecoleId!, {
      statut,
      eleveId,
    });
    const pdf = await this.pdfService.genererListePdf({
      titre: 'Liste des paiements',
      colonnes: ['Élève', 'Type', 'Montant', 'Payé', 'Échéance', 'Statut'],
      lignes,
    });
    res.setHeader('Content-Type', 'application/pdf');
    res.setHeader(
      'Content-Disposition',
      `attachment; filename="rapport-paiements-${Date.now()}.pdf"`,
    );
    res.send(pdf);
  }

  @Post('rappels')
  async declencherRappels(@CurrentUser() user: AuthenticatedUser) {
    const envoyes = await this.rappelsService.executerPourEcole(user.ecoleId!);
    return { envoyes };
  }

  @Get(':id')
  trouver(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.trouver(user.ecoleId!, id);
  }

  @Post()
  creer(@CurrentUser() user: AuthenticatedUser, @Body() dto: PaiementDto) {
    return this.service.creer(user.ecoleId!, dto);
  }

  @Put(':id')
  modifier(
    @CurrentUser() user: AuthenticatedUser,
    @Param('id') id: string,
    @Body() dto: PaiementDto,
  ) {
    return this.service.modifier(user.ecoleId!, id, dto);
  }

  @Delete(':id')
  supprimer(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.supprimer(user.ecoleId!, id);
  }
}
