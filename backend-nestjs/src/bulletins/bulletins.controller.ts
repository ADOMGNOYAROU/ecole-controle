import {
  Controller,
  Get,
  Param,
  Post,
  Query,
  Res,
  UseGuards,
} from '@nestjs/common';
import type { Response } from 'express';
import { CurrentUser } from '../auth/current-user.decorator';
import { FirebaseAuthGuard } from '../auth/firebase-auth.guard';
import { Roles } from '../auth/roles.decorator';
import { RolesGuard } from '../auth/roles.guard';
import type { AuthenticatedUser } from '../auth/types';
import { BulletinsService } from './bulletins.service';

@Controller('bulletins')
@UseGuards(FirebaseAuthGuard, RolesGuard)
@Roles('admin', 'enseignant')
export class BulletinsController {
  constructor(private readonly service: BulletinsService) {}

  @Get()
  lister(
    @CurrentUser() user: AuthenticatedUser,
    @Query('classeId') classeId?: string,
    @Query('trimestreId') trimestreId?: string,
  ) {
    return this.service.lister(user.ecoleId!, { classeId, trimestreId });
  }

  @Post('classes/:classeId/trimestres/:trimestreId')
  genererPourClasse(
    @CurrentUser() user: AuthenticatedUser,
    @Param('classeId') classeId: string,
    @Param('trimestreId') trimestreId: string,
  ) {
    return this.service.genererPourClasse(user.ecoleId!, classeId, trimestreId);
  }

  @Get('eleves/:eleveId/trimestres/:trimestreId')
  async telecharger(
    @CurrentUser() user: AuthenticatedUser,
    @Param('eleveId') eleveId: string,
    @Param('trimestreId') trimestreId: string,
    @Res() res: Response,
  ): Promise<void> {
    const pdf = await this.service.genererPdf(
      user.ecoleId!,
      eleveId,
      trimestreId,
    );
    res.setHeader('Content-Type', 'application/pdf');
    res.setHeader(
      'Content-Disposition',
      `attachment; filename="bulletin-${eleveId}-${trimestreId}.pdf"`,
    );
    res.send(pdf);
  }
}
