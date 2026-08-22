# Schéma Firestore — Scolarix

Référence de structure des données pour la migration NestJS + Angular + Firestore.
Ce document est mis à jour à chaque phase de la migration (voir `CAHIER_DES_CHARGES.md` pour le cahier des charges fonctionnel d'origine).

## Principe général

- **Isolation multi-tenant par sous-collections.** Chaque école est un document dans `/ecoles/{ecoleId}`, et toutes ses données (élèves, classes, notes, ...) vivent dans des sous-collections en dessous. Ça donne une isolation naturelle et des règles de sécurité simples.
- **Un seul niveau de sous-collection sous chaque école** (pas de nesting en cascade). Les relations (ex. trimestre → année scolaire, élève → classe) passent par un champ `xxxId` plutôt que par l'emplacement du document. Ça évite les *collection group queries* (qui demandent des index composites spécifiques) et se rapproche du modèle relationnel d'origine.
- **Accès cross-tenant (super-admin)** via *collection group queries* (ex. `collectionGroup('factures')`) plutôt que des collections top-level, pour ne pas casser l'isolation par école.
- **Écritures via le backend NestJS** (SDK Admin, qui contourne les règles) pour tout le CRUD classique. Les règles Firestore ci-dessous sont donc une isolation de secours + les quelques accès directs qu'on ouvre volontairement (temps réel).
- **Documents dénormalisés pour les calculs lourds** (bulletins, risque d'échec) : Firestore ne fait pas de jointures ni d'agrégations complexes côté serveur, donc ces documents sont pré-calculés par des Cloud Functions plutôt que recalculés à la volée.

## Collections top-level

### `/users/{uid}`
Profil applicatif, en miroir de Firebase Auth (le `uid` du document = l'UID Auth).
```
role: 'super_admin' | 'admin' | 'enseignant' | 'parent' | 'eleve'
ecoleId: string | null          // null pour super_admin
nom: string
prenom: string
email: string
actif: boolean
createdAt: timestamp
```
Les claims personnalisés Firebase Auth (`role`, `ecoleId`, `premium`) sont synchronisés depuis ce document à chaque écriture (Cloud Function `onUserWrite` ou écriture directe côté NestJS via `auth.setCustomUserClaims`).

### `/ecoles/{ecoleId}`
Le tenant.
```
nom: string
adresse: string
statut: 'actif' | 'suspendu'
abonnement: {
  plan: 'gratuit' | 'premium'
  statut: 'actif' | 'expire' | 'essai'
  expireLe: timestamp | null
}
createdAt: timestamp
```

## Sous-collections de `/ecoles/{ecoleId}/...`

| Sous-collection | Rôle | Champs clés |
|---|---|---|
| `anneesScolaires/{id}` | Années scolaires | `libelle, dateDebut, dateFin, active` |
| `trimestres/{id}` | Trimestres | `anneeScolaireId, nom, ordre, dateDebut, dateFin` |
| `classes/{id}` | Classes | `nom, niveau, anneeScolaireId, enseignantPrincipalId, capacite` |
| `matieres/{id}` | Matières | `nom, code, coefficientDefaut` |
| `eleves/{id}` | Élèves | `matricule, nom, prenom, sexe, dateNaissance, classeId, statut, dateInscription, tuteurIds[], userId` |
| `enseignants/{id}` | Enseignants | `nom, prenom, telephone, email, specialite, dateEmbauche, userId, matiereIds[], classeIds[]` |
| `tuteurs/{id}` | Parents/tuteurs | `nom, prenom, telephone, email, profession, adresse, userId, eleves[] ({id, lienParente})` |
| `creneauxHoraires/{id}` | Emploi du temps | `classeId, matiereId, enseignantId, jourSemaine, heureDebut, heureFin, salle` |
| `notes/{id}` | Notes | `eleveId, matiereId, classeId, trimestreId, enseignantId, type, valeur, bareme, coefficient, dateEvaluation, commentaire`. ID déterministe pour la saisie en masse (`classeId_matiereId_trimestreId_type_date_eleveId`), auto-généré pour la saisie unitaire. |
| `presences/{id}` | Présences | `eleveId, classeId, enseignantId, trimestreId, date, statut, motif`. ID déterministe `eleveId_date` (upsert, un seul statut par élève et par jour). |
| `bulletins/{id}` | Bulletins (instantanés) | `eleveId, classeId, trimestreId, moyenneGenerale, rang, appreciation, tauxPresence, matieresDetail[], genereLe`. ID déterministe `eleveId_trimestreId` ; le PDF est régénéré à la demande depuis cet instantané (pas de fichier stocké). |
| `paiements/{id}` | Paiements | `eleveId, anneeScolaireId, type, montant, montantPaye, dateEcheance, datePaiement, statut, commentaire, dernierRappelLe` |
| `notifications/{id}` | Notifications | `utilisateurId, titre, message, type, lien, lu, creeLe` |
| `messages/{id}` | Messagerie | `expediteurId, destinataireId, contenu, lu, createdAt` |
| `annonces/{id}` | Annonces | `titre, contenu, cible ('tous'\|'parents'\|'enseignants'\|'eleves'\|'classe'), classeId, auteurId, datePublication`. Visibilité filtrée côté service selon le rôle du lecteur. |
| `abonnements/{id}` | Historique d'abonnement | `statut, dateDebut, dateFin, montant` |
| `factures/{id}` | Facturation abonnement | `montant, statut, periode, dateEmission, dateConfirmation` |

Le risque d'échec (`/eleves/{id}/progression`) n'est **pas persisté** : il est recalculé à la demande à partir de `notes` et `bulletins` (voir `ProgressionService`), pour rester toujours à jour sans job de synchronisation séparé.

## Statut

- [x] Phase 0-1 : fondations, auth, écoles/comptes
- [x] Phase 2 : référentiel académique (années, trimestres, classes, matières, enseignants, tuteurs, élèves)
- [x] Phase 3 : emploi du temps, notes, présences
- [x] Phase 4 : bulletins, risque d'échec, rapports PDF
- [x] Phase 5 : paiements, relances automatiques (cron + déclenchement manuel), notifications
- [x] Phase 6 : messagerie (enseignant ↔ parent), annonces (visibilité par rôle/classe), notifications (liste + marquage lu)
- [x] Phase 7 : espace élève (mon-espace) et espace parent (mes-enfants) en libre-service
- [ ] Phase 8+ : abonnement, super-admin
