# Déploiement — École Manager (Laravel)

L'application est déployée sur **Render** sous forme d'image Docker (`Dockerfile`), avec une base **PostgreSQL**. Chaque modification passe par des tests automatiques (GitHub Actions) avant d'arriver sur `master`, et Render ne déploie `master` que lorsque ces tests sont verts.

## 1. Le circuit d'une modification

```
feature/ma-modif  ──push──▶  CI (4 contrôles)  ──PR──▶  develop  ──PR──▶  master  ──▶  Render
```

| Branche | Rôle | Règle |
|---|---|---|
| `feature/…`, `fix/…` | Une modification en cours | Les contrôles tournent à chaque push |
| `develop` | Intégration, version de test | Modifiée uniquement par pull request, contrôles verts obligatoires |
| `master` | Production, déployée sur Render | Modifiée uniquement par pull request depuis `develop`, contrôles verts obligatoires |

Démarrer une modification :

```bash
git switch develop && git pull
git switch -c feature/nom-de-la-modif
# ... travail, commits ...
git push -u origin feature/nom-de-la-modif
gh pr create --base develop
```

## 2. Les contrôles automatiques

Workflow : `.github/workflows/ci-ecole-manager.yml`, lancé à chaque push sur n'importe quelle branche et sur chaque pull request vers `develop` ou `master`.

| Contrôle | Ce qu'il vérifie |
|---|---|
| **Qualité du code** | Style Laravel (`pint --test`), `composer.json` valide, aucune faille de sécurité connue dans les dépendances (`composer audit`) |
| **Tests (SQLite)** | Les 48 tests : unitaires, isolation entre écoles, parcours de bout en bout, toutes les pages avec chaque rôle |
| **Tests (PostgreSQL)** | Les migrations et les mêmes tests sur PostgreSQL 16, la base utilisée par Render |
| **Image Docker** | L'image déployée sur Render se construit sans erreur |

Lancer les mêmes contrôles sur votre poste avant de pousser :

```bash
vendor/bin/pint            # corrige le style
php artisan test           # 48 tests
composer audit             # failles connues
```

## 3. Première mise en service sur Render

1. Sur [render.com](https://render.com), **New → Blueprint**, choisir le dépôt `ecole-controle`. Render lit `render.yaml` et propose le service `ecole-manager` et la base `ecole-manager-db`.
2. Renseigner les variables marquées « à remplir » (`sync: false`) :
   - `APP_URL` : l'adresse donnée par Render (`https://ecole-manager-xxxx.onrender.com`) ou votre domaine ;
   - `PAYDUNYA_*` : clés de test d'abord ;
   - `MAIL_*` : un serveur SMTP, sinon aucun e-mail n'est envoyé.
3. Lancer le déploiement. Au démarrage, le conteneur met la configuration en cache, applique les migrations et lance le planificateur des rappels de paiement.
4. Créer le compte super_admin depuis l'onglet **Shell** du service : `php artisan create:admin`.
5. Vérifier : `php artisan app:verifier-deploiement` dans le même Shell.

Dans les réglages du service, **Auto-Deploy** doit être sur « After CI Checks Pass » (c'est ce que fixe `autoDeployTrigger: checksPass`).

## 4. Offre gratuite de Render : limites à connaître

| Limite | Conséquence | Solution |
|---|---|---|
| Le service gratuit s'endort après 15 minutes sans visite | La première page après une pause met environ une minute à s'afficher | Plan payant avant d'avoir des écoles clientes |
| **La base PostgreSQL gratuite expire 30 jours après sa création** | Toutes les données sont perdues | Passer la base en plan payant **avant** d'y mettre de vraies écoles |
| Pas de cron job gratuit | — | Le planificateur tourne dans le conteneur (`schedule:work`), mais il s'arrête quand le service s'endort |

Pour la phase de démonstration, l'offre gratuite suffit. Pour de vraies écoles : service web et base en plan payant.

## 5. Contrôle de la configuration : `php artisan app:verifier-deploiement`

La commande vérifie l'environnement, PHP et ses extensions, la base et les migrations, le compte super_admin, les droits d'écriture, le mode et la validité des clés PayDunya (appel réel à l'API), la configuration e-mail et le planificateur.

- `✘` : point bloquant (code de sortie 1).
- `!` : avertissement.
- `--sans-reseau` : sans appel à l'API PayDunya.

## 6. Mise en service de PayDunya

1. Tableau de bord PayDunya : passer l'application en **production** et récupérer les clés live.
2. Sur Render, renseigner les clés et `PAYDUNYA_MODE=live`, redéployer, puis lancer `php artisan app:verifier-deploiement`.
3. (Optionnel) Section IPN de PayDunya : `https://<votre-adresse>/paydunya/webhook`.
4. Faire un **vrai paiement de faible montant** et vérifier : facture « payée », école en Premium.
5. **Redistribution** : l'API de déboursement n'accepte que les clés live. Pour l'activer, `PAYDUNYA_REDISTRIBUTION_ENABLED=true`, puis un petit paiement en surveillant les journaux du service (« PayDunya redistribution effectuée » ou « refusée »).

Les clés PayDunya ne doivent jamais être commitées ni partagées.

## 7. Limites connues

- Les rapports PDF sont plafonnés à **500 lignes** (environ 7 s et 90 Mo).
- Le modèle `User` n'est pas filtré automatiquement par école : toute nouvelle requête sur les utilisateurs doit filtrer `ecole_id`. `IsolationEcolesTest` sert de garde-fou.
