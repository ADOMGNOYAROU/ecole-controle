import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { PdfModule } from '../pdf/pdf.module';
import { TuteursController } from './tuteurs.controller';
import { TuteursService } from './tuteurs.service';

@Module({
  imports: [AuthModule, PdfModule],
  controllers: [TuteursController],
  providers: [TuteursService],
})
export class TuteursModule {}
