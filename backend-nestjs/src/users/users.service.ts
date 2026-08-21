import {
  ConflictException,
  Inject,
  Injectable,
  NotFoundException,
} from '@nestjs/common';
import { randomBytes } from 'crypto';
import type { Auth } from 'firebase-admin/auth';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue } from 'firebase-admin/firestore';
import { AuthenticatedUser } from '../auth/types';
import { FIREBASE_AUTH, FIRESTORE } from '../firebase/firebase.constants';
import { GenererCompteDto, TypeProfil } from './dto/generer-compte.dto';
import { ReinitialiserMotDePasseDto } from './dto/reinitialiser-mot-de-passe.dto';

const SOUS_COLLECTION_PAR_TYPE: Record<TypeProfil, string> = {
  eleve: 'eleves',
  enseignant: 'enseignants',
  tuteur: 'tuteurs',
};

export interface IdentifiantsGeneres {
  name: string;
  email: string;
  password: string;
}

@Injectable()
export class UsersService {
  constructor(
    @Inject(FIRESTORE) private readonly db: Firestore,
    @Inject(FIREBASE_AUTH) private readonly auth: Auth,
  ) {}

  async lister(ecoleId: string) {
    const snap = await this.db
      .collection('users')
      .where('ecoleId', '==', ecoleId)
      .get();
    return snap.docs
      .map(
        (doc) =>
          ({ id: doc.id, ...doc.data() }) as { id: string; name: string },
      )
      .sort((a, b) => a.name.localeCompare(b.name));
  }

  // Reproduit UserAccountController::generer() : crée un compte de connexion
  // à partir d'une fiche élève/enseignant/tuteur déjà existante.
  async genererCompte(
    admin: AuthenticatedUser,
    dto: GenererCompteDto,
  ): Promise<IdentifiantsGeneres> {
    const ecoleId = admin.ecoleId!;
    const collectionName = SOUS_COLLECTION_PAR_TYPE[dto.type];
    const profilRef = this.db
      .collection('ecoles')
      .doc(ecoleId)
      .collection(collectionName)
      .doc(dto.profilId);
    const profilSnap = await profilRef.get();

    if (!profilSnap.exists) {
      throw new NotFoundException('Profil introuvable.');
    }

    const profil = profilSnap.data()!;
    const email: string | undefined =
      dto.emailManuel ?? (profil['email'] as string | undefined);

    if (!email) {
      throw new ConflictException(
        "Cette personne n'a pas d'adresse email. Saisissez-en une manuellement.",
      );
    }

    const motDePasseGenere = !dto.motDePasse;
    const motDePasse = dto.motDePasse ?? this.genererMotDePasse();
    const role = dto.type === 'tuteur' ? 'parent' : dto.type;
    const nomComplet = `${profil['prenom']} ${profil['nom']}`;

    const uid = await this.creerUtilisateurFirebase(
      email,
      motDePasse,
      nomComplet,
      role,
      ecoleId,
    );

    const batch = this.db.batch();
    batch.set(this.db.collection('users').doc(uid), {
      name: nomComplet,
      email,
      role,
      ecoleId,
      phone: null,
      mustChangePassword: motDePasseGenere,
      createdAt: FieldValue.serverTimestamp(),
    });
    batch.update(profilRef, { userId: uid, email });
    await batch.commit();

    return { name: nomComplet, email, password: motDePasse };
  }

  async reinitialiserMotDePasse(
    admin: AuthenticatedUser,
    uid: string,
    dto: ReinitialiserMotDePasseDto,
  ): Promise<IdentifiantsGeneres> {
    const userRef = this.db.collection('users').doc(uid);
    const userSnap = await userRef.get();

    if (!userSnap.exists || userSnap.data()?.['ecoleId'] !== admin.ecoleId) {
      throw new NotFoundException('Compte introuvable.');
    }

    const motDePasseGenere = !dto.nouveauMotDePasse;
    const motDePasse = dto.nouveauMotDePasse ?? this.genererMotDePasse();

    await this.auth.updateUser(uid, { password: motDePasse });
    await userRef.update({ mustChangePassword: motDePasseGenere });

    const user = userSnap.data()!;
    return {
      name: user['name'] as string,
      email: user['email'] as string,
      password: motDePasse,
    };
  }

  async supprimer(admin: AuthenticatedUser, uid: string): Promise<void> {
    const userRef = this.db.collection('users').doc(uid);
    const userSnap = await userRef.get();

    if (!userSnap.exists || userSnap.data()?.['ecoleId'] !== admin.ecoleId) {
      throw new NotFoundException('Compte introuvable.');
    }

    await this.auth.deleteUser(uid);
    await userRef.delete();
  }

  private async creerUtilisateurFirebase(
    email: string,
    motDePasse: string,
    displayName: string,
    role: string,
    ecoleId: string,
  ): Promise<string> {
    try {
      const userRecord = await this.auth.createUser({
        email,
        password: motDePasse,
        displayName,
      });
      await this.auth.setCustomUserClaims(userRecord.uid, { role, ecoleId });
      return userRecord.uid;
    } catch (error) {
      if (
        (error as { code?: string } | null)?.code ===
        'auth/email-already-exists'
      ) {
        throw new ConflictException('Cette adresse email est déjà utilisée.');
      }
      throw error;
    }
  }

  private genererMotDePasse(length = 14): string {
    const charset =
      'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
    return Array.from(randomBytes(length))
      .map((byte) => charset[byte % charset.length])
      .join('');
  }
}
