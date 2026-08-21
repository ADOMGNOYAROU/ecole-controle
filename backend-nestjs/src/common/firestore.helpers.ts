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
