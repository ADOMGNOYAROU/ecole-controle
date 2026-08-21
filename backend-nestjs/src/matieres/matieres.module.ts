import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { MatieresController } from './matieres.controller';
import { MatieresService } from './matieres.service';

@Module({
  imports: [AuthModule],
  controllers: [MatieresController],
  providers: [MatieresService],
})
export class MatieresModule {}
