import { Module } from '@nestjs/common';
import { ConfigModule } from '@nestjs/config';
import { AppController } from './app.controller';
import { AppService } from './app.service';
import { FirebaseModule } from './firebase/firebase.module';
import { AuthModule } from './auth/auth.module';
import { EcolesModule } from './ecoles/ecoles.module';
import { UsersModule } from './users/users.module';
import { AnneesScolairesModule } from './annees-scolaires/annees-scolaires.module';
import { TrimestresModule } from './trimestres/trimestres.module';
import { ClassesModule } from './classes/classes.module';
import { MatieresModule } from './matieres/matieres.module';
import { TuteursModule } from './tuteurs/tuteurs.module';
import { EnseignantsModule } from './enseignants/enseignants.module';
import { ElevesModule } from './eleves/eleves.module';

@Module({
  imports: [
    ConfigModule.forRoot({ isGlobal: true }),
    FirebaseModule,
    AuthModule,
    EcolesModule,
    UsersModule,
    AnneesScolairesModule,
    TrimestresModule,
    ClassesModule,
    MatieresModule,
    TuteursModule,
    EnseignantsModule,
    ElevesModule,
  ],
  controllers: [AppController],
  providers: [AppService],
})
export class AppModule {}
