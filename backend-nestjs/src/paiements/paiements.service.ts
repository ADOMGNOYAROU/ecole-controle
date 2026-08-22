import { BadRequestException, Inject, Injectable } from '@nestjs/common';
import type { Firestore, Query } from 'firebase-admin/firestore';
import { FieldValue } from 'firebase-admin/firestore';
import { ecoleCollection, getDocOrThrow } from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { PaiementDto } from './dto/paiement.dto';

export interface PaiementFiltres {
  statut?: string;
  eleveId?: string;
}

@Injectable()
export class PaiementsService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  async lister(ecoleId: string, filtres: PaiementFiltres) {
    let query: Query = ecoleCollection(this.db, ecoleId, 'paiements');
    if (filtres.statut) query = query.where('statut', '==', filtres.statut);
    if (filtres.eleveId) query = query.where('eleveId', '==', filtres.eleveId);

    const snap = await query.get();
    return snap.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
  }

  async trouver(ecoleId: string, id: string) {
    const snap = await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'paiements').doc(id),
      'Paiement introuvable.',
    );
    return { id: snap.id, ...snap.data() };
  }

  async stats(ecoleId: string) {
    const snap = await ecoleCollection(this.db, ecoleId, 'paiements').get();
    const maintenant = new Date();

    let totalAttendu = 0;
    let totalCollecte = 0;
    let enRetard = 0;

    for (const doc of snap.docs) {
      const p = doc.data();
      totalAttendu += p['montant'] as number;
      totalCollecte += p['montantPaye'] as number;
      const dateEcheance = new Date(p['dateEcheance'] as string);
      if (p['statut'] !== 'paye' && dateEcheance < maintenant) {
        enRetard += 1;
      }
    }

    return { totalAttendu, totalCollecte, enRetard };
  }

  async creer(ecoleId: string, dto: PaiementDto) {
    this.verifierMontantPaye(dto);
    await this.verifierReferences(ecoleId, dto);

    const ref = await ecoleCollection(this.db, ecoleId, 'paiements').add({
      ...this.versDocument(dto),
      dernierRappelLe: null,
      createdAt: FieldValue.serverTimestamp(),
    });
    return { id: ref.id };
  }

  async modifier(ecoleId: string, id: string, dto: PaiementDto): Promise<void> {
    this.verifierMontantPaye(dto);
    const ref = ecoleCollection(this.db, ecoleId, 'paiements').doc(id);
    await getDocOrThrow(ref, 'Paiement introuvable.');
    await this.verifierReferences(ecoleId, dto);

    await ref.update(this.versDocument(dto));
  }

  async supprimer(ecoleId: string, id: string): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'paiements').doc(id);
    await getDocOrThrow(ref, 'Paiement introuvable.');
    await ref.delete();
  }

  async pourRapport(
    ecoleId: string,
    filtres: PaiementFiltres,
  ): Promise<string[][]> {
    const paiements = (await this.lister(ecoleId, filtres)) as Record<
      string,
      unknown
    >[];
    const formateur = new Intl.NumberFormat('fr-FR');
    const cacheEleves = new Map<string, string>();

    const lignes: string[][] = [];
    for (const paiement of paiements) {
      const eleveId = paiement['eleveId'] as string;
      if (!cacheEleves.has(eleveId)) {
        const eleveSnap = await ecoleCollection(this.db, ecoleId, 'eleves')
          .doc(eleveId)
          .get();
        const data = eleveSnap.data();
        cacheEleves.set(
          eleveId,
          data ? `${data['prenom'] as string} ${data['nom'] as string}` : '—',
        );
      }

      const statut = paiement['statut'] as string;
      lignes.push([
        cacheEleves.get(eleveId)!,
        paiement['type'] as string,
        `${formateur.format(paiement['montant'] as number)} FCFA`,
        `${formateur.format(paiement['montantPaye'] as number)} FCFA`,
        paiement['dateEcheance'] as string,
        statut.charAt(0).toUpperCase() + statut.slice(1),
      ]);
    }

    return lignes;
  }

  // Calcule le statut à partir du montant payé et de l'échéance,
  // reproduisant PaiementController::calculerStatut().
  calculerStatut(
    montant: number,
    montantPaye: number,
    dateEcheance: string,
  ): string {
    if (montantPaye >= montant && montant > 0) return 'paye';
    if (montantPaye > 0) return 'partiel';
    return new Date() > new Date(dateEcheance) ? 'retard' : 'en_attente';
  }

  private versDocument(dto: PaiementDto) {
    const montantPaye = dto.montantPaye ?? 0;
    return {
      eleveId: dto.eleveId,
      anneeScolaireId: dto.anneeScolaireId,
      type: dto.type,
      montant: dto.montant,
      montantPaye,
      dateEcheance: dto.dateEcheance,
      datePaiement: dto.datePaiement ?? null,
      commentaire: dto.commentaire ?? null,
      statut: this.calculerStatut(dto.montant, montantPaye, dto.dateEcheance),
    };
  }

  private verifierMontantPaye(dto: PaiementDto): void {
    if (dto.montantPaye !== undefined && dto.montantPaye > dto.montant) {
      throw new BadRequestException(
        'Le montant payé ne peut pas dépasser le montant dû.',
      );
    }
  }

  private async verifierReferences(
    ecoleId: string,
    dto: PaiementDto,
  ): Promise<void> {
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'eleves').doc(dto.eleveId),
      'Élève introuvable.',
    );
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'anneesScolaires').doc(
        dto.anneeScolaireId,
      ),
      'Année scolaire introuvable.',
    );
  }
}
