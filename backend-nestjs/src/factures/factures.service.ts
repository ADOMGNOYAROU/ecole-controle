import { Inject, Injectable, NotFoundException } from '@nestjs/common';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue } from 'firebase-admin/firestore';
import {
  ecoleCollection,
  getDocOrThrow,
  versDate,
} from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { ConfirmerFactureDto } from './dto/confirmer-facture.dto';

// Tarif trimestriel de l'offre Premium, reproduit de Ecole::TARIF_PREMIUM_TRIMESTRIEL.
export const TARIF_PREMIUM_TRIMESTRIEL = 15000;

export interface FactureAvecEcole extends Record<string, unknown> {
  id: string;
  ecoleId: string;
  ecoleNom: string;
}

@Injectable()
export class FacturesService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  // Reproduit AbonnementController::index() côté école.
  async pourEcole(ecoleId: string) {
    const ecoleSnap = await getDocOrThrow(
      this.db.collection('ecoles').doc(ecoleId),
      'École introuvable.',
    );
    const facturesSnap = await ecoleCollection(this.db, ecoleId, 'factures')
      .orderBy('dateEcheance', 'desc')
      .get();

    return {
      ecole: { id: ecoleSnap.id, ...ecoleSnap.data() },
      factures: facturesSnap.docs.map((doc) => ({
        id: doc.id,
        ...doc.data(),
      })),
      tarif: TARIF_PREMIUM_TRIMESTRIEL,
    };
  }

  // Reproduit AbonnementController::souscrire() : crée une facture en
  // attente, à régler hors-ligne (Mobile Money) puis confirmée manuellement
  // par un super-admin, faute d'intégration de passerelle de paiement.
  async souscrire(ecoleId: string): Promise<{ id: string }> {
    await getDocOrThrow(
      this.db.collection('ecoles').doc(ecoleId),
      'École introuvable.',
    );

    const dateEcheance = new Date();
    dateEcheance.setDate(dateEcheance.getDate() + 7);

    const ref = await ecoleCollection(this.db, ecoleId, 'factures').add({
      montant: TARIF_PREMIUM_TRIMESTRIEL,
      dateEcheance,
      statut: 'en_attente',
      abonnementId: null,
      methodePaiement: null,
      referenceTransaction: null,
      payeeLe: null,
      confirmeeParId: null,
      createdAt: FieldValue.serverTimestamp(),
    });
    return { id: ref.id };
  }

  // Reproduit SuperAdmin\FactureController::index(). Une facture appartient
  // à une sous-collection par école ; on liste via collectionGroup en une
  // seule lecture large plutôt que d'itérer chaque école, et on filtre/trie
  // en mémoire pour ne pas dépendre d'un index composite cross-collection.
  async listerToutes(filtres: { statut?: string; ecoleId?: string }) {
    const [facturesSnap, ecolesSnap] = await Promise.all([
      this.db.collectionGroup('factures').get(),
      this.db.collection('ecoles').get(),
    ]);

    const nomsEcoles = new Map(
      ecolesSnap.docs.map((doc) => [doc.id, doc.data()['nom'] as string]),
    );

    const toutesLesFactures: FactureAvecEcole[] = facturesSnap.docs.map(
      (doc) => {
        const ecoleId = doc.ref.parent.parent!.id;
        return {
          id: doc.id,
          ecoleId,
          ecoleNom: nomsEcoles.get(ecoleId) ?? '—',
          ...doc.data(),
        };
      },
    );

    const maintenant = new Date();
    const stats = {
      enAttente: toutesLesFactures.filter((f) => f['statut'] === 'en_attente')
        .length,
      enRetard: toutesLesFactures.filter((f) => {
        const echeance = versDate(f['dateEcheance']);
        return (
          f['statut'] === 'en_attente' &&
          echeance !== undefined &&
          echeance < maintenant
        );
      }).length,
      payeesCeMois: this.sommeCeMois(toutesLesFactures),
      montantEnAttente: toutesLesFactures
        .filter((f) => f['statut'] === 'en_attente')
        .reduce((total, f) => total + (f['montant'] as number), 0),
    };

    let factures = toutesLesFactures;
    if (filtres.statut) {
      factures = factures.filter((f) => f['statut'] === filtres.statut);
    }
    if (filtres.ecoleId) {
      factures = factures.filter((f) => f.ecoleId === filtres.ecoleId);
    }
    factures = [...factures].sort(
      (a, b) =>
        (versDate(b['createdAt'])?.getTime() ?? 0) -
        (versDate(a['createdAt'])?.getTime() ?? 0),
    );

    return {
      factures,
      stats,
      ecoles: ecolesSnap.docs.map((doc) => ({
        id: doc.id,
        nom: doc.data()['nom'] as string,
      })),
    };
  }

  // Reproduit DashboardController@index (bloc SaaS cross-tenant).
  async dashboard() {
    const [ecolesSnap, abonnementsSnap, facturesSnap] = await Promise.all([
      this.db.collection('ecoles').get(),
      this.db.collectionGroup('abonnements').get(),
      this.db.collectionGroup('factures').get(),
    ]);

    const ecoles = ecolesSnap.docs.map((doc) => ({
      id: doc.id,
      ...doc.data(),
    }));
    const debutMois = new Date();
    debutMois.setDate(1);
    debutMois.setHours(0, 0, 0, 0);

    const maintenant = new Date();
    const abonnementsActifs = abonnementsSnap.docs.filter((doc) => {
      const data = doc.data();
      const dateFin = versDate(data['dateFin']);
      return (
        data['statut'] === 'actif' &&
        dateFin !== undefined &&
        dateFin >= maintenant
      );
    }).length;

    const factures: Record<string, unknown>[] = facturesSnap.docs.map(
      (doc) => ({
        id: doc.id,
        ecoleId: doc.ref.parent.parent!.id,
        ...doc.data(),
      }),
    );
    const facturesEnAttente = factures.filter(
      (f) => f['statut'] === 'en_attente',
    );

    const nomsEcoles = new Map(
      ecoles.map((e) => [e['id'], e['nom'] as string]),
    );

    return {
      stats: {
        ecolesTotal: ecoles.length,
        ecolesActives: ecoles.filter((e) => e['statut'] === 'actif').length,
        ecolesEssai: ecoles.filter((e) => e['statut'] === 'essai').length,
        ecolesSuspendues: ecoles.filter((e) => e['statut'] === 'suspendu')
          .length,
        nouvellesCeMois: ecoles.filter((e) => {
          const createdAt = versDate(e['createdAt']);
          return createdAt !== undefined && createdAt >= debutMois;
        }).length,
      },
      abonnementsActifs,
      mrrEstime: Math.round(
        (abonnementsActifs * TARIF_PREMIUM_TRIMESTRIEL) / 3,
      ),
      revenuCeMois: this.sommeCeMois(factures as FactureAvecEcole[]),
      facturesEnAttente: facturesEnAttente.length,
      facturesEnRetard: facturesEnAttente.filter((f) => {
        const echeance = versDate(f['dateEcheance']);
        return echeance !== undefined && echeance < maintenant;
      }).length,
      montantEnAttente: facturesEnAttente.reduce(
        (total, f) => total + (f['montant'] as number),
        0,
      ),
      dernieresEcoles: [...ecoles]
        .sort(
          (a, b) =>
            (versDate(b['createdAt'])?.getTime() ?? 0) -
            (versDate(a['createdAt'])?.getTime() ?? 0),
        )
        .slice(0, 5),
      facturesUrgentes: [...facturesEnAttente]
        .sort(
          (a, b) =>
            (versDate(a['dateEcheance'])?.getTime() ?? 0) -
            (versDate(b['dateEcheance'])?.getTime() ?? 0),
        )
        .slice(0, 5)
        .map((f) => ({
          ...f,
          ecoleNom: nomsEcoles.get(f['ecoleId'] as string) ?? '—',
        })),
    };
  }

  // Reproduit Facture::confirmerPaiement() : marque la facture payée puis
  // crée ou prolonge un abonnement Premium de 3 mois, et réactive l'école.
  async confirmer(
    factureId: string,
    dto: ConfirmerFactureDto,
    confirmeeParUid: string,
  ): Promise<void> {
    const facturesSnap = await this.db.collectionGroup('factures').get();
    const factureDoc = facturesSnap.docs.find((doc) => doc.id === factureId);
    if (!factureDoc) {
      throw new NotFoundException('Facture introuvable.');
    }
    const ecoleId = factureDoc.ref.parent.parent!.id;
    const facture = factureDoc.data();

    const maintenant = new Date();
    await factureDoc.ref.update({
      statut: 'payee',
      payeeLe: maintenant,
      confirmeeParId: confirmeeParUid,
      methodePaiement: dto.methodePaiement,
      referenceTransaction:
        dto.referenceTransaction ??
        (facture['referenceTransaction'] as string | null) ??
        null,
    });

    const dateFin = new Date(maintenant);
    dateFin.setMonth(dateFin.getMonth() + 3);
    const abonnementId = facture['abonnementId'] as string | null;

    if (!abonnementId) {
      const abonnementRef = ecoleCollection(
        this.db,
        ecoleId,
        'abonnements',
      ).doc();
      await abonnementRef.set({
        plan: 'premium',
        dateDebut: maintenant,
        dateFin,
        statut: 'actif',
        montant: facture['montant'] as number,
      });
      await factureDoc.ref.update({ abonnementId: abonnementRef.id });
    } else {
      await ecoleCollection(this.db, ecoleId, 'abonnements')
        .doc(abonnementId)
        .update({ statut: 'actif', dateDebut: maintenant, dateFin });
    }

    await this.db
      .collection('ecoles')
      .doc(ecoleId)
      .update({ plan: 'premium', statut: 'actif' });
  }

  private sommeCeMois(factures: FactureAvecEcole[]): number {
    const debutMois = new Date();
    debutMois.setDate(1);
    debutMois.setHours(0, 0, 0, 0);

    return factures
      .filter((f) => {
        const payeeLe = versDate(f['payeeLe']);
        return (
          f['statut'] === 'payee' &&
          payeeLe !== undefined &&
          payeeLe >= debutMois
        );
      })
      .reduce((total, f) => total + (f['montant'] as number), 0);
  }
}
