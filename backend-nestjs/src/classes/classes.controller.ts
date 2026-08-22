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
import { ClassesService } from './classes.service';
import { ClasseDto } from './dto/classe.dto';

// Consultation (index/show) ouverte à admin + enseignant, comme dans
// l'app Laravel d'origine ; la modification reste réservée à l'admin.
@Controller('classes')
@UseGuards(FirebaseAuthGuard, RolesGuard)
export class ClassesController {
  constructor(
    private readonly service: ClassesService,
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
      titre: 'Liste des classes',
      colonnes: [
        'Classe',
        'Niveau',
        'Effectif',
        'Enseignant principal',
        'Année scolaire',
      ],
      lignes,
    });
    res.setHeader('Content-Type', 'application/pdf');
    res.setHeader(
      'Content-Disposition',
      `attachment; filename="rapport-classes-${Date.now()}.pdf"`,
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
  creer(@CurrentUser() user: AuthenticatedUser, @Body() dto: ClasseDto) {
    return this.service.creer(user.ecoleId!, dto);
  }

  @Put(':id')
  @Roles('admin')
  modifier(
    @CurrentUser() user: AuthenticatedUser,
    @Param('id') id: string,
    @Body() dto: ClasseDto,
  ) {
    return this.service.modifier(user.ecoleId!, id, dto);
  }

  @Delete(':id')
  @Roles('admin')
  supprimer(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.supprimer(user.ecoleId!, id);
  }
}
