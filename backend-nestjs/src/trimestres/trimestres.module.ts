import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { TrimestresController } from './trimestres.controller';
import { TrimestresService } from './trimestres.service';

@Module({
  imports: [AuthModule],
  controllers: [TrimestresController],
  providers: [TrimestresService],
})
export class TrimestresModule {}
