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
import { PremiumGuard } from '../auth/premium.guard';
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { NoteBulkDto } from './dto/note-bulk.dto';
import { NoteDto } from './dto/note.dto';
import { NotesService } from './notes.service';

@Controller('notes')
@UseGuards(FirebaseAuthGuard, RolesGuard)
@Roles('admin', 'enseignant')
export class NotesController {
  constructor(
    private readonly service: NotesService,
    private readonly pdfService: PdfService,
  ) {}

  @Get()
  lister(
    @CurrentUser() user: AuthenticatedUser,
    @Query('classeId') classeId?: string,
    @Query('matiereId') matiereId?: string,
    @Query('trimestreId') trimestreId?: string,
  ) {
    return this.service.lister(user.ecoleId!, user, {
      classeId,
      matiereId,
      trimestreId,
    });
  }

  @Get('rapport')
  @UseGuards(PremiumGuard)
  async rapport(
    @CurrentUser() user: AuthenticatedUser,
    @Res() res: Response,
    @Query('classeId') classeId?: string,
    @Query('matiereId') matiereId?: string,
    @Query('trimestreId') trimestreId?: string,
  ): Promise<void> {
    const lignes = await this.service.pourRapport(user.ecoleId!, user, {
      classeId,
      matiereId,
      trimestreId,
    });
    const pdf = await this.pdfService.genererListePdf({
      titre: 'Liste des notes',
      colonnes: ['Élève', 'Matière', 'Classe', 'Type', 'Note', 'Date'],
      lignes,
    });
    res.setHeader('Content-Type', 'application/pdf');
    res.setHeader(
      'Content-Disposition',
      `attachment; filename="rapport-notes-${Date.now()}.pdf"`,
    );
    res.send(pdf);
  }

  @Get('classes/:classeId/eleves')
  elevesPourSaisie(
    @CurrentUser() user: AuthenticatedUser,
    @Param('classeId') classeId: string,
  ) {
    return this.service.elevesPourSaisie(user.ecoleId!, classeId);
  }

  @Post()
  creer(@CurrentUser() user: AuthenticatedUser, @Body() dto: NoteDto) {
    return this.service.creer(user.ecoleId!, user, dto);
  }

  @Post('bulk')
  bulkCreer(@CurrentUser() user: AuthenticatedUser, @Body() dto: NoteBulkDto) {
    return this.service.bulkCreer(user.ecoleId!, user, dto);
  }

  @Put(':id')
  modifier(
    @CurrentUser() user: AuthenticatedUser,
    @Param('id') id: string,
    @Body() dto: NoteDto,
  ) {
    return this.service.modifier(user.ecoleId!, user, id, dto);
  }

  @Delete(':id')
  supprimer(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.supprimer(user.ecoleId!, user, id);
  }
}
