# Tests de charge k6

Mesurent combien d'utilisateurs simultanés École Manager supporte, et quelles pages ralentissent en premier.

**Ne jamais lancer ces tests sur le site en ligne** : ils écrivent des données (bulletins) et consomment les limites de Render et de Neon. Toujours utiliser une copie de test.

## 1. Préparer une base de test

```bash
# Base SQLite séparée (ou une base PostgreSQL de test via DB_URL)
export DB_CONNECTION=sqlite DB_DATABASE=/chemin/charge-test.sqlite DB_URL=
php artisan migrate:fresh --force
php artisan db:seed --class=ChargeDeTestSeeder --force
```

Par défaut : 20 écoles, 6 classes de 17 élèves chacune (2 040 élèves), notes, présences, paiements et 10 parents par école.
Variables : `CHARGE_ECOLES`, `CHARGE_ELEVES_PAR_CLASSE`. Le générateur refuse de tourner si `APP_ENV=production`.

Comptes (mot de passe `charge-test`) : `directeur{n}@charge.test`, `enseignant{n}-{k}@charge.test`, `parent{n}-{k}@charge.test`.

## 2. Lancer k6

```bash
# Vérification rapide : 1 directeur et 1 parent pendant 45 s
k6 run -e BASE_URL=http://127.0.0.1:8000 tests/charge/ecole-manager.js

# Une matinée chargée : 20 directeurs et 40 parents pendant 4 min 30
k6 run -e PROFIL=charge -e BASE_URL=https://copie-de-test.onrender.com tests/charge/ecole-manager.js

# Le jour des bulletins : 150 parents, et la génération de bulletins en parallèle
k6 run -e PROFIL=pic -e BULLETINS=1 -e BASE_URL=https://copie-de-test.onrender.com tests/charge/ecole-manager.js
```

Seuils : moins de 1 % d'erreurs, 95 % des pages en moins de 2 s, une classe entière de bulletins en moins de 30 s.

## 3. Où lancer la vraie mesure

`php artisan serve` ne traite qu'une requête à la fois : il sert à vérifier les scénarios, pas à mesurer la capacité.
Pour une mesure qui vaut pour la production, déployer une copie du site sur Render avec le même type de serveur que la production (Starter, 512 Mo), une base Neon séparée remplie avec le générateur, lancer k6, puis supprimer la copie.
