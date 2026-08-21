import {
  ConflictException,
  Inject,
  Injectable,
  NotFoundException,
} from '@nestjs/common';
import type { Auth } from 'firebase-admin/auth';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue } from 'firebase-admin/firestore';
import { FIREBASE_AUTH, FIRESTORE } from '../firebase/firebase.constants';
import { InscriptionEcoleDto } from './dto/inscription-ecole.dto';

const DUREE_ESSAI_JOURS = 30;

@Injectable()
export class EcolesService {
  constructor(
    @Inject(FIRESTORE) private readonly db: Firestore,
    @Inject(FIREBASE_AUTH) private readonly auth: Auth,
  ) {}

  // Reproduit InscriptionController::store() de l'app Laravel d'origine :
  // crée l'école, le premier compte admin, une année scolaire et un
  // premier trimestre par défaut, avec 30 jours d'essai Premium.
  async inscrire(dto: InscriptionEcoleDto): Promise<{ customToken: string }> {
    const ecoleRef = this.db.collection('ecoles').doc();

    let uid: string;
    try {
      const userRecord = await this.auth.createUser({
        email: dto.adminEmail,
        password: dto.adminPassword,
        displayName: dto.adminNom,
      });
      uid = userRecord.uid;
    } catch (error) {
      if (this.estEmailDejaUtilise(error)) {
        throw new ConflictException('Cette adresse email est déjà utilisée.');
      }
      throw error;
    }

    await this.auth.setCustomUserClaims(uid, {
      role: 'admin',
      ecoleId: ecoleRef.id,
    });

    const maintenant = new Date();
    const trialEndsAt = new Date(maintenant);
    trialEndsAt.setDate(trialEndsAt.getDate() + DUREE_ESSAI_JOURS);

    const anneeRef = ecoleRef.collection('anneesScolaires').doc();
    const trimestreDebut = new Date(maintenant);
    const trimestreFin = new Date(maintenant);
    trimestreFin.setMonth(trimestreFin.getMonth() + 3);

    const batch = this.db.batch();

    batch.set(ecoleRef, {
      nom: dto.nomEcole,
      slug: this.genererSlug(dto.nomEcole),
      emailContact: dto.adminEmail,
      telephone: dto.telephone ?? null,
      ville: dto.ville ?? null,
      statut: 'essai',
      plan: 'premium',
      trialEndsAt,
      createdAt: FieldValue.serverTimestamp(),
    });

    batch.set(this.db.collection('users').doc(uid), {
      name: dto.adminNom,
      email: dto.adminEmail,
      role: 'admin',
      ecoleId: ecoleRef.id,
      phone: null,
      mustChangePassword: false,
      createdAt: FieldValue.serverTimestamp(),
    });

    batch.set(anneeRef, {
      libelle: `${maintenant.getFullYear()}-${maintenant.getFullYear() + 1}`,
      dateDebut: new Date(maintenant.getFullYear(), 0, 1),
      dateFin: new Date(maintenant.getFullYear(), 11, 31),
      active: true,
    });

    batch.set(anneeRef.collection('trimestres').doc(), {
      nom: '1er trimestre',
      ordre: 1,
      dateDebut: trimestreDebut,
      dateFin: trimestreFin,
    });

    await batch.commit();

    const customToken = await this.auth.createCustomToken(uid);
    return { customToken };
  }

  async lister() {
    const snap = await this.db
      .collection('ecoles')
      .orderBy('createdAt', 'desc')
      .get();
    return snap.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
  }

  async trouver(ecoleId: string) {
    const doc = await this.db.collection('ecoles').doc(ecoleId).get();
    if (!doc.exists) {
      throw new NotFoundException('École introuvable.');
    }
    return { id: doc.id, ...doc.data() };
  }

  async suspendre(ecoleId: string) {
    await this.majStatut(ecoleId, 'suspendu');
  }

  async activer(ecoleId: string) {
    await this.majStatut(ecoleId, 'actif');
  }

  private async majStatut(ecoleId: string, statut: string): Promise<void> {
    const ref = this.db.collection('ecoles').doc(ecoleId);
    const doc = await ref.get();
    if (!doc.exists) {
      throw new NotFoundException('École introuvable.');
    }
    await ref.update({ statut });
  }

  private estEmailDejaUtilise(error: unknown): boolean {
    return (
      (error as { code?: string } | null)?.code === 'auth/email-already-exists'
    );
  }

  private genererSlug(nom: string): string {
    return nom
      .toLowerCase()
      .normalize('NFD')
      .replace(/[̀-ͯ]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)/g, '');
  }
}
