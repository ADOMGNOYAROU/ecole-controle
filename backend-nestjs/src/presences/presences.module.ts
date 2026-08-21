import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { PdfModule } from '../pdf/pdf.module';
import { PresencesController } from './presences.controller';
import { PresencesService } from './presences.service';

@Module({
  imports: [AuthModule, PdfModule],
  controllers: [PresencesController],
  providers: [PresencesService],
})
export class PresencesModule {}
