import { Module } from '@nestjs/common';
import { AuthModule } from '../auth/auth.module';
import { MessagerieController } from './messagerie.controller';
import { MessagerieService } from './messagerie.service';

@Module({
  imports: [AuthModule],
  controllers: [MessagerieController],
  providers: [MessagerieService],
})
export class MessagerieModule {}
