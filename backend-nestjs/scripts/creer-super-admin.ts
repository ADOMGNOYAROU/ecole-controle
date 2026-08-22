// Bootstrap manuel du tout premier compte super-admin, reproduisant le rôle
// de la commande Artisan CreateAdminUser / du seeder Laravel (il n'existe
// aucun flux applicatif pour créer un super-admin : c'est volontaire, comme
// dans l'app d'origine, où seul un accès direct au serveur le permet).
//
// Usage : npm run creer-super-admin -- <email> <mot-de-passe> [nom]

import { cert, initializeApp, type ServiceAccount } from 'firebase-admin/app';
import { getAuth } from 'firebase-admin/auth';
import { FieldValue, getFirestore } from 'firebase-admin/firestore';
import { readFileSync } from 'fs';
import { resolve } from 'path';

async function main(): Promise<void> {
  const [, , email, password, nom] = process.argv;
  if (!email || !password) {
    console.error(
      'Usage: npm run creer-super-admin -- <email> <mot-de-passe> [nom]',
    );
    process.exit(1);
  }

  const serviceAccountPath = resolve(
    process.cwd(),
    process.env.FIREBASE_SERVICE_ACCOUNT_PATH!,
  );
  const serviceAccount = JSON.parse(
    readFileSync(serviceAccountPath, 'utf-8'),
  ) as ServiceAccount;

  const app = initializeApp({
    credential: cert(serviceAccount),
    projectId: process.env.FIREBASE_PROJECT_ID,
  });
  const auth = getAuth(app);
  const db = getFirestore(app);

  const userRecord = await auth.createUser({
    email,
    password,
    displayName: nom ?? email,
  });
  await auth.setCustomUserClaims(userRecord.uid, {
    role: 'super_admin',
    ecoleId: null,
  });
  await db.collection('users').doc(userRecord.uid).set({
    name: nom ?? email,
    email,
    role: 'super_admin',
    ecoleId: null,
    phone: null,
    mustChangePassword: false,
    createdAt: FieldValue.serverTimestamp(),
  });

  console.log(`Super-admin créé : ${email} (uid ${userRecord.uid})`);
  process.exit(0);
}

main().catch((error: unknown) => {
  console.error(error);
  process.exit(1);
});
