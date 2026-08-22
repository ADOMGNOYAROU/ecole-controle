import { NotFoundException } from '@nestjs/common';
import type {
  CollectionReference,
  DocumentReference,
  Firestore,
} from 'firebase-admin/firestore';
import { FieldValue, Timestamp } from 'firebase-admin/firestore';

export function ecoleCollection(
  db: Firestore,
  ecoleId: string,
  nom: string,
): CollectionReference {
  return db.collection('ecoles').doc(ecoleId).collection(nom);
}

export async function getDocOrThrow(ref: DocumentReference, message: string) {
  const snap = await ref.get();
  if (!snap.exists) {
    throw new NotFoundException(message);
  }
  return snap;
}

// Résout l'id du profil enseignant lié au compte connecté (via le champ
// userId synchronisé lors de la génération du compte, cf. UsersService).
export async function trouverEnseignantIdParUid(
  db: Firestore,
  ecoleId: string,
  uid: string,
): Promise<string | null> {
  const snap = await ecoleCollection(db, ecoleId, 'enseignants')
    .where('userId', '==', uid)
    .limit(1)
    .get();
  return snap.empty ? null : snap.docs[0].id;
}

// Reproduit Trimestre::actuel() : le trimestre en cours dans l'année
// scolaire active, ou à défaut le dernier par ordre décroissant.
export async function trouverTrimestreActuelId(
  db: Firestore,
  ecoleId: string,
): Promise<string | null> {
  const anneeSnap = await ecoleCollection(db, ecoleId, 'anneesScolaires')
    .where('active', '==', true)
    .limit(1)
    .get();
  if (anneeSnap.empty) return null;

  const trimestresSnap = await ecoleCollection(db, ecoleId, 'trimestres')
    .where('anneeScolaireId', '==', anneeSnap.docs[0].id)
    .get();
  if (trimestresSnap.empty) return null;

  const maintenant = new Date();
  const enCours = trimestresSnap.docs.find((doc) => {
    const data = doc.data();
    const debut = (
      data['dateDebut'] as FirebaseFirestore.Timestamp | undefined
    )?.toDate();
    const fin = (
      data['dateFin'] as FirebaseFirestore.Timestamp | undefined
    )?.toDate();
    return (
      debut !== undefined &&
      fin !== undefined &&
      debut <= maintenant &&
      fin >= maintenant
    );
  });
  if (enCours) return enCours.id;

  const parOrdreDesc = [...trimestresSnap.docs].sort(
    (a, b) =>
      ((b.data()['ordre'] as number | undefined) ?? 0) -
      ((a.data()['ordre'] as number | undefined) ?? 0),
  );
  return parOrdreDesc[0]?.id ?? null;
}

export function versDate(valeur: unknown): Date | undefined {
  return valeur instanceof Timestamp ? valeur.toDate() : undefined;
}

// Reproduit Ecole::aAccesPremium() de l'app Laravel d'origine. Partagé
// entre PremiumGuard et la tâche de relances de paiement.
export async function ecoleAAccesPremium(
  db: Firestore,
  ecoleId: string,
  ecole: FirebaseFirestore.DocumentData,
): Promise<boolean> {
  if (ecole['statut'] === 'suspendu') return false;
  if (ecole['plan'] !== 'premium') return false;

  const trialEndsAt = versDate(ecole['trialEndsAt']);
  const enEssai =
    ecole['statut'] === 'essai' &&
    trialEndsAt !== undefined &&
    trialEndsAt > new Date();
  if (enEssai) return true;

  // Filtre sur un seul champ pour éviter un index composite Firestore ;
  // le filtre sur dateFin se fait en mémoire (peu de lignes par école).
  const abonnementsActifs = await db
    .collection('ecoles')
    .doc(ecoleId)
    .collection('abonnements')
    .where('statut', '==', 'actif')
    .get();

  const maintenant = new Date();
  return abonnementsActifs.docs.some((doc) => {
    const dateFin = versDate(doc.data()['dateFin']);
    return dateFin !== undefined && dateFin >= maintenant;
  });
}

export async function trouverTuteurIdParUid(
  db: Firestore,
  ecoleId: string,
  uid: string,
): Promise<string | null> {
  const snap = await ecoleCollection(db, ecoleId, 'tuteurs')
    .where('userId', '==', uid)
    .limit(1)
    .get();
  return snap.empty ? null : snap.docs[0].id;
}

export interface NouvelleNotification {
  utilisateurId: string;
  titre: string;
  message: string;
  type: string;
  lien?: string | null;
}

export async function creerNotification(
  db: Firestore,
  ecoleId: string,
  notification: NouvelleNotification,
): Promise<void> {
  await ecoleCollection(db, ecoleId, 'notifications').add({
    utilisateurId: notification.utilisateurId,
    titre: notification.titre,
    message: notification.message,
    type: notification.type,
    lien: notification.lien ?? null,
    lu: false,
    creeLe: FieldValue.serverTimestamp(),
  });
}

// Reproduit Ecole::abonnementActif() : le dernier abonnement actif et non
// expiré, ou null. Filtre sur un seul champ (statut) pour éviter un index
// composite, la comparaison de date_fin se fait en mémoire.
export async function trouverAbonnementActif(
  db: Firestore,
  ecoleId: string,
): Promise<Record<string, unknown> | null> {
  const snap = await ecoleCollection(db, ecoleId, 'abonnements')
    .where('statut', '==', 'actif')
    .get();

  const maintenant = new Date();
  const actifs = snap.docs
    .map((doc) => ({ id: doc.id, ...doc.data() }))
    .filter((abonnement) => {
      const dateFin = versDate(abonnement['dateFin']);
      return dateFin !== undefined && dateFin >= maintenant;
    })
    .sort(
      (a, b) =>
        versDate(b['dateFin'])!.getTime() - versDate(a['dateFin'])!.getTime(),
    );

  return actifs[0] ?? null;
}

export async function trouverEleveIdParUid(
  db: Firestore,
  ecoleId: string,
  uid: string,
): Promise<string | null> {
  const snap = await ecoleCollection(db, ecoleId, 'eleves')
    .where('userId', '==', uid)
    .limit(1)
    .get();
  return snap.empty ? null : snap.docs[0].id;
}
