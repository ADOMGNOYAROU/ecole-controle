import { Global, Module } from '@nestjs/common';
import { ConfigModule, ConfigService } from '@nestjs/config';
import {
  cert,
  getApp,
  getApps,
  initializeApp,
  type App,
  type ServiceAccount,
} from 'firebase-admin/app';
import { getFirestore } from 'firebase-admin/firestore';
import { getAuth } from 'firebase-admin/auth';
import { readFileSync } from 'fs';
import { resolve } from 'path';
import { FIREBASE_APP, FIREBASE_AUTH, FIRESTORE } from './firebase.constants';

@Global()
@Module({
  imports: [ConfigModule],
  providers: [
    {
      provide: FIREBASE_APP,
      inject: [ConfigService],
      useFactory: (config: ConfigService): App => {
        if (getApps().length) {
          return getApp();
        }

        const serviceAccountPath = resolve(
          process.cwd(),
          config.getOrThrow<string>('FIREBASE_SERVICE_ACCOUNT_PATH'),
        );
        const serviceAccount = JSON.parse(
          readFileSync(serviceAccountPath, 'utf-8'),
        ) as ServiceAccount;

        return initializeApp({
          credential: cert(serviceAccount),
          projectId: config.getOrThrow<string>('FIREBASE_PROJECT_ID'),
        });
      },
    },
    {
      provide: FIRESTORE,
      inject: [FIREBASE_APP],
      useFactory: (app: App) => getFirestore(app),
    },
    {
      provide: FIREBASE_AUTH,
      inject: [FIREBASE_APP],
      useFactory: (app: App) => getAuth(app),
    },
  ],
  exports: [FIREBASE_APP, FIRESTORE, FIREBASE_AUTH],
})
export class FirebaseModule {}
