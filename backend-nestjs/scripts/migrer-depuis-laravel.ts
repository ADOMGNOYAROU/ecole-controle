// Migration ponctuelle des données de l'app Laravel (SQLite, cf. .env
// DB_CONNECTION de school-management-api) vers Firestore, en respectant
// exactement les conventions de champs (Timestamp vs chaîne de date) déjà
// utilisées par chaque module NestJS — voir firestore-schema.md.
//
// Ce script est idempotent pour les comptes utilisateurs (une adresse email
// déjà présente dans Firebase Auth est réutilisée plutôt que dupliquée),
// mais PAS pour le reste des données : le relancer recrée une deuxième
// fois les écoles/classes/notes/etc. Ne l'exécuter qu'une fois par base
// source, ou nettoyer Firestore entre deux essais.
//
// Les mots de passe existants sont préservés tels quels (hash bcrypt
// importé directement dans Firebase Auth) : personne n'a besoin de
// réinitialiser son mot de passe après la bascule.
//
// La table `bulletins` n'est volontairement pas migrée : les bulletins
// sont recalculés à la demande par BulletinsService.obtenirOuGenerer() à
// partir des notes/présences migrées, exactement comme pour un élève dont
// le bulletin n'a jamais encore été consulté.
// La table `responsabilites` n'est pas migrée non plus : elle n'est reliée
// à aucune route dans l'app Laravel (aucun contrôleur ne la lit/l'écrit),
// ce n'est donc pas une fonctionnalité réellement exposée à migrer.
//
// Usage : npm run migrer-depuis-laravel -- <chemin-vers-database.sqlite>

import Database from 'better-sqlite3';
import { cert, initializeApp } from 'firebase-admin/app';
import { getAuth, type Auth, type UserImportRecord } from 'firebase-admin/auth';
import {
  FieldValue,
  getFirestore,
  type Firestore,
} from 'firebase-admin/firestore';
import { readFileSync } from 'fs';
import { resolve } from 'path';

type Row = Record<string, unknown>;

function versDate(valeur: unknown): Date | null {
  if (!valeur || typeof valeur !== 'string') return null;
  return new Date(valeur.replace(' ', 'T'));
}

function versBool(valeur: unknown): boolean {
  return valeur === 1 || valeur === true;
}

function versHeure(valeur: unknown): string {
  return typeof valeur === 'string' ? valeur.slice(0, 5) : '';
}

// Reproduit PaiementsService.calculerStatut : le statut peut avoir évolué
// depuis l'export Laravel (le temps a passé entre export et import).
function calculerStatutPaiement(
  montant: number,
  montantPaye: number,
  dateEcheance: string,
): string {
  if (montantPaye >= montant && montant > 0) return 'paye';
  if (montantPaye > 0) return 'partiel';
  return new Date() > new Date(dateEcheance) ? 'retard' : 'en_attente';
}

function initFirebase(): { db: Firestore; auth: Auth } {
  const serviceAccountPath = resolve(
    process.cwd(),
    process.env.FIREBASE_SERVICE_ACCOUNT_PATH!,
  );
  const serviceAccount = JSON.parse(
    readFileSync(serviceAccountPath, 'utf-8'),
  );
  const app = initializeApp({
    credential: cert(serviceAccount),
    projectId: process.env.FIREBASE_PROJECT_ID,
  });
  return { db: getFirestore(app), auth: getAuth(app) };
}

async function main(): Promise<void> {
  const cheminSqlite = process.argv[2];
  if (!cheminSqlite) {
    console.error(
      'Usage: npm run migrer-depuis-laravel -- <chemin-vers-database.sqlite>',
    );
    process.exit(1);
  }

  const sqlite = new Database(cheminSqlite, { readonly: true });
  const { db, auth } = initFirebase();

  const idEcoles = new Map<number, string>();
  const idUsers = new Map<number, string>(); // Laravel users.id -> Firebase uid
  const idAnnees = new Map<number, string>();
  const idTrimestres = new Map<number, string>();
  const idClasses = new Map<number, string>();
  const idMatieres = new Map<number, string>();
  const idEnseignants = new Map<number, string>();
  const idEleves = new Map<number, string>();
  const idTuteurs = new Map<number, string>();
  const idAbonnements = new Map<number, string>();

  const lignes = <T extends Row = Row>(sql: string): T[] =>
    sqlite.prepare(sql).all() as T[];

  console.log('=== 1. Écoles ===');
  for (const ecole of lignes('SELECT * FROM ecoles')) {
    const ref = db.collection('ecoles').doc();
    await ref.set({
      nom: ecole.nom,
      slug: ecole.slug,
      emailContact: ecole.email_contact ?? null,
      telephone: ecole.telephone ?? null,
      ville: ecole.ville ?? null,
      statut: ecole.statut,
      plan: ecole.plan,
      trialEndsAt: versDate(ecole.trial_ends_at),
      createdAt: versDate(ecole.created_at) ?? FieldValue.serverTimestamp(),
    });
    idEcoles.set(ecole.id as number, ref.id);
  }
  console.log(`  ${idEcoles.size} école(s) migrée(s).`);

  console.log('=== 2. Années scolaires ===');
  for (const annee of lignes('SELECT * FROM annees_scolaires')) {
    const ecoleId = idEcoles.get(annee.ecole_id as number)!;
    const ref = db.collection('ecoles').doc(ecoleId).collection('anneesScolaires').doc();
    await ref.set({
      libelle: annee.libelle,
      dateDebut: versDate(annee.date_debut),
      dateFin: versDate(annee.date_fin),
      active: versBool(annee.active),
      createdAt: versDate(annee.created_at) ?? FieldValue.serverTimestamp(),
    });
    idAnnees.set(annee.id as number, ref.id);
  }
  console.log(`  ${idAnnees.size} année(s) scolaire(s) migrée(s).`);

  console.log('=== 3. Trimestres ===');
  for (const trimestre of lignes('SELECT * FROM trimestres')) {
    const ecoleId = idEcoles.get(trimestre.ecole_id as number)!;
    const ref = db.collection('ecoles').doc(ecoleId).collection('trimestres').doc();
    await ref.set({
      anneeScolaireId: idAnnees.get(trimestre.annee_scolaire_id as number)!,
      nom: trimestre.nom,
      ordre: trimestre.ordre,
      dateDebut: versDate(trimestre.date_debut),
      dateFin: versDate(trimestre.date_fin),
      createdAt: versDate(trimestre.created_at) ?? FieldValue.serverTimestamp(),
    });
    idTrimestres.set(trimestre.id as number, ref.id);
  }
  console.log(`  ${idTrimestres.size} trimestre(s) migré(s).`);

  console.log('=== 4. Matières ===');
  for (const matiere of lignes('SELECT * FROM matieres')) {
    const ecoleId = idEcoles.get(matiere.ecole_id as number)!;
    const ref = db.collection('ecoles').doc(ecoleId).collection('matieres').doc();
    await ref.set({
      nom: matiere.nom,
      code: matiere.code,
      coefficientDefaut: matiere.coefficient_defaut,
      createdAt: versDate(matiere.created_at) ?? FieldValue.serverTimestamp(),
    });
    idMatieres.set(matiere.id as number, ref.id);
  }
  console.log(`  ${idMatieres.size} matière(s) migrée(s).`);

  console.log('=== 5. Enseignants (sans classeIds/matiereIds pour le moment) ===');
  for (const enseignant of lignes('SELECT * FROM enseignants')) {
    const ecoleId = idEcoles.get(enseignant.ecole_id as number)!;
    const ref = db.collection('ecoles').doc(ecoleId).collection('enseignants').doc();
    await ref.set({
      nom: enseignant.nom,
      prenom: enseignant.prenom,
      telephone: enseignant.telephone ?? null,
      email: enseignant.email ?? null,
      specialite: enseignant.specialite ?? null,
      dateEmbauche: versDate(enseignant.date_embauche),
      matiereIds: [],
      classeIds: [],
      userId: null,
      createdAt: versDate(enseignant.created_at) ?? FieldValue.serverTimestamp(),
    });
    idEnseignants.set(enseignant.id as number, ref.id);
  }
  console.log(`  ${idEnseignants.size} enseignant(s) migré(s).`);

  console.log('=== 6. Classes ===');
  for (const classe of lignes('SELECT * FROM classes')) {
    const ecoleId = idEcoles.get(classe.ecole_id as number)!;
    const ref = db.collection('ecoles').doc(ecoleId).collection('classes').doc();
    await ref.set({
      nom: classe.nom,
      niveau: classe.niveau ?? null,
      anneeScolaireId: idAnnees.get(classe.annee_scolaire_id as number)!,
      enseignantPrincipalId: classe.enseignant_principal_id
        ? (idEnseignants.get(classe.enseignant_principal_id as number) ?? null)
        : null,
      capacite: classe.capacite ?? null,
      createdAt: versDate(classe.created_at) ?? FieldValue.serverTimestamp(),
    });
    idClasses.set(classe.id as number, ref.id);
  }
  console.log(`  ${idClasses.size} classe(s) migrée(s).`);

  console.log('=== 7. Élèves (sans userId/tuteurIds pour le moment) ===');
  for (const eleve of lignes('SELECT * FROM eleves')) {
    const ecoleId = idEcoles.get(eleve.ecole_id as number)!;
    const ref = db.collection('ecoles').doc(ecoleId).collection('eleves').doc();
    await ref.set({
      matricule: eleve.matricule,
      nom: eleve.nom,
      prenom: eleve.prenom,
      sexe: eleve.sexe,
      dateNaissance: versDate(eleve.date_naissance),
      lieuNaissance: eleve.lieu_naissance ?? null,
      adresse: eleve.adresse ?? null,
      telephone: eleve.telephone ?? null,
      email: eleve.email ?? null,
      classeId: eleve.classe_id
        ? (idClasses.get(eleve.classe_id as number) ?? null)
        : null,
      statut: eleve.statut,
      dateInscription: versDate(eleve.date_inscription),
      contactUrgenceNom: eleve.contact_urgence_nom ?? null,
      contactUrgenceTelephone: eleve.contact_urgence_telephone ?? null,
      tuteurIds: [],
      userId: null,
      createdAt: versDate(eleve.created_at) ?? FieldValue.serverTimestamp(),
    });
    idEleves.set(eleve.id as number, ref.id);
  }
  console.log(`  ${idEleves.size} élève(s) migré(s).`);

  console.log('=== 8. Tuteurs (+ retour tuteurIds sur les élèves) ===');
  const tuteursParEleve = new Map<number, { eleve_id: number; tuteur_id: number; lien_parente: string }[]>();
  for (const lien of lignes<{ eleve_id: number; tuteur_id: number; lien_parente: string }>(
    'SELECT * FROM eleve_tuteur',
  )) {
    const liste = tuteursParEleve.get(lien.tuteur_id) ?? [];
    liste.push(lien);
    tuteursParEleve.set(lien.tuteur_id, liste);
  }

  for (const tuteur of lignes('SELECT * FROM tuteurs')) {
    const ecoleId = idEcoles.get(tuteur.ecole_id as number)!;
    const ref = db.collection('ecoles').doc(ecoleId).collection('tuteurs').doc();
    const liens = tuteursParEleve.get(tuteur.id as number) ?? [];
    const eleves = liens
      .map((lien) => ({
        id: idEleves.get(lien.eleve_id),
        lienParente: lien.lien_parente,
      }))
      .filter((e): e is { id: string; lienParente: string } => !!e.id);

    await ref.set({
      nom: tuteur.nom,
      prenom: tuteur.prenom,
      telephone: tuteur.telephone,
      email: tuteur.email ?? null,
      profession: tuteur.profession ?? null,
      adresse: tuteur.adresse ?? null,
      eleves,
      userId: null,
      createdAt: versDate(tuteur.created_at) ?? FieldValue.serverTimestamp(),
    });
    idTuteurs.set(tuteur.id as number, ref.id);

    for (const { id: eleveId } of eleves) {
      await db
        .collection('ecoles')
        .doc(ecoleId)
        .collection('eleves')
        .doc(eleveId)
        .update({ tuteurIds: FieldValue.arrayUnion(ref.id) });
    }
  }
  console.log(`  ${idTuteurs.size} tuteur(s) migré(s).`);

  console.log('=== 9. Enseignants : classeIds / matiereIds (pivot) ===');
  const pivotParEnseignant = new Map<number, { matiere_id: number; classe_id: number }[]>();
  for (const p of lignes<{ enseignant_id: number; matiere_id: number; classe_id: number }>(
    'SELECT * FROM enseignant_matiere_classe',
  )) {
    const liste = pivotParEnseignant.get(p.enseignant_id) ?? [];
    liste.push(p);
    pivotParEnseignant.set(p.enseignant_id, liste);
  }
  for (const [enseignantIdLaravel, liste] of pivotParEnseignant) {
    const enseignantId = idEnseignants.get(enseignantIdLaravel);
    if (!enseignantId) continue;
    const ecoleRow = lignes<{ ecole_id: number }>(
      `SELECT ecole_id FROM enseignants WHERE id = ${enseignantIdLaravel}`,
    )[0];
    const ecoleId = idEcoles.get(ecoleRow.ecole_id)!;
    const matiereIds = [...new Set(liste.map((p) => idMatieres.get(p.matiere_id)).filter((v): v is string => !!v))];
    const classeIds = [...new Set(liste.map((p) => idClasses.get(p.classe_id)).filter((v): v is string => !!v))];
    await db
      .collection('ecoles')
      .doc(ecoleId)
      .collection('enseignants')
      .doc(enseignantId)
      .update({ matiereIds, classeIds });
  }
  console.log(`  ${pivotParEnseignant.size} enseignant(s) mis à jour avec leurs matières/classes.`);

  console.log('=== 10. Utilisateurs (Firebase Auth, hash bcrypt préservé) ===');
  const usersRows = lignes<Row & { id: number; role: string; ecole_id: number | null; email: string; password: string }>(
    'SELECT * FROM users',
  );
  const aImporter: UserImportRecord[] = [];
  const nouveauxParLaravelId = new Map<number, string>();

  for (const u of usersRows) {
    if (u.role === 'super_admin') {
      console.log(`  Ignoré (super_admin, à créer via npm run creer-super-admin) : ${u.email}`);
      continue;
    }
    const ecoleId = idEcoles.get(u.ecole_id as number);
    if (!ecoleId) {
      console.log(`  Ignoré (école non trouvée) : ${u.email}`);
      continue;
    }

    const existant = await auth.getUserByEmail(u.email as string).catch(() => null);
    if (existant) {
      console.log(`  Déjà présent dans Firebase Auth, réutilisé : ${u.email}`);
      idUsers.set(u.id, existant.uid);
      await db.collection('users').doc(existant.uid).set(
        {
          name: u.name,
          email: u.email,
          role: u.role,
          ecoleId,
          phone: u.phone ?? null,
          mustChangePassword: versBool(u.must_change_password),
          createdAt: versDate(u.created_at) ?? FieldValue.serverTimestamp(),
        },
        { merge: true },
      );
      continue;
    }

    const uid = db.collection('_').doc().id;
    nouveauxParLaravelId.set(u.id, uid);
    idUsers.set(u.id, uid);
    aImporter.push({
      uid,
      email: u.email as string,
      displayName: u.name as string,
      passwordHash: Buffer.from(u.password as string),
      customClaims: { role: u.role, ecoleId },
    });
  }

  if (aImporter.length > 0) {
    const resultat = await auth.importUsers(aImporter, { hash: { algorithm: 'BCRYPT' } });
    if (resultat.failureCount > 0) {
      console.error('  Échecs d\'import Auth :', JSON.stringify(resultat.errors, null, 2));
    }
    for (const u of usersRows) {
      const uid = nouveauxParLaravelId.get(u.id);
      if (!uid) continue;
      const ecoleId = idEcoles.get(u.ecole_id as number)!;
      await db.collection('users').doc(uid).set({
        name: u.name,
        email: u.email,
        role: u.role,
        ecoleId,
        phone: u.phone ?? null,
        mustChangePassword: versBool(u.must_change_password),
        createdAt: versDate(u.created_at) ?? FieldValue.serverTimestamp(),
      });
    }
    console.log(`  ${aImporter.length} nouveau(x) compte(s) importé(s) avec mot de passe préservé.`);
  }

  console.log('=== 11. Retour userId sur enseignants/élèves/tuteurs ===');
  for (const enseignant of lignes<{ id: number; ecole_id: number; user_id: number | null }>(
    'SELECT id, ecole_id, user_id FROM enseignants WHERE user_id IS NOT NULL',
  )) {
    const uid = idUsers.get(enseignant.user_id!);
    const enseignantId = idEnseignants.get(enseignant.id);
    if (!uid || !enseignantId) continue;
    await db
      .collection('ecoles')
      .doc(idEcoles.get(enseignant.ecole_id)!)
      .collection('enseignants')
      .doc(enseignantId)
      .update({ userId: uid });
  }
  for (const eleve of lignes<{ id: number; ecole_id: number; user_id: number | null }>(
    'SELECT id, ecole_id, user_id FROM eleves WHERE user_id IS NOT NULL',
  )) {
    const uid = idUsers.get(eleve.user_id!);
    const eleveId = idEleves.get(eleve.id);
    if (!uid || !eleveId) continue;
    await db
      .collection('ecoles')
      .doc(idEcoles.get(eleve.ecole_id)!)
      .collection('eleves')
      .doc(eleveId)
      .update({ userId: uid });
  }
  for (const tuteur of lignes<{ id: number; ecole_id: number; user_id: number | null }>(
    'SELECT id, ecole_id, user_id FROM tuteurs WHERE user_id IS NOT NULL',
  )) {
    const uid = idUsers.get(tuteur.user_id!);
    const tuteurId = idTuteurs.get(tuteur.id);
    if (!uid || !tuteurId) continue;
    await db
      .collection('ecoles')
      .doc(idEcoles.get(tuteur.ecole_id)!)
      .collection('tuteurs')
      .doc(tuteurId)
      .update({ userId: uid });
  }
  console.log('  Terminé.');

  console.log('=== 12. Créneaux horaires ===');
  let nbCreneaux = 0;
  for (const creneau of lignes('SELECT * FROM creneaux_horaires')) {
    const ecoleId = idEcoles.get(creneau.ecole_id as number)!;
    await db.collection('ecoles').doc(ecoleId).collection('creneauxHoraires').add({
      classeId: idClasses.get(creneau.classe_id as number)!,
      matiereId: idMatieres.get(creneau.matiere_id as number)!,
      enseignantId: creneau.enseignant_id
        ? (idEnseignants.get(creneau.enseignant_id as number) ?? null)
        : null,
      jourSemaine: creneau.jour_semaine,
      heureDebut: versHeure(creneau.heure_debut),
      heureFin: versHeure(creneau.heure_fin),
      salle: creneau.salle ?? null,
      createdAt: versDate(creneau.created_at) ?? FieldValue.serverTimestamp(),
    });
    nbCreneaux += 1;
  }
  console.log(`  ${nbCreneaux} créneau(x) migré(s).`);

  console.log('=== 13. Notes ===');
  let nbNotes = 0;
  for (const note of lignes('SELECT * FROM notes')) {
    const ecoleId = idEcoles.get(note.ecole_id as number)!;
    await db.collection('ecoles').doc(ecoleId).collection('notes').add({
      eleveId: idEleves.get(note.eleve_id as number)!,
      matiereId: idMatieres.get(note.matiere_id as number)!,
      classeId: idClasses.get(note.classe_id as number)!,
      trimestreId: idTrimestres.get(note.trimestre_id as number)!,
      enseignantId: idEnseignants.get(note.enseignant_id as number) ?? null,
      type: note.type,
      valeur: note.valeur,
      bareme: note.bareme,
      coefficient: note.coefficient,
      dateEvaluation: note.date_evaluation,
      commentaire: note.commentaire ?? null,
    });
    nbNotes += 1;
  }
  console.log(`  ${nbNotes} note(s) migrée(s).`);

  console.log('=== 14. Présences ===');
  let nbPresences = 0;
  for (const presence of lignes('SELECT * FROM presences')) {
    const ecoleId = idEcoles.get(presence.ecole_id as number)!;
    const eleveId = idEleves.get(presence.eleve_id as number)!;
    const id = `${eleveId}_${presence.date as string}`;
    await db.collection('ecoles').doc(ecoleId).collection('presences').doc(id).set({
      eleveId,
      classeId: idClasses.get(presence.classe_id as number)!,
      enseignantId: presence.enseignant_id
        ? (idEnseignants.get(presence.enseignant_id as number) ?? null)
        : null,
      trimestreId: presence.trimestre_id
        ? (idTrimestres.get(presence.trimestre_id as number) ?? null)
        : null,
      date: presence.date,
      statut: presence.statut,
      motif: presence.motif ?? null,
    });
    nbPresences += 1;
  }
  console.log(`  ${nbPresences} présence(s) migrée(s).`);

  console.log('=== 15. Paiements ===');
  let nbPaiements = 0;
  for (const paiement of lignes('SELECT * FROM paiements')) {
    const ecoleId = idEcoles.get(paiement.ecole_id as number)!;
    const montant = paiement.montant as number;
    const montantPaye = (paiement.montant_paye as number) ?? 0;
    const dateEcheance = paiement.date_echeance as string;
    await db.collection('ecoles').doc(ecoleId).collection('paiements').add({
      eleveId: idEleves.get(paiement.eleve_id as number)!,
      anneeScolaireId: idAnnees.get(paiement.annee_scolaire_id as number)!,
      type: paiement.type,
      montant,
      montantPaye,
      dateEcheance,
      datePaiement: paiement.date_paiement ?? null,
      commentaire: paiement.commentaire ?? null,
      statut: calculerStatutPaiement(montant, montantPaye, dateEcheance),
      dernierRappelLe: versDate(paiement.dernier_rappel_le),
      createdAt: versDate(paiement.created_at) ?? FieldValue.serverTimestamp(),
    });
    nbPaiements += 1;
  }
  console.log(`  ${nbPaiements} paiement(s) migré(s).`);

  console.log('=== 16. Notifications ===');
  let nbNotifications = 0;
  for (const notif of lignes<Row & { user_id: number }>('SELECT * FROM notifications')) {
    const utilisateurId = idUsers.get(notif.user_id);
    if (!utilisateurId) continue;
    const userRow = lignes<{ ecole_id: number }>(
      `SELECT ecole_id FROM users WHERE id = ${notif.user_id}`,
    )[0];
    const ecoleId = idEcoles.get(userRow.ecole_id);
    if (!ecoleId) continue;
    await db.collection('ecoles').doc(ecoleId).collection('notifications').add({
      utilisateurId,
      titre: notif.titre,
      message: notif.message,
      type: notif.type ?? null,
      lien: notif.lien ?? null,
      lu: versBool(notif.lu),
      creeLe: versDate(notif.created_at) ?? FieldValue.serverTimestamp(),
    });
    nbNotifications += 1;
  }
  console.log(`  ${nbNotifications} notification(s) migrée(s).`);

  console.log('=== 17. Messages ===');
  let nbMessages = 0;
  for (const message of lignes('SELECT * FROM messages')) {
    const expediteurId = idUsers.get(message.expediteur_id as number);
    const destinataireId = idUsers.get(message.destinataire_id as number);
    if (!expediteurId || !destinataireId) continue;
    const ecoleId = idEcoles.get(message.ecole_id as number)!;
    await db.collection('ecoles').doc(ecoleId).collection('messages').add({
      expediteurId,
      destinataireId,
      contenu: message.contenu,
      lu: versBool(message.lu),
      createdAt: versDate(message.created_at) ?? FieldValue.serverTimestamp(),
    });
    nbMessages += 1;
  }
  console.log(`  ${nbMessages} message(s) migré(s).`);

  console.log('=== 18. Annonces ===');
  let nbAnnonces = 0;
  for (const annonce of lignes('SELECT * FROM annonces')) {
    const auteurId = idUsers.get(annonce.auteur_id as number);
    if (!auteurId) continue;
    const ecoleId = idEcoles.get(annonce.ecole_id as number)!;
    await db.collection('ecoles').doc(ecoleId).collection('annonces').add({
      titre: annonce.titre,
      contenu: annonce.contenu,
      cible: annonce.cible,
      classeId: annonce.classe_id
        ? (idClasses.get(annonce.classe_id as number) ?? null)
        : null,
      auteurId,
      datePublication: versDate(annonce.date_publication) ?? FieldValue.serverTimestamp(),
    });
    nbAnnonces += 1;
  }
  console.log(`  ${nbAnnonces} annonce(s) migrée(s).`);

  console.log('=== 19. Abonnements ===');
  for (const abonnement of lignes('SELECT * FROM abonnements')) {
    const ecoleId = idEcoles.get(abonnement.ecole_id as number)!;
    const ref = db.collection('ecoles').doc(ecoleId).collection('abonnements').doc();
    await ref.set({
      plan: abonnement.plan,
      dateDebut: versDate(abonnement.date_debut),
      dateFin: versDate(abonnement.date_fin),
      statut: abonnement.statut,
      montant: abonnement.montant,
    });
    idAbonnements.set(abonnement.id as number, ref.id);
  }
  console.log(`  ${idAbonnements.size} abonnement(s) migré(s).`);

  console.log('=== 20. Factures ===');
  let nbFactures = 0;
  for (const facture of lignes('SELECT * FROM factures')) {
    const ecoleId = idEcoles.get(facture.ecole_id as number)!;
    const confirmeeParId = facture.confirmee_par_id
      ? (idUsers.get(facture.confirmee_par_id as number) ?? null)
      : null;
    await db.collection('ecoles').doc(ecoleId).collection('factures').add({
      montant: facture.montant,
      dateEcheance: versDate(facture.date_echeance),
      statut: facture.statut,
      abonnementId: facture.abonnement_id
        ? (idAbonnements.get(facture.abonnement_id as number) ?? null)
        : null,
      methodePaiement: facture.methode_paiement ?? null,
      referenceTransaction: facture.reference_transaction ?? null,
      payeeLe: versDate(facture.payee_le),
      confirmeeParId,
      createdAt: versDate(facture.created_at) ?? FieldValue.serverTimestamp(),
    });
    nbFactures += 1;
  }
  console.log(`  ${nbFactures} facture(s) migrée(s).`);

  sqlite.close();
  console.log('\n=== Migration terminée ===');
}

main()
  .then(() => process.exit(0))
  .catch((error: unknown) => {
    console.error(error);
    process.exit(1);
  });
