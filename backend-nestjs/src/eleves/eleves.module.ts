import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { ElevesController } from './eleves.controller';
import { ElevesService } from './eleves.service';

@Module({
  imports: [AuthModule],
  controllers: [ElevesController],
  providers: [ElevesService],
})
export class ElevesModule {}
