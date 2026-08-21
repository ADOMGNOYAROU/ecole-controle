import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { BulletinsModule } from '../bulletins/bulletins.module';
import { ProgressionController } from './progression.controller';
import { ProgressionService } from './progression.service';

@Module({
  imports: [AuthModule, BulletinsModule],
  controllers: [ProgressionController],
  providers: [ProgressionService],
})
export class ProgressionModule {}
