import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { AnneesScolairesController } from './annees-scolaires.controller';
import { AnneesScolairesService } from './annees-scolaires.service';

@Module({
  imports: [AuthModule],
  controllers: [AnneesScolairesController],
  providers: [AnneesScolairesService],
  exports: [AnneesScolairesService],
})
export class AnneesScolairesModule {}
