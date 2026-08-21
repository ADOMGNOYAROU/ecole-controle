import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { CreneauxHorairesController } from './creneaux-horaires.controller';
import { CreneauxHorairesService } from './creneaux-horaires.service';

@Module({
  imports: [AuthModule],
  controllers: [CreneauxHorairesController],
  providers: [CreneauxHorairesService],
})
export class CreneauxHorairesModule {}
