import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { EcolesController } from './ecoles.controller';
import { EcolesService } from './ecoles.service';

@Module({
  imports: [AuthModule],
  controllers: [EcolesController],
  providers: [EcolesService],
})
export class EcolesModule {}
