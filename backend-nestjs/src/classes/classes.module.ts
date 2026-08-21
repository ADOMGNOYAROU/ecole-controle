import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { PdfModule } from '../pdf/pdf.module';
import { ClassesController } from './classes.controller';
import { ClassesService } from './classes.service';

@Module({
  imports: [AuthModule, PdfModule],
  controllers: [ClassesController],
  providers: [ClassesService],
})
export class ClassesModule {}
