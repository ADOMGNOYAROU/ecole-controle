import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { PdfModule } from '../pdf/pdf.module';
import { EnseignantsController } from './enseignants.controller';
import { EnseignantsService } from './enseignants.service';

@Module({
  imports: [AuthModule, PdfModule],
  controllers: [EnseignantsController],
  providers: [EnseignantsService],
})
export class EnseignantsModule {}
