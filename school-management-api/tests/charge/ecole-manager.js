// Test de charge k6 d'École Manager.
//
// À lancer UNIQUEMENT sur une copie de test remplie avec ChargeDeTestSeeder, jamais sur le site en ligne.
//
//   k6 run tests/charge/ecole-manager.js                                   (vérification rapide, 1 utilisateur)
//   k6 run -e PROFIL=charge -e BASE_URL=https://copie-de-test.example tests/charge/ecole-manager.js
//
// Variables : BASE_URL (défaut http://127.0.0.1:8000), PROFIL (fumee | charge | pic),
// ECOLES (nombre d'écoles créées par le seeder, défaut 20), BULLETINS=1 pour inclure la génération de bulletins.

import http from 'k6/http';
import { check, group, sleep } from 'k6';

const BASE = (__ENV.BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
const ECOLES = parseInt(__ENV.ECOLES || '20', 10);
const MOT_DE_PASSE = 'charge-test';
const PROFIL = __ENV.PROFIL || 'fumee';

// Combien d'utilisateurs simultanés pour chaque profil
const PROFILS = {
  // Vérifie que le script fonctionne : 1 directeur, 1 parent, 30 secondes
  fumee: {
    directeurs: [{ duration: '1s', target: 1 }, { duration: '45s', target: 1 }],
    parents: [{ duration: '1s', target: 1 }, { duration: '45s', target: 1 }],
  },
  // Une matinée chargée : 20 directeurs et secrétaires, 40 parents
  charge: {
    directeurs: [{ duration: '1m', target: 20 }, { duration: '3m', target: 20 }, { duration: '30s', target: 0 }],
    parents: [{ duration: '1m', target: 40 }, { duration: '3m', target: 40 }, { duration: '30s', target: 0 }],
  },
  // Le jour de la sortie des bulletins : beaucoup de parents d'un coup
  pic: {
    directeurs: [{ duration: '1m', target: 20 }, { duration: '2m', target: 20 }, { duration: '30s', target: 0 }],
    parents: [{ duration: '1m', target: 150 }, { duration: '2m', target: 150 }, { duration: '30s', target: 0 }],
  },
};

const plan = PROFILS[PROFIL] || PROFILS.fumee;

const scenarios = {
  directeurs: { executor: 'ramping-vus', exec: 'directeur', startVUs: 0, stages: plan.directeurs, gracefulRampDown: '10s' },
  parents: { executor: 'ramping-vus', exec: 'parent', startVUs: 0, stages: plan.parents, gracefulRampDown: '10s' },
};

if (__ENV.BULLETINS === '1') {
  // La tâche la plus lourde : générer les bulletins PDF d'une classe entière, 2 directeurs en même temps
  scenarios.bulletins = { executor: 'constant-vus', exec: 'bulletins', vus: 2, duration: PROFIL === 'fumee' ? '30s' : '4m' };
}

export const options = {
  scenarios,
  // Garder la session entre deux tours : un vrai utilisateur ne se reconnecte pas à chaque page
  noCookiesReset: true,
  thresholds: {
    // Moins de 1 % d'erreurs
    http_req_failed: ['rate<0.01'],
    // 95 % des pages en moins de 2 secondes (hors génération de bulletins)
    'http_req_duration{lourd:non}': ['p(95)<2000'],
    // Une classe entière de bulletins en moins de 30 secondes
    'http_req_duration{page:generer_bulletins}': ['p(95)<30000'],
    checks: ['rate>0.99'],
  },
};

// --- Outils -------------------------------------------------------------------------

const jeton = (html) => {
  const m = html.match(/name="_token"\s+value="([^"]+)"/) || html.match(/name="csrf-token"\s+content="([^"]+)"/);
  return m ? m[1] : '';
};

const page = (chemin, nom, lourd = false) => {
  const r = http.get(`${BASE}${chemin}`, { tags: { page: nom, lourd: lourd ? 'oui' : 'non' } });
  check(r, {
    [`${nom} : 200`]: (res) => res.status === 200,
    [`${nom} : toujours connecté`]: (res) => !res.url.includes('/login'),
  });
  return r;
};

// Connexion une seule fois par utilisateur virtuel ; k6 garde les cookies de session ensuite
const connexions = {};
const seConnecter = (email) => {
  if (connexions[email]) {
    return;
  }
  const formulaire = http.get(`${BASE}/login`, { tags: { page: 'login', lourd: 'non' } });
  const r = http.post(
    `${BASE}/login`,
    { _token: jeton(formulaire.body), email, password: MOT_DE_PASSE },
    { tags: { page: 'connexion', lourd: 'non' } },
  );
  connexions[email] = check(r, { 'connexion réussie': (res) => res.status === 200 && !res.url.includes('/login') });
};

const ecoleDuVu = () => ((__VU - 1) % ECOLES) + 1;
const pause = () => sleep(1 + Math.random() * 2);

// --- Scénarios ----------------------------------------------------------------------

// Un directeur (ou une secrétaire) qui parcourt son école
export function directeur() {
  seConnecter(`directeur${ecoleDuVu()}@charge.test`);

  group('directeur', () => {
    page('/dashboard', 'tableau_de_bord');
    pause();
    const liste = page('/eleves', 'liste_eleves');
    const ids = [...liste.body.matchAll(/\/eleves\/(\d+)"/g)].map((m) => m[1]);
    pause();
    if (ids.length) {
      page(`/eleves/${ids[Math.floor(Math.random() * ids.length)]}`, 'fiche_eleve');
      pause();
    }
    page('/notes', 'notes');
    pause();
    page('/paiements', 'paiements');
    pause();
    page('/presences', 'presences');
    pause();
  });
}

// Un parent qui regarde les notes de son enfant
export function parent() {
  const k = Math.floor((__VU - 1) / ECOLES) % 10 + 1;
  seConnecter(`parent${ecoleDuVu()}-${k}@charge.test`);

  group('parent', () => {
    const enfants = page('/mes-enfants', 'mes_enfants');
    const m = enfants.body.match(/\/mes-enfants\/(\d+)"/);
    pause();
    if (m) {
      page(`/mes-enfants/${m[1]}`, 'detail_enfant');
    }
    sleep(3 + Math.random() * 4);
  });
}

// Un directeur qui génère les bulletins d'une classe
export function bulletins() {
  seConnecter(`directeur${ecoleDuVu()}@charge.test`);

  const ecran = page('/bulletins', 'page_bulletins');
  const modele = ecran.body.match(/data-url-template="([^"]+)"/);
  const trimestre = ecran.body.match(/<option value="(\d+)"\s+selected/) || ecran.body.match(/name="trimestre_id"[^>]*>\s*<option value="(\d+)"/);

  if (modele && trimestre) {
    const url = modele[1].replace(/&amp;/g, '&').replace('__TRIMESTRE__', trimestre[1]);
    const r = http.post(url, { _token: jeton(ecran.body) }, { tags: { page: 'generer_bulletins', lourd: 'oui' }, timeout: '120s' });
    check(r, { 'bulletins générés': (res) => res.status === 200 && !res.url.includes('/login') });
  }
  sleep(20);
}
