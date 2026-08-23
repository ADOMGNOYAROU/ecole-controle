import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { BulletinsModule } from '../bulletins/bulletins.module';
import { PaiementsModule } from '../paiements/paiements.module';
import { ProgressionModule } from '../progression/progression.module';
import { DashboardController } from './dashboard.controller';
import { DashboardService } from './dashboard.service';

@Module({
  imports: [AuthModule, BulletinsModule, PaiementsModule, ProgressionModule],
  controllers: [DashboardController],
  providers: [DashboardService],
})
export class DashboardModule {}
