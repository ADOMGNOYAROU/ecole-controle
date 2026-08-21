import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { TuteursController } from './tuteurs.controller';
import { TuteursService } from './tuteurs.service';

@Module({
  imports: [AuthModule],
  controllers: [TuteursController],
  providers: [TuteursService],
})
export class TuteursModule {}
