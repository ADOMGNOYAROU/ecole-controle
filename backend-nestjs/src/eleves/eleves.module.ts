import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { PdfModule } from '../pdf/pdf.module';
import { ElevesController } from './eleves.controller';
import { ElevesService } from './eleves.service';

@Module({
  imports: [AuthModule, PdfModule],
  controllers: [ElevesController],
  providers: [ElevesService],
})
export class ElevesModule {}
