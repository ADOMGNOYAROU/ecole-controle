import {
  Body,
  Controller,
  Delete,
  Get,
  Param,
  Post,
  Put,
  UseGuards,
} from '@nestjs/common';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { TrimestreDto } from './dto/trimestre.dto';
import { TrimestresService } from './trimestres.service';

@Controller('trimestres')
@UseGuards(FirebaseAuthGuard, RolesGuard)
@Roles('admin')
export class TrimestresController {
  constructor(private readonly service: TrimestresService) {}

  @Get()
  lister(@CurrentUser() user: AuthenticatedUser) {
    return this.service.lister(user.ecoleId!);
  }

  @Post()
  creer(@CurrentUser() user: AuthenticatedUser, @Body() dto: TrimestreDto) {
    return this.service.creer(user.ecoleId!, dto);
  }

  @Put(':id')
  modifier(
    @CurrentUser() user: AuthenticatedUser,
    @Param('id') id: string,
    @Body() dto: TrimestreDto,
  ) {
    return this.service.modifier(user.ecoleId!, id, dto);
  }

  @Delete(':id')
  supprimer(@CurrentUser() user: AuthenticatedUser, @Param('id') id: string) {
    return this.service.supprimer(user.ecoleId!, id);
  }
}
