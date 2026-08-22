import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { BulletinsModule } from '../bulletins/bulletins.module';
import { PaiementsModule } from '../paiements/paiements.module';
import { EspaceEleveController } from './espace-eleve.controller';
import { EspaceEleveService } from './espace-eleve.service';

@Module({
  imports: [AuthModule, BulletinsModule, PaiementsModule],
  controllers: [EspaceEleveController],
  providers: [EspaceEleveService],
})
export class EspaceEleveModule {}
