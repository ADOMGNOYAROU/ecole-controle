import { NotFoundException } from '@nestjs/common';
import type {
  CollectionReference,
  DocumentReference,
  Firestore,
} from 'firebase-admin/firestore';

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
