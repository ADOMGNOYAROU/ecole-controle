import {
  Body,
  Controller,
  Delete,
  Get,
  Param,
  Post,
  Put,
  Query,
  UseGuards,
} from '@nestjs/common';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
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
  constructor(private readonly service: NotesService) {}

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
