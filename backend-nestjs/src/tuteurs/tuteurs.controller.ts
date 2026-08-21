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
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { TuteurDto } from './dto/tuteur.dto';
import { TuteursService } from './tuteurs.service';

@Controller('tuteurs')
@UseGuards(FirebaseAuthGuard, RolesGuard)
@Roles('admin')
export class TuteursController {
  constructor(
    private readonly service: TuteursService,
    private readonly pdfService: PdfService,
  ) {}

  @Get()
  lister(@CurrentUser() user: AuthenticatedUser) {
    return this.service.lister(user.ecoleId!);
  }

  @Get('rapport')
  async rapport(
    @CurrentUser() user: AuthenticatedUser,
    @Res() res: Response,
  ): Promise<void> {
    const lignes = await this.service.pourRapport(user.ecoleId!);
    const pdf = await this.pdfService.genererListePdf({
      titre: 'Liste des tuteurs / parents',
      colonnes: ['Nom complet', 'Téléphone', 'Email', "Nombre d'enfants"],
      lignes,
    });
    res.setHeader('Content-Type', 'application/pdf');
    res.setHeader(
      'Content-Disposition',
      `attachment; filename="rapport-tuteurs-${Date.now()}.pdf"`,
    );
    res.send(pdf);
  }

  @Get(':id')
  trouver(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.trouver(user.ecoleId!, id);
  }

  @Post()
  creer(@CurrentUser() user: AuthenticatedUser, @Body() dto: TuteurDto) {
    return this.service.creer(user.ecoleId!, dto);
  }

  @Put(':id')
  modifier(
    @CurrentUser() user: AuthenticatedUser,
    @Param('id') id: string,
    @Body() dto: TuteurDto,
  ) {
    return this.service.modifier(user.ecoleId!, id, dto);
  }

  @Delete(':id')
  supprimer(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.supprimer(user.ecoleId!, id);
  }
}
