// Point d'entrée pour le déploiement en Cloud Functions (2ᵉ génération),
// séparé de main.ts qui reste le point d'entrée du serveur local classique
// (npm run start:dev). Les deux partagent le même AppModule.
import { ValidationPipe } from '@nestjs/common';
import { NestFactory } from '@nestjs/core';
import { ExpressAdapter } from '@nestjs/platform-express';
import express from 'express';
import { setGlobalOptions } from 'firebase-functions/v2';
import { onRequest } from 'firebase-functions/v2/https';
import { AppModule } from './app.module';

setGlobalOptions({ region: 'europe-west1', memory: '512MiB' });

const server = express();
let pretIl: Promise<void> | null = null;

async function demarrerNest(): Promise<void> {
  const app = await NestFactory.create(AppModule, new ExpressAdapter(server));
  app.enableCors();
  app.useGlobalPipes(new ValidationPipe({ whitelist: true, transform: true }));
  await app.init();
}

// Une seule fonction HTTPS exposant toute l'API NestJS (routes déjà
// préfixées par module, ex. /eleves, /super-admin/ecoles) : la même
// approche que le serveur Express local, mais hébergée en Cloud Function.
export const api = onRequest(async (req, res) => {
  pretIl ??= demarrerNest();
  await pretIl;
  server(req, res);
});
