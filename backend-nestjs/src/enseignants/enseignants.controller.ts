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
import { EnseignantDto } from './dto/enseignant.dto';
import { EnseignantsService } from './enseignants.service';

@Controller('enseignants')
@UseGuards(FirebaseAuthGuard, RolesGuard)
@Roles('admin')
export class EnseignantsController {
  constructor(
    private readonly service: EnseignantsService,
    private readonly pdfService: PdfService,
  ) {}

  @Get()
  lister(@CurrentUser() user: AuthenticatedUser) {
    return this.service.lister(user.ecoleId!);
  }

  @Get('rapport')
  @UseGuards(PremiumGuard)
  async rapport(
    @CurrentUser() user: AuthenticatedUser,
    @Res() res: Response,
  ): Promise<void> {
    const lignes = await this.service.pourRapport(user.ecoleId!);
    const pdf = await this.pdfService.genererListePdf({
      titre: 'Liste des enseignants',
      colonnes: [
        'Nom complet',
        'Spécialité',
        'Téléphone',
        'Classes principales',
      ],
      lignes,
    });
    res.setHeader('Content-Type', 'application/pdf');
    res.setHeader(
      'Content-Disposition',
      `attachment; filename="rapport-enseignants-${Date.now()}.pdf"`,
    );
    res.send(pdf);
  }

  @Get(':id')
  trouver(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.trouver(user.ecoleId!, id);
  }

  @Post()
  creer(@CurrentUser() user: AuthenticatedUser, @Body() dto: EnseignantDto) {
    return this.service.creer(user.ecoleId!, dto);
  }

  @Put(':id')
  modifier(
    @CurrentUser() user: AuthenticatedUser,
    @Param('id') id: string,
    @Body() dto: EnseignantDto,
  ) {
    return this.service.modifier(user.ecoleId!, id, dto);
  }

  @Delete(':id')
  supprimer(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.supprimer(user.ecoleId!, id);
  }
}
