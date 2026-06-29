# Cahier des charges — École Manager

**Plateforme SaaS de gestion scolaire multi-établissements pour le marché togolais**

| | |
|---|---|
| Version | 1.0 |
| Date | 28 juin 2026 |
| Statut | Document de référence — backend implémenté, API de paiement à intégrer |
| Périmètre couvert | `school-management-api` (backend Laravel, SaaS) + `school_management_app` (application mobile Flutter) |

---

## 1. Présentation générale

### 1.1 Contexte

La gestion administrative et pédagogique des établissements scolaires privés au Togo repose
encore majoritairement sur des outils manuels (cahiers, tableurs) ou des logiciels mono-poste non
connectés. École Manager est une plateforme web (et mobile, via l'application compagnon) qui
centralise la gestion d'un établissement — élèves, enseignants, classes, notes, présences,
finances, communication — et la propose en mode SaaS (Software as a Service) à plusieurs écoles
simultanément, chacune disposant de son propre espace isolé.

### 1.2 Problématique adressée

- Suivi des notes et calcul des bulletins fait manuellement, source d'erreurs et de perte de temps.
- Absence de visibilité en temps réel des parents sur la scolarité de leurs enfants (notes,
  présences, paiements).
- Suivi des paiements de scolarité peu rigoureux, retards difficiles à détecter.
- Aucune solution logicielle abordable et adaptée aux moyens de paiement locaux (Mobile Money)
  pour les établissements de taille petite à moyenne.

### 1.3 Vision produit

Offrir à toute école togolaise — d'une classe unique à plusieurs centaines d'élèves — un outil de
gestion complet, gratuit dans ses fonctions essentielles, avec une montée en gamme payante
abordable (palier unique, facturation trimestrielle alignée sur le cycle de trésorerie réel d'une
école), sans dépendre d'une carte bancaire internationale.

### 1.4 Périmètre du document

Ce cahier des charges couvre :
- le backend applicatif (`school-management-api`, Laravel 12) exposant à la fois une interface web
  complète et une API REST consommée par l'application mobile ;
- l'application mobile compagnon (`school_management_app`, Flutter) ;
- le modèle économique SaaS et les règles de gestion associées (essai, plans, facturation).

Il ne couvre pas l'intégration technique détaillée de l'agrégateur de paiement Mobile Money,
prévue dans une phase ultérieure (voir §11.2).

---

## 2. Objectifs du projet

### 2.1 Objectifs généraux

- **OG1** — Permettre à une école de gérer entièrement sa scolarité (administrative et
  pédagogique) depuis une seule plateforme.
- **OG2** — Permettre à la plateforme d'héberger un nombre illimité d'écoles clientes, chacune
  totalement isolée des autres (aucune fuite de données entre établissements).
- **OG3** — Générer un revenu récurrent via un modèle freemium adapté au pouvoir d'achat et aux
  habitudes de paiement togolaises.
- **OG4** — Donner aux parents et élèves un accès direct et en temps réel aux informations qui les
  concernent (notes, présences, scolarité).

### 2.2 Objectifs spécifiques mesurables

| Réf. | Objectif |
|---|---|
| OS1 | Réduire à zéro le calcul manuel des moyennes et des rangs (calcul automatique pondéré par les coefficients). |
| OS2 | Générer un bulletin PDF pour un élève en moins de 5 secondes. |
| OS3 | Permettre l'inscription complète d'une nouvelle école (auto-service, sans intervention humaine) en moins de 5 minutes. |
| OS4 | Garantir qu'aucune requête applicative ne retourne de données appartenant à une autre école (isolation multi-tenant à 100 %). |
| OS5 | Permettre à une école de passer du plan Gratuit au plan Premium en moins de 3 clics (hors confirmation de paiement). |

---

## 3. Acteurs du système

| Acteur | Description | Rattachement |
|---|---|---|
| **Super-administrateur** | Exploitant de la plateforme École Manager. Gère l'ensemble des écoles clientes, les abonnements et les factures. | Plateforme (aucune école) |
| **Administrateur d'école** | Responsable de l'établissement (direction, secrétariat). Gère la configuration de son école, le personnel, les élèves, les finances. | Une école |
| **Enseignant** | Dispense des cours, saisit les notes et les présences de ses classes/matières. | Une école |
| **Élève** | Consulte ses propres notes, présences, bulletins et sa situation financière (plan Premium). | Une école |
| **Parent / Tuteur** | Consulte les informations scolaires de son ou ses enfants. | Une école, lié à un ou plusieurs élèves |
| **Visiteur (prospect)** | Personne non authentifiée consultant la page d'inscription pour créer le compte de son école. | Aucun |

---

## 4. Modèle économique (SaaS)

### 4.1 Principe : freemium

| Plan | Tarif | Contenu |
|---|---|---|
| **Gratuit** | 0 FCFA, illimité dans le temps et en volume | Gestion des classes, élèves, enseignants, matières, notes, présences, emploi du temps. Le cœur de gestion d'une école, sans restriction de durée ni de nombre d'élèves. |
| **Premium** | 15 000 FCFA / trimestre (≈ 45 000 FCFA / an), tarif unique quelle que soit la taille de l'école | Tout le plan Gratuit, plus : bulletins PDF (moyenne, rang, appréciation automatiques), suivi des paiements de scolarité, annonces et notifications, espaces self-service élève et parent, gestion des comptes utilisateurs (provisioning élèves/enseignants/parents). |

**Justification du choix freemium** (plutôt que des paliers tarifaires par effectif) : simplicité
de vente et de compréhension dans un marché peu habitué aux logiciels payants ; le plan Gratuit
sert de produit d'appel et de preuve de valeur avant la conversion au Premium.

### 4.2 Essai

- Toute école nouvellement inscrite bénéficie de **30 jours d'essai Premium complet et gratuit**.
- À l'expiration de l'essai, si aucun abonnement payé n'est actif, l'école **repasse
  automatiquement au plan Gratuit** (aucune coupure de service, aucune perte de données — seules
  les fonctionnalités Premium deviennent inaccessibles).

### 4.3 Facturation

- **Cycle trimestriel**, aligné sur le rythme réel d'encaissement des frais de scolarité par les
  écoles togolaises (plutôt qu'un cycle mensuel, inadapté à leur trésorerie).
- Une **Facture** est un document numérique (statuts : en attente, payée, en retard, annulée)
  associée à une école et, une fois payée, à un **Abonnement** (période de 3 mois avec date de
  début/fin).
- Montant fixe : 15 000 FCFA par facture/trimestre (configurable sans migration de base de
  données).

### 4.4 Modalités de paiement

- **Moyens visés** : Mobile Money (Flooz/Moov Money, TMoney/Togocom) en priorité, complétés par
  virement bancaire ou espèces pour les cas particuliers.
- **État actuel** : aucune intégration automatique d'agrégateur de paiement. Le parcours est :
  1. L'administrateur d'école clique sur « Passer au Premium » → une facture « en attente » est
     créée et les instructions de paiement (numéro Mobile Money, référence à communiquer) sont
     affichées.
  2. L'école effectue le paiement par ses propres moyens (hors plateforme) et communique la
     référence de transaction à l'exploitant.
  3. Le super-administrateur **confirme manuellement** le paiement dans le back-office
     (méthode utilisée, référence de transaction) — ce qui active automatiquement l'abonnement et
     fait basculer l'école sur le plan Premium.
- **Évolution prévue** (hors périmètre de la version actuelle) : intégration d'une API de paiement
  (agrégateur type CinetPay/PayDunya, ou API directe des opérateurs) pour automatiser entièrement
  les étapes 2 et 3 via un webhook de confirmation. L'architecture de données (champs
  `methode_paiement`, `reference_transaction` sur la facture) est conçue pour absorber ce
  changement sans modification de schéma.

### 4.5 Règles de gestion liées à la facturation

| Réf. | Règle |
|---|---|
| RG1 | Une école ne peut avoir qu'un seul abonnement « actif » à la fois. |
| RG2 | Le passage au Premium ne nécessite aucune validation humaine côté école — la facture est créée immédiatement à la demande. |
| RG3 | La confirmation de paiement ne peut être effectuée que par un super-administrateur. |
| RG4 | Une école dont le statut est « suspendu » perd l'accès à toute la plateforme (hors page d'abonnement et déconnexion), quel que soit son plan. |
| RG5 | Un essai expiré sans abonnement payé fait perdre l'accès Premium mais jamais l'accès aux fonctions Gratuit. |

---

## 5. Périmètre fonctionnel détaillé

### 5.1 Back-office plateforme (super-administrateur)

| Réf. | Exigence fonctionnelle |
|---|---|
| RF-001 | Lister toutes les écoles inscrites avec leur plan, statut, nombre d'utilisateurs et date de fin d'essai. |
| RF-002 | Suspendre une école (coupe l'accès, conserve les données). |
| RF-003 | Réactiver une école suspendue. |
| RF-004 | Lister toutes les factures de toutes les écoles, avec leur statut. |
| RF-005 | Confirmer manuellement le paiement d'une facture (méthode, référence), ce qui active l'abonnement Premium correspondant et réactive l'école si elle était suspendue pour impayé. |

### 5.2 Inscription et authentification

| Réf. | Exigence fonctionnelle |
|---|---|
| RF-010 | Permettre à un visiteur de créer un compte d'école en self-service (nom de l'école, ville, téléphone, identité et identifiants de l'administrateur). |
| RF-011 | À l'inscription, créer automatiquement une année scolaire et un premier trimestre par défaut pour que l'école soit immédiatement opérationnelle. |
| RF-012 | Authentification par email/mot de passe, avec limitation du nombre de tentatives (protection contre les attaques par force brute). |
| RF-013 | Réinitialisation de mot de passe : un mot de passe temporaire est envoyé exclusivement par email, jamais affiché à l'écran ni journalisé. |
| RF-014 | Un utilisateur doit pouvoir changer son mot de passe depuis son profil. |

### 5.3 Gestion administrative (plan Gratuit)

| Réf. | Exigence fonctionnelle |
|---|---|
| RF-020 | CRUD complet des classes (nom, niveau, année scolaire, professeur principal, capacité). |
| RF-021 | CRUD complet des élèves (identité, classe, statut : actif/inactif/diplômé/exclu, contact d'urgence). |
| RF-022 | CRUD complet des enseignants, avec affectation à des combinaisons matière × classe. |
| RF-023 | CRUD complet des matières (nom, code, coefficient par défaut utilisé dans le calcul de moyenne). |
| RF-024 | CRUD complet des tuteurs/parents, avec association à un ou plusieurs élèves et un lien de parenté par élève. |
| RF-025 | Gestion des années scolaires et trimestres (référentiel temporel utilisé par les notes, présences et bulletins). |
| RF-026 | Saisie des notes par enseignant, unitaire ou groupée (toute une classe en une fois), avec type (devoir/composition), barème et coefficient. |
| RF-027 | Rapports de notes : moyenne par classe pour un trimestre donné. |
| RF-028 | Saisie des présences par enseignant, unitaire ou par appel de classe complet (statuts : présent/absent/retard, motif). |
| RF-029 | Consultation et gestion de l'emploi du temps hebdomadaire par classe (jour, créneau horaire, matière, enseignant, salle). |

### 5.4 Fonctionnalités Premium

| Réf. | Exigence fonctionnelle |
|---|---|
| RF-040 | Génération d'un bulletin PDF par élève et par trimestre : moyenne par matière (pondérée par le coefficient de chaque note), moyenne générale (pondérée par le coefficient de chaque matière), rang dans la classe, appréciation automatique. |
| RF-041 | Génération en masse des bulletins de toute une classe pour un trimestre donné. |
| RF-042 | Suivi des paiements de scolarité par élève (type, montant dû, montant payé, échéance, statut calculé automatiquement : payé/partiel/en attente/en retard). |
| RF-043 | Publication d'annonces ciblées (tous, parents, enseignants, élèves, ou une classe spécifique). |
| RF-044 | Notification automatique en application des destinataires concernés à la publication d'une annonce. |
| RF-045 | Centre de notifications personnel avec marquage lu/non lu. |
| RF-046 | Provisioning de comptes utilisateurs (génération d'identifiants) pour les élèves, enseignants et tuteurs disposant d'une adresse email, avec envoi des identifiants exclusivement par email. |
| RF-047 | Réinitialisation de mot de passe d'un compte utilisateur par l'administrateur d'école. |
| RF-048 | Espace self-service élève : consultation de ses notes par trimestre, de ses présences, de sa situation financière, téléchargement de son bulletin. |
| RF-049 | Espace self-service parent : liste de ses enfants, puis pour chacun : moyenne, taux de présence, paiements, accès au bulletin. |

### 5.5 Tableau de bord

| Réf. | Exigence fonctionnelle |
|---|---|
| RF-060 | Tableau de bord administrateur : effectifs (élèves actifs, enseignants, classes), paiements en retard, dernières notes saisies, taux de présence global, dernières annonces. |
| RF-061 | Tableau de bord enseignant : ses classes avec effectif, ses dernières notes saisies, dernières annonces. |
| RF-062 | Tableau de bord élève : moyenne du trimestre en cours, taux de présence, solde dû, dernières notes, annonces. |
| RF-063 | Tableau de bord parent : une carte par enfant avec moyenne et taux de présence courants, dernières annonces. |
| RF-064 | Toutes les données affichées sont calculées en temps réel à partir des données réelles (aucune donnée factice/statique). |

### 5.6 Application mobile (Flutter)

| Réf. | Exigence fonctionnelle |
|---|---|
| RF-070 | Authentification mobile via l'API REST (jeton Sanctum). |
| RF-071 | Consultation des classes, élèves, enseignants, matières, notes, présences selon le rôle connecté. |
| RF-072 | Les écrans d'administration (ajout classe/élève/enseignant) reproduisent les fonctionnalités web cœur (plan Gratuit). |

---

## 6. Règles d'autorisation (matrice des droits)

| Module | Super-admin | Admin école | Enseignant | Élève | Parent |
|---|---|---|---|---|---|
| Back-office plateforme (écoles, factures) | Lecture/Écriture | — | — | — | — |
| Classes / Matières / Tuteurs (CRUD) | — | Lecture/Écriture | Lecture | — | — |
| Élèves / Enseignants (CRUD) | — | Lecture/Écriture | Lecture (élèves) | — | — |
| Notes (saisie) | — | Lecture/Écriture | Lecture/Écriture (ses classes) | Lecture (les siennes) | Lecture (ses enfants) |
| Présences (saisie) | — | Lecture/Écriture | Lecture/Écriture (ses classes) | Lecture (les siennes) | Lecture (ses enfants) |
| Bulletins (génération) | — | Écriture | Écriture (ses classes) | Lecture (le sien) | Lecture (ses enfants) |
| Paiements scolarité | — | Lecture/Écriture | — | Lecture (le sien) | Lecture (ses enfants) |
| Annonces | — | Écriture | Écriture | Lecture (ciblée) | Lecture (ciblée) |
| Comptes utilisateurs | — | Lecture/Écriture | — | — | — |
| Abonnement / Facturation école | — | Lecture/Écriture | — | — | — |

Principe directeur : un utilisateur ne peut **jamais** accéder, même en lecture, aux données d'une
autre école que la sienne (cf. §8.2). Un élève/parent ne voit que ses propres données ou celles de
ses enfants déclarés. Un enseignant ne modifie que les notes/présences des classes et matières qui
lui sont affectées.

---

## 7. Exigences non-fonctionnelles

| Réf. | Catégorie | Exigence |
|---|---|---|
| RNF-001 | Sécurité | Toutes les routes (web et API) requièrent une authentification, à l'exception de la page de connexion et de la page d'inscription. |
| RNF-002 | Sécurité | Les mots de passe sont systématiquement hachés (bcrypt) et jamais journalisés ni renvoyés en clair dans une réponse HTTP. |
| RNF-003 | Sécurité | Limitation du débit (rate limiting) sur les tentatives de connexion. |
| RNF-004 | Sécurité | Validation systématique de toute entrée utilisateur côté serveur avant écriture en base. |
| RNF-005 | Sécurité | Isolation stricte des données entre écoles (multi-tenant) — vérifiée par des tests automatisés. |
| RNF-006 | Performance | Génération d'un bulletin PDF en moins de 5 secondes pour un élève. |
| RNF-007 | Localisation | Interface entièrement en français ; montants exprimés en FCFA. |
| RNF-008 | Disponibilité | Suspension d'une école pour impayé n'entraîne aucune perte de données — réversible à tout moment par confirmation de paiement. |
| RNF-009 | Maintenabilité | Couverture par tests automatisés des parcours critiques (authentification, autorisation par rôle, isolation multi-tenant, facturation). |
| RNF-010 | Compatibilité | Interface web responsive ; application mobile Android/iOS via Flutter. |

---

## 8. Architecture technique

### 8.1 Vue d'ensemble

```
                     ┌────────────────────────┐
                     │   Navigateur (web)      │
                     └───────────┬─────────────┘
                                 │ HTTP (sessions, Blade)
┌────────────────────────────────▼─────────────────────────────────┐
│                   school-management-api (Laravel 12)              │
│  ┌──────────────┐  ┌──────────────┐  ┌─────────────────────────┐  │
│  │  Routes web   │  │  Routes API  │  │  Middlewares            │  │
│  │  (Blade)      │  │  (Sanctum)   │  │  auth / role / premium  │  │
│  └──────┬───────┘  └──────┬───────┘  │  ecole.active            │  │
│         │                  │          └─────────────────────────┘  │
│  ┌──────▼──────────────────▼───────┐                               │
│  │     Contrôleurs + Policies       │                               │
│  └──────────────┬───────────────────┘                               │
│  ┌──────────────▼───────────────────┐                               │
│  │  Modèles Eloquent (scope ecole)   │                               │
│  └──────────────┬───────────────────┘                               │
└─────────────────┼─────────────────────────────────────────────────┘
                   │
            ┌──────▼──────┐
            │  Base de     │   (SQLite en dev — MySQL/PostgreSQL visé en prod)
            │  données     │
            └─────────────┘
                   ▲
                   │ HTTP (JSON, jeton Sanctum)
       ┌───────────┴────────────┐
       │  school_management_app  │  (Flutter — Android / iOS)
       └─────────────────────────┘
```

### 8.2 Modèle multi-tenant

- **Stratégie** : base de données unique, schéma partagé — chaque table métier porte une colonne
  `ecole_id`. Choix retenu pour son coût d'infrastructure minimal, cohérent avec la cible (petites
  et moyennes écoles, pas de budget pour une infrastructure dédiée par client).
- **Isolation automatique** : un global scope Eloquent filtre toute requête par l'école de
  l'utilisateur connecté ; toute création de donnée se voit assigner automatiquement l'école
  courante. Le modèle `Utilisateur` est volontairement exclu de ce mécanisme générique (la
  résolution de l'utilisateur authentifié ne doit pas dépendre d'elle-même) et fait l'objet d'un
  filtrage explicite codé au cas par cas.
- **Rôle plateforme** : le super-administrateur n'est rattaché à aucune école et n'est jamais
  soumis au filtre — il a une vue globale nécessaire à l'exploitation commerciale.

### 8.3 Stack technique

| Couche | Technologie |
|---|---|
| Backend | PHP 8.3, Laravel 12 |
| Authentification web | Sessions Laravel |
| Authentification API | Laravel Sanctum (jetons) |
| Base de données | SQLite (développement), compatible MySQL/PostgreSQL (production) |
| Génération PDF | barryvdh/laravel-dompdf |
| Frontend web | Blade, Tailwind CSS, Alpine.js, Chart.js |
| Application mobile | Flutter (Dart), Android & iOS |
| Tests | PHPUnit (tests Feature Laravel) |

### 8.4 Modèle de données (entités principales)

| Entité | Rôle |
|---|---|
| `Ecole` | Le tenant. Statut (essai/actif/suspendu/expiré), plan (gratuit/premium), date de fin d'essai. |
| `Abonnement` | Période Premium active liée à une école (dates, montant, statut). |
| `Facture` | Demande de paiement (montant, échéance, statut, méthode et référence de paiement). |
| `Utilisateur` | Compte applicatif (rôle : super-admin/admin/enseignant/élève/parent), rattaché à une école (sauf super-admin). |
| `AnneeScolaire`, `Trimestre` | Référentiel temporel. |
| `Classe`, `Matiere`, `Enseignant`, `Eleve`, `Tuteur` | Référentiels métier de l'établissement. |
| `Note`, `Presence` | Données pédagogiques rattachées à un trimestre. |
| `CreneauHoraire` | Emploi du temps. |
| `Paiement` | Paiement de scolarité d'un élève (distinct de `Facture`, qui concerne l'abonnement SaaS de l'école). |
| `Annonce`, `Notification` | Communication. |
| `Bulletin` | Métadonnées du bulletin généré (moyenne, rang, chemin du PDF). |

---

## 9. Contraintes

| Réf. | Contrainte |
|---|---|
| C1 | L'adresse email d'un utilisateur est unique sur l'ensemble de la plateforme (et non par école) — c'est l'identifiant de connexion. |
| C2 | Aucune carte bancaire internationale ne peut être exigée des écoles clientes — les moyens de paiement doivent rester accessibles localement (Mobile Money, virement, espèces). |
| C3 | L'intégration d'une API de paiement automatisée est hors périmètre de la version actuelle ; la confirmation de paiement est manuelle (super-administrateur). |
| C4 | L'interface doit rester utilisable sur des connexions à bande passante limitée. |
| C5 | Toute évolution du modèle tarifaire (nombre de plans, montants) doit être réalisable sans migration de base de données lourde. |

---

## 10. Hors périmètre (version actuelle)

- Intégration technique réelle d'un agrégateur de paiement Mobile Money.
- Envoi de SMS aux parents (mentionné comme évolution possible du plan Premium).
- Export comptable avancé.
- Multi-langue (anglais notamment, pour une expansion régionale au-delà du Togo francophone).
- Application mobile pour les parents/élèves (l'app Flutter actuelle est orientée gestion
  administrative).

---

## 11. Roadmap / Évolutions prévues

### 11.1 Court terme
- Intégration d'une API de paiement Mobile Money (CinetPay, PayDunya, ou API opérateur directe)
  pour automatiser la confirmation des factures Premium (webhook remplaçant la confirmation
  manuelle du super-administrateur).

### 11.2 Moyen terme
- Notifications par SMS aux parents (absences, nouvelles notes).
- Export comptable des paiements de scolarité.
- Plan « Premium Plus » pour les grands établissements (>300 élèves), avec support prioritaire.

### 11.3 Long terme
- Extension régionale (autres pays d'Afrique de l'Ouest, autres opérateurs Mobile Money).
- Application mobile dédiée aux parents/élèves (consultation seule).

---

## 12. Glossaire

| Terme | Définition |
|---|---|
| **Tenant** | Une école cliente, isolée des autres sur la plateforme partagée. |
| **SaaS** | Software as a Service — logiciel fourni en ligne par abonnement, sans installation locale. |
| **Freemium** | Modèle économique combinant un niveau de service gratuit et un niveau payant (Premium). |
| **Mobile Money** | Service de paiement mobile (Flooz/Moov Money, TMoney/Togocom) très répandu au Togo. |
| **FCFA** | Franc CFA, monnaie utilisée au Togo. |
| **Bulletin** | Document récapitulant les notes et la moyenne d'un élève pour une période donnée. |
| **Trimestre** | Période de l'année scolaire (généralement 3 par an) servant de référentiel pour les notes et bulletins. |
| **Global scope** | Mécanisme technique Laravel filtrant automatiquement toutes les requêtes d'un modèle selon une condition (ici : l'école de l'utilisateur connecté). |
