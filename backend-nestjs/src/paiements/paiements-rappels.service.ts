import { Inject, Injectable, Logger } from '@nestjs/common';
import { Cron, CronExpression } from '@nestjs/schedule';
import type { Firestore } from 'firebase-admin/firestore';
import { FieldValue, Timestamp } from 'firebase-admin/firestore';
import {
  creerNotification,
  ecoleAAccesPremium,
  ecoleCollection,
} from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { MailService } from '../mail/mail.service';

const DUREE_MIN_ENTRE_RAPPELS_MS = 24 * 60 * 60 * 1000;

@Injectable()
export class PaiementsRappelsService {
  private readonly logger = new Logger(PaiementsRappelsService.name);

  constructor(
    @Inject(FIRESTORE) private readonly db: Firestore,
    private readonly mailService: MailService,
  ) {}

  // Une fois par jour à 8h, comme le ferait le scheduler Laravel avec la
  // commande `paiements:rappels`.
  @Cron(CronExpression.EVERY_DAY_AT_8AM)
  async executerPourToutesLesEcoles(): Promise<void> {
    const ecolesSnap = await this.db.collection('ecoles').get();
    let total = 0;

    for (const ecoleDoc of ecolesSnap.docs) {
      if (!(await ecoleAAccesPremium(this.db, ecoleDoc.id, ecoleDoc.data()))) {
        continue;
      }
      total += await this.executerPourEcole(ecoleDoc.id);
    }

    this.logger.log(`${total} rappel(s) de paiement envoyé(s).`);
  }

  // Reproduit EnvoyerRappelsPaiements::handle() pour une seule école —
  // réutilisable par le cron (toutes les écoles) et par un déclenchement
  // manuel (une école, ex. bouton admin).
  async executerPourEcole(ecoleId: string, joursAvant = 3): Promise<number> {
    const seuil = new Date();
    seuil.setDate(seuil.getDate() + joursAvant);

    const snap = await ecoleCollection(this.db, ecoleId, 'paiements')
      .where('statut', '!=', 'paye')
      .get();

    const maintenant = new Date();
    let envoyes = 0;

    for (const doc of snap.docs) {
      const paiement = doc.data();
      const dateEcheance = new Date(paiement['dateEcheance'] as string);
      if (dateEcheance > seuil) continue;

      const dernierRappelLe = (
        paiement['dernierRappelLe'] as Timestamp | null
      )?.toDate();
      if (
        dernierRappelLe &&
        maintenant.getTime() - dernierRappelLe.getTime() <
          DUREE_MIN_ENTRE_RAPPELS_MS
      ) {
        continue;
      }

      await this.rappelerPourPaiement(
        ecoleId,
        doc.id,
        paiement,
        dateEcheance < maintenant,
      );
      envoyes += 1;
    }

    return envoyes;
  }

  private async rappelerPourPaiement(
    ecoleId: string,
    paiementId: string,
    paiement: FirebaseFirestore.DocumentData,
    enRetard: boolean,
  ): Promise<void> {
    const eleveId = paiement['eleveId'] as string;
    const eleveSnap = await ecoleCollection(this.db, ecoleId, 'eleves')
      .doc(eleveId)
      .get();
    if (!eleveSnap.exists) return;
    const eleve = eleveSnap.data()!;
    const eleveNom = `${eleve['prenom'] as string} ${eleve['nom'] as string}`;
    const tuteurIds = (eleve['tuteurIds'] as string[] | undefined) ?? [];
    const solde =
      (paiement['montant'] as number) - (paiement['montantPaye'] as number);

    for (const tuteurId of tuteurIds) {
      const tuteurSnap = await ecoleCollection(this.db, ecoleId, 'tuteurs')
        .doc(tuteurId)
        .get();
      const userId = tuteurSnap.data()?.['userId'] as string | undefined;
      if (!userId) continue;

      const userSnap = await this.db.collection('users').doc(userId).get();
      const email = userSnap.data()?.['email'] as string | undefined;

      if (email) {
        await this.mailService.envoyerRappelPaiement({
          destinataire: email,
          eleveNom,
          type: paiement['type'] as string,
          dateEcheance: paiement['dateEcheance'] as string,
          solde,
          enRetard,
        });
      }

      await creerNotification(this.db, ecoleId, {
        utilisateurId: userId,
        titre: enRetard ? 'Paiement en retard' : 'Échéance de paiement proche',
        message: `${eleveNom} — ${new Intl.NumberFormat('fr-FR').format(solde)} FCFA dû(s) pour le ${paiement['dateEcheance'] as string}`,
        type: 'paiement_rappel',
      });
    }

    await ecoleCollection(this.db, ecoleId, 'paiements')
      .doc(paiementId)
      .update({
        dernierRappelLe: FieldValue.serverTimestamp(),
      });
  }
}
