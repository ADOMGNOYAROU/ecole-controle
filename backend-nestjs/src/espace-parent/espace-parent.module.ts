import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { BulletinsModule } from '../bulletins/bulletins.module';
import { PaiementsModule } from '../paiements/paiements.module';
import { EspaceParentController } from './espace-parent.controller';
import { EspaceParentService } from './espace-parent.service';

@Module({
  imports: [AuthModule, BulletinsModule, PaiementsModule],
  controllers: [EspaceParentController],
  providers: [EspaceParentService],
})
export class EspaceParentModule {}
