import { Module } from '@nestjs/common';
import { AuthController } from './auth.controller';
import { FirebaseAuthGuard } from './firebase-auth.guard';
import { RolesGuard } from './roles.guard';
import { PremiumGuard } from './premium.guard';

@Module({
  controllers: [AuthController],
  providers: [FirebaseAuthGuard, RolesGuard, PremiumGuard],
  exports: [FirebaseAuthGuard, RolesGuard, PremiumGuard],
})
export class AuthModule {}
