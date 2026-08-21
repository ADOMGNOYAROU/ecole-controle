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
import { PdfService } from '../pdf/pdf.service';
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { PresenceBulkDto } from './dto/presence-bulk.dto';
import { PresenceDto } from './dto/presence.dto';
import { PresencesService } from './presences.service';

@Controller('presences')
@UseGuards(FirebaseAuthGuard, RolesGuard)
@Roles('admin', 'enseignant')
export class PresencesController {
  constructor(
    private readonly service: PresencesService,
    private readonly pdfService: PdfService,
  ) {}

  @Get()
  lister(
    @CurrentUser() user: AuthenticatedUser,
    @Query('classeId') classeId?: string,
    @Query('date') date?: string,
  ) {
    return this.service.lister(user.ecoleId!, user, { classeId, date });
  }

  @Get('rapport')
  async rapport(
    @CurrentUser() user: AuthenticatedUser,
    @Res() res: Response,
    @Query('classeId') classeId?: string,
    @Query('date') date?: string,
  ): Promise<void> {
    const lignes = await this.service.pourRapport(user.ecoleId!, user, {
      classeId,
      date,
    });
    const pdf = await this.pdfService.genererListePdf({
      titre: 'Liste des présences',
      colonnes: ['Élève', 'Classe', 'Date', 'Statut', 'Motif'],
      lignes,
    });
    res.setHeader('Content-Type', 'application/pdf');
    res.setHeader(
      'Content-Disposition',
      `attachment; filename="rapport-presences-${Date.now()}.pdf"`,
    );
    res.send(pdf);
  }

  @Get('classes/:classeId/eleves')
  elevesPourAppel(
    @CurrentUser() user: AuthenticatedUser,
    @Param('classeId') classeId: string,
  ) {
    return this.service.elevesPourAppel(user.ecoleId!, classeId);
  }

  @Post()
  creer(@CurrentUser() user: AuthenticatedUser, @Body() dto: PresenceDto) {
    return this.service.creer(user.ecoleId!, user, dto);
  }

  @Post('bulk')
  bulkCreer(
    @CurrentUser() user: AuthenticatedUser,
    @Body() dto: PresenceBulkDto,
  ) {
    return this.service.bulkCreer(user.ecoleId!, user, dto);
  }

  @Put(':id')
  modifier(
    @CurrentUser() user: AuthenticatedUser,
    @Param('id') id: string,
    @Body() dto: PresenceDto,
  ) {
    return this.service.modifier(user.ecoleId!, user, id, dto);
  }

  @Delete(':id')
  supprimer(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.supprimer(user.ecoleId!, user, id);
  }
}
