import { Module } from '@nestjs/common';
import { ConfigModule } from '@nestjs/config';
import { ScheduleModule } from '@nestjs/schedule';
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
import { CreneauxHorairesModule } from './creneaux-horaires/creneaux-horaires.module';
import { NotesModule } from './notes/notes.module';
import { PresencesModule } from './presences/presences.module';
import { PdfModule } from './pdf/pdf.module';
import { BulletinsModule } from './bulletins/bulletins.module';
import { ProgressionModule } from './progression/progression.module';
import { MailModule } from './mail/mail.module';
import { PaiementsModule } from './paiements/paiements.module';

@Module({
  imports: [
    ConfigModule.forRoot({ isGlobal: true }),
    ScheduleModule.forRoot(),
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
    CreneauxHorairesModule,
    NotesModule,
    PresencesModule,
    PdfModule,
    BulletinsModule,
    ProgressionModule,
    MailModule,
    PaiementsModule,
  ],
  controllers: [AppController],
  providers: [AppService],
})
export class AppModule {}
