import { Injectable, inject } from '@angular/core';
import {
  User,
  getIdToken,
  getIdTokenResult,
  onAuthStateChanged,
  signInWithCustomToken,
  signInWithEmailAndPassword,
  signOut,
} from 'firebase/auth';
import { Observable, from, shareReplay, switchMap } from 'rxjs';
import { FIREBASE_AUTH } from './firebase.providers';

export interface SessionUser {
  uid: string;
  email: string | null;
  role: string | null;
  ecoleId: string | null;
}

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly auth = inject(FIREBASE_AUTH);

  // Ne s'appuie pas sur un BehaviorSubject pré-rempli à null : on attend la
  // première notification de Firebase (restauration de session comprise)
  // avant de laisser les guards de route décider quoi que ce soit.
  readonly user$: Observable<SessionUser | null> = new Observable<User | null>((subscriber) =>
    onAuthStateChanged(this.auth, (firebaseUser) => subscriber.next(firebaseUser)),
  ).pipe(
    switchMap((firebaseUser) => from(this.versSessionUser(firebaseUser))),
    shareReplay({ bufferSize: 1, refCount: false }),
  );

  async connexion(email: string, motDePasse: string): Promise<void> {
    await signInWithEmailAndPassword(this.auth, email, motDePasse);
  }

  async connexionAvecToken(customToken: string): Promise<void> {
    await signInWithCustomToken(this.auth, customToken);
  }

  async deconnexion(): Promise<void> {
    await signOut(this.auth);
  }

  async jetonActuel(): Promise<string | null> {
    const user = this.auth.currentUser;
    return user ? getIdToken(user) : null;
  }

  private async versSessionUser(firebaseUser: User | null): Promise<SessionUser | null> {
    if (!firebaseUser) {
      return null;
    }

    const resultat = await getIdTokenResult(firebaseUser);
    return {
      uid: firebaseUser.uid,
      email: firebaseUser.email,
      role: (resultat.claims['role'] as string | undefined) ?? null,
      ecoleId: (resultat.claims['ecoleId'] as string | undefined) ?? null,
    };
  }
}
