import { ForbiddenException, Inject, Injectable } from '@nestjs/common';
import type { Firestore, Query } from 'firebase-admin/firestore';
import type { AuthenticatedUser } from '../auth/types';
import {
  ecoleCollection,
  getDocOrThrow,
  trouverEnseignantIdParUid,
  trouverTrimestreActuelId,
} from '../common/firestore.helpers';
import { FIRESTORE } from '../firebase/firebase.constants';
import { PresenceBulkDto } from './dto/presence-bulk.dto';
import { PresenceDto } from './dto/presence.dto';

export interface PresenceFiltres {
  classeId?: string;
  date?: string;
}

@Injectable()
export class PresencesService {
  constructor(@Inject(FIRESTORE) private readonly db: Firestore) {}

  async lister(
    ecoleId: string,
    user: AuthenticatedUser,
    filtres: PresenceFiltres,
  ) {
    let query: Query = ecoleCollection(this.db, ecoleId, 'presences');

    if (user.role === 'enseignant') {
      const enseignantId = await trouverEnseignantIdParUid(
        this.db,
        ecoleId,
        user.uid,
      );
      if (!enseignantId) return [];
      query = query.where('enseignantId', '==', enseignantId);
    }
    if (filtres.classeId)
      query = query.where('classeId', '==', filtres.classeId);
    if (filtres.date) query = query.where('date', '==', filtres.date);

    const snap = await query.get();
    return snap.docs.map((doc) => ({ id: doc.id, ...doc.data() }));
  }

  async elevesPourAppel(ecoleId: string, classeId: string) {
    const snap = await ecoleCollection(this.db, ecoleId, 'eleves')
      .where('classeId', '==', classeId)
      .where('statut', '==', 'actif')
      .get();

    return snap.docs
      .map((doc) => {
        const data = doc.data();
        return {
          id: doc.id,
          nom: data['nom'] as string,
          prenom: data['prenom'] as string,
          matricule: data['matricule'] as string,
        };
      })
      .sort((a, b) => a.nom.localeCompare(b.nom));
  }

  async creer(ecoleId: string, user: AuthenticatedUser, dto: PresenceDto) {
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'eleves').doc(dto.eleveId),
      'Élève introuvable.',
    );
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'classes').doc(dto.classeId),
      'Classe introuvable.',
    );

    const enseignantId = await trouverEnseignantIdParUid(
      this.db,
      ecoleId,
      user.uid,
    );
    const trimestreId =
      dto.trimestreId ?? (await trouverTrimestreActuelId(this.db, ecoleId));
    const id = `${dto.eleveId}_${dto.date}`;

    await ecoleCollection(this.db, ecoleId, 'presences')
      .doc(id)
      .set({
        eleveId: dto.eleveId,
        classeId: dto.classeId,
        enseignantId,
        trimestreId,
        date: dto.date,
        statut: dto.statut,
        motif: dto.motif ?? null,
      });
    return { id };
  }

  async bulkCreer(
    ecoleId: string,
    user: AuthenticatedUser,
    dto: PresenceBulkDto,
  ): Promise<void> {
    await getDocOrThrow(
      ecoleCollection(this.db, ecoleId, 'classes').doc(dto.classeId),
      'Classe introuvable.',
    );

    const enseignantId = await trouverEnseignantIdParUid(
      this.db,
      ecoleId,
      user.uid,
    );
    const trimestreId =
      dto.trimestreId ?? (await trouverTrimestreActuelId(this.db, ecoleId));
    const batch = this.db.batch();

    for (const entree of dto.presences) {
      const id = `${entree.eleveId}_${dto.date}`;
      batch.set(ecoleCollection(this.db, ecoleId, 'presences').doc(id), {
        eleveId: entree.eleveId,
        classeId: dto.classeId,
        enseignantId,
        trimestreId,
        date: dto.date,
        statut: entree.statut,
        motif: entree.motif ?? null,
      });
    }

    await batch.commit();
  }

  async modifier(
    ecoleId: string,
    user: AuthenticatedUser,
    id: string,
    dto: PresenceDto,
  ): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'presences').doc(id);
    const snap = await getDocOrThrow(ref, 'Présence introuvable.');
    await this.verifierProprietaire(
      ecoleId,
      user,
      snap.data()!['enseignantId'] as string | null,
    );

    await ref.update({
      eleveId: dto.eleveId,
      classeId: dto.classeId,
      trimestreId:
        dto.trimestreId ?? (snap.data()!['trimestreId'] as string | null),
      date: dto.date,
      statut: dto.statut,
      motif: dto.motif ?? null,
    });
  }

  async supprimer(
    ecoleId: string,
    user: AuthenticatedUser,
    id: string,
  ): Promise<void> {
    const ref = ecoleCollection(this.db, ecoleId, 'presences').doc(id);
    const snap = await getDocOrThrow(ref, 'Présence introuvable.');
    await this.verifierProprietaire(
      ecoleId,
      user,
      snap.data()!['enseignantId'] as string | null,
    );
    await ref.delete();
  }

  async pourRapport(
    ecoleId: string,
    user: AuthenticatedUser,
    filtres: PresenceFiltres,
  ): Promise<string[][]> {
    const presences = (await this.lister(ecoleId, user, filtres)) as Record<
      string,
      unknown
    >[];
    const cacheNoms = new Map<string, string>();

    const nomDe = async (
      collection: string,
      id: string,
      champs: (data: Record<string, unknown>) => string,
    ) => {
      const cle = `${collection}:${id}`;
      if (!cacheNoms.has(cle)) {
        const snap = await ecoleCollection(this.db, ecoleId, collection)
          .doc(id)
          .get();
        cacheNoms.set(cle, snap.exists ? champs(snap.data()!) : '—');
      }
      return cacheNoms.get(cle)!;
    };

    const lignes: string[][] = [];
    for (const presence of presences) {
      const eleveNom = await nomDe(
        'eleves',
        presence['eleveId'] as string,
        (d) => `${d['prenom'] as string} ${d['nom'] as string}`,
      );
      const classeNom = await nomDe(
        'classes',
        presence['classeId'] as string,
        (d) => d['nom'] as string,
      );
      const statut = presence['statut'] as string;

      lignes.push([
        eleveNom,
        classeNom,
        presence['date'] as string,
        statut.charAt(0).toUpperCase() + statut.slice(1),
        (presence['motif'] as string | null) ?? '—',
      ]);
    }

    return lignes;
  }

  private async verifierProprietaire(
    ecoleId: string,
    user: AuthenticatedUser,
    enseignantIdDeLaPresence: string | null,
  ): Promise<void> {
    if (user.role === 'admin') return;

    if (user.role === 'enseignant') {
      const monEnseignantId = await trouverEnseignantIdParUid(
        this.db,
        ecoleId,
        user.uid,
      );
      if (monEnseignantId && monEnseignantId === enseignantIdDeLaPresence)
        return;
    }

    throw new ForbiddenException('Accès non autorisé.');
  }
}
