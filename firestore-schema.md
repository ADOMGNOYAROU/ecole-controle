# Schéma Firestore — Scolarix

Référence de structure des données pour la migration NestJS + Angular + Firestore.
Ce document est mis à jour à chaque phase de la migration (voir `CAHIER_DES_CHARGES.md` pour le cahier des charges fonctionnel d'origine).

## Principe général

- **Isolation multi-tenant par sous-collections.** Chaque école est un document dans `/ecoles/{ecoleId}`, et toutes ses données (élèves, classes, notes, ...) vivent dans des sous-collections en dessous. Ça donne une isolation naturelle et des règles de sécurité simples.
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
| `anneesScolaires/{id}/trimestres/{id}` | Trimestres | `libelle, dateDebut, dateFin, ordre` |
| `classes/{id}` | Classes | `nom, niveau, anneeScolaireId, enseignantPrincipalId` |
| `matieres/{id}` | Matières | `nom, coefficient` |
| `eleves/{id}` | Élèves | `nom, prenom, dateNaissance, classeId, tuteurIds[], userId, statut` |
| `enseignants/{id}` | Enseignants | `nom, prenom, userId, matiereIds[]` |
| `tuteurs/{id}` | Parents/tuteurs | `nom, prenom, telephone, userId, eleveIds[]` |
| `emploiDuTemps/{id}` | Créneaux horaires | `classeId, matiereId, enseignantId, jour, heureDebut, heureFin` |
| `notes/{id}` | Notes | `eleveId, matiereId, classeId, trimestreId, valeur, coefficient, type, saisiParId, createdAt` |
| `presences/{id}` | Présences | `eleveId, classeId, date, statut, justifie, saisiParId` |
| `bulletins/{id}` | Bulletins (générés) | `eleveId, trimestreId, classeId, moyenneGenerale, rang, appreciations, pdfUrl, genereLe` |
| `paiements/{id}` | Paiements | `eleveId, montant, montantPaye, dateEcheance, statut, dernierRappelLe` |
| `messages/{id}` | Messagerie | `participants[], texte, envoyeParId, envoyeLe, lu` |
| `annonces/{id}` | Annonces | `titre, contenu, cibleRoles[], publieParId, publieLe` |
| `notifications/{id}` | Notifications | `utilisateurId, titre, message, lu, creeLe` |
| `progressions/{eleveId}` | Risque d'échec (calculé) | `score, facteurs[], dernierCalculLe` |
| `factures/{id}` | Facturation abonnement | `montant, statut, periode, dateEmission, dateConfirmation` |

## Statut

- [x] Schéma initial rédigé (Phase 0)
- [ ] Affiné à chaque phase avec les cas réels rencontrés à l'implémentation
