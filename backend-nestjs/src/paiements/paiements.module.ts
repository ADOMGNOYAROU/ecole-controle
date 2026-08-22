import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { MailModule } from '../mail/mail.module';
import { PdfModule } from '../pdf/pdf.module';
import { PaiementsController } from './paiements.controller';
import { PaiementsRappelsService } from './paiements-rappels.service';
import { PaiementsService } from './paiements.service';

@Module({
  imports: [AuthModule, PdfModule, MailModule],
  controllers: [PaiementsController],
  providers: [PaiementsService, PaiementsRappelsService],
  exports: [PaiementsService],
})
export class PaiementsModule {}
