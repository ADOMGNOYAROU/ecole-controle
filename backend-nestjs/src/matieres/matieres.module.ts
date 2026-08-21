import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { PdfModule } from '../pdf/pdf.module';
import { MatieresController } from './matieres.controller';
import { MatieresService } from './matieres.service';

@Module({
  imports: [AuthModule, PdfModule],
  controllers: [MatieresController],
  providers: [MatieresService],
})
export class MatieresModule {}
