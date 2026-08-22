import {
  ConflictException,
  Inject,
  Injectable,
  NotFoundException,
} from '@nestjs/common';
import type { Auth } from 'firebase-admin/auth';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue } from 'firebase-admin/firestore';
import {
  ecoleCollection,
  trouverAbonnementActif,
} from '../common/firestore.helpers';
import { FIREBASE_AUTH, FIRESTORE } from '../firebase/firebase.constants';
import { InscriptionEcoleDto } from './dto/inscription-ecole.dto';

const DUREE_ESSAI_JOURS = 30;

export interface FiltresEcoles {
  recherche?: string;
  statut?: string;
  plan?: string;
}

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
    const trimestreRef = ecoleRef.collection('trimestres').doc();
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

    batch.set(trimestreRef, {
      anneeScolaireId: anneeRef.id,
      nom: '1er trimestre',
      ordre: 1,
      dateDebut: trimestreDebut,
      dateFin: trimestreFin,
    });

    await batch.commit();

    const customToken = await this.auth.createCustomToken(uid);
    return { customToken };
  }

  // Reproduit SuperAdmin\EcoleController::index() : recherche sur
  // nom/ville et filtres statut/plan appliqués en mémoire (peu d'écoles
  // par plateforme), plutôt qu'une pagination/requête composite Firestore.
  async lister(filtres: FiltresEcoles) {
    const snap = await this.db
      .collection('ecoles')
      .orderBy('createdAt', 'desc')
      .get();

    let ecoles = snap.docs.map((doc) => ({ id: doc.id, ...doc.data() }));

    if (filtres.recherche) {
      const recherche = filtres.recherche.toLowerCase();
      ecoles = ecoles.filter(
        (e) =>
          (e['nom'] as string | undefined)?.toLowerCase().includes(recherche) ||
          (e['ville'] as string | undefined)?.toLowerCase().includes(recherche),
      );
    }
    if (filtres.statut) {
      ecoles = ecoles.filter((e) => e['statut'] === filtres.statut);
    }
    if (filtres.plan) {
      ecoles = ecoles.filter((e) => e['plan'] === filtres.plan);
    }

    return Promise.all(
      ecoles.map(async (ecole) => ({
        ...ecole,
        utilisateursCount: await this.compterUtilisateurs(ecole['id']),
      })),
    );
  }

  // Reproduit SuperAdmin\EcoleController::show().
  async trouver(ecoleId: string) {
    const doc = await this.db.collection('ecoles').doc(ecoleId).get();
    if (!doc.exists) {
      throw new NotFoundException('École introuvable.');
    }

    const [
      eleves,
      enseignants,
      classes,
      utilisateurs,
      abonnements,
      factures,
      abonnementActif,
    ] = await Promise.all([
      ecoleCollection(this.db, ecoleId, 'eleves').count().get(),
      ecoleCollection(this.db, ecoleId, 'enseignants').count().get(),
      ecoleCollection(this.db, ecoleId, 'classes').count().get(),
      this.compterUtilisateurs(ecoleId),
      ecoleCollection(this.db, ecoleId, 'abonnements')
        .orderBy('dateFin', 'desc')
        .get(),
      ecoleCollection(this.db, ecoleId, 'factures')
        .orderBy('createdAt', 'desc')
        .get(),
      trouverAbonnementActif(this.db, ecoleId),
    ]);

    return {
      id: doc.id,
      ...doc.data(),
      stats: {
        eleves: eleves.data().count,
        enseignants: enseignants.data().count,
        classes: classes.data().count,
        utilisateurs,
      },
      abonnements: abonnements.docs.map((d) => ({ id: d.id, ...d.data() })),
      factures: factures.docs.map((d) => ({ id: d.id, ...d.data() })),
      abonnementActif,
    };
  }

  private async compterUtilisateurs(ecoleId: string): Promise<number> {
    const snap = await this.db
      .collection('users')
      .where('ecoleId', '==', ecoleId)
      .count()
      .get();
    return snap.data().count;
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
