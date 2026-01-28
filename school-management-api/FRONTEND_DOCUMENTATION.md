# School Manager - Frontend Documentation

## 📋 Overview

Ce frontend Laravel + Tailwind CSS a été développé pour le système de gestion d'école School Manager. Il offre une interface moderne, responsive et intuitive pour gérer tous les aspects d'un établissement scolaire.

## 🏗️ Architecture

### Structure des dossiers

```
resources/views/
├── layouts/
│   ├── app.blade.php              # Layout principal
│   ├── navigation.blade.php       # Sidebar navigation
│   └── header.blade.php          # Top header
├── components/
│   ├── flash-messages.blade.php   # Messages flash animés
│   ├── card.blade.php             # Cartes configurables
│   ├── button.blade.php           # Boutons avec variantes
│   ├── input.blade.php            # Champs de formulaire
│   └── table.blade.php            # Tableaux de données
├── auth/
│   └── login.blade.php            # Page de connexion
├── dashboard.blade.php            # Tableau de bord
├── classes/
│   └── index.blade.php            # Gestion des classes
├── eleves/
│   └── index.blade.php            # Gestion des élèves
├── enseignants/
│   └── index.blade.php            # Gestion des enseignants
├── presences/
│   └── index.blade.php            # Gestion des présences
├── notes/
│   └── index.blade.php            # Gestion des notes
├── profile.blade.php              # Profile utilisateur
└── settings.blade.php             # Paramètres système
```

## 🎨 Design System

### Couleurs principales
- **Bleu primaire**: `#3B82F6` (blue-600)
- **Vert succès**: `#10B981` (green-600)
- **Rouge erreur**: `#EF4444` (red-600)
- **Jaune avertissement**: `#F59E0B` (yellow-600)
- **Gris neutre**: `#6B7280` (gray-600)

### Typographie
- **Police principale**: Inter (Google Fonts)
- **Tailles**: `text-xs` → `text-3xl`
- **Poids**: `font-medium`, `font-semibold`, `font-bold`

### Espacements
- **Base**: `4px` (0.25rem)
- **Échelle**: 1, 2, 3, 4, 6, 8, 12, 16

## 🧩 Composants

### 1. Layout Components

#### `app.blade.php`
Layout principal avec:
- Configuration Alpine.js
- Integration Chart.js
- Support mode sombre
- Structure responsive

#### `navigation.blade.php`
Sidebar avec:
- Menu par rôle (Admin, Enseignant, Élève, Parent)
- Navigation responsive mobile
- Indicateurs actifs
- Support dark mode

#### `header.blade.php`
Header avec:
- Barre de recherche
- Toggle dark mode
- Notifications dropdown
- Menu utilisateur

### 2. UI Components

#### `card.blade.php`
```blade
<x-card title="Titre" subtitle="Sous-titre">
    Contenu de la carte
</x-card>
```

#### `button.blade.php`
```blade
<x-button variant="primary" size="md" icon="path/to/icon">
    Bouton
</x-button>
```

#### `input.blade.php`
```blade
<x-input name="email" type="email" label="Email" required />
```

#### `table.blade.php`
```blade
<x-table :headers="$headers" :data="$data" actions="true">
</x-table>
```

#### `flash-messages.blade.php`
Messages flash automatiques avec:
- Success, Error, Warning, Info
- Auto-dismiss après 5-8 secondes
- Animations Alpine.js
- Support validation errors

## 📱 Pages

### 1. Dashboard (`dashboard.blade.php`)
- **Statistiques**: Cartes avec icônes et compteurs
- **Graphiques**: Tendance des présences (Chart.js)
- **Activité récente**: Timeline des dernières actions
- **Actions rapides**: Raccourcis vers fonctionnalités principales

### 2. Gestion des Classes (`classes/index.blade.php`)
- **Filtres avancés**: Niveau, statut, année scolaire
- **Tableau**: Liste avec pagination
- **CRUD**: Création, modification, suppression
- **Modal**: Formulaire modal pour édition
- **Progress bars**: Effectif des classes

### 3. Gestion des Élèves (`eleves/index.blade.php`)
- **Recherche**: Nom, matricule, classe
- **Avatars**: Initiales avec gradient
- **Filtres**: Genre, statut, classe
- **Actions**: Voir, modifier, supprimer
- **Modal**: Formulaire complet élève

### 4. Gestion des Enseignants (`enseignants/index.blade.php`)
- **Spécialités**: Filtre par matière
- **Classes assignées**: Affichage des classes
- **Informations**: Contact, date d'embauche
- **CRUD**: Gestion complète

### 5. Gestion des Présences (`presences/index.blade.php`)
- **Statistiques**: Présents, absents, retards
- **Saisie individuelle**: Par élève
- **Saisie en masse**: Par classe
- **Filtres**: Date, classe, statut
- **Motifs**: Commentaires sur absences

### 6. Gestion des Notes (`notes/index.blade.php`)
- **Statistiques**: Moyenne, min, max
- **Conversion**: Note/20 automatique
- **Types**: Devoir, interrogation, examen
- **Visualisation**: Barres de progression colorées
- **Bulk**: Saisie en masse par classe

### 7. Profile (`profile.blade.php`)
- **Informations**: Nom, email, téléphone
- **Avatar**: Initiales avec gradient
- **Mot de passe**: Changement sécurisé
- **Préférences**: Dark mode, notifications, langue

### 8. Paramètres (`settings.blade.php`)
- **Tabs**: Général, Établissement, Académique, Notifications, Sécurité
- **Configuration**: Paramètres système
- **Toggles**: Options activables/désactivables
- **Validation**: Sauvegarde avec feedback

## 🎯 Fonctionnalités

### 1. Responsive Design
- **Mobile-first**: Adapté mobile/desktop
- **Breakpoints**: sm (640px), md (768px), lg (1024px), xl (1280px)
- **Navigation**: Sidebar adaptative mobile
- **Tables**: Scroll horizontal sur mobile

### 2. Dark Mode
- **Toggle**: Bouton dans header
- **Persistant**: Sauvegardé dans localStorage
- **Transitions**: Animations fluides
- **Composants**: Tous adaptés dark/light

### 3. Interactivité Alpine.js
- **Recherche**: Filtres en temps réel
- **Modals**: Ouverture/fermeture animée
- **Forms**: Validation côté client
- **Toggles**: Switches interactifs
- **Tabs**: Navigation par onglets

### 4. Animations
- **Transitions**: Alpine.js x-transition
- **Hover effects**: Sur tous les éléments interactifs
- **Loading states**: Spinners et disabled states
- **Micro-interactions**: Feedback visuel

### 5. Accessibilité
- **ARIA**: Labels et descriptions
- **Keyboard**: Navigation au clavier
- **Contrast**: Ratios WCAG respectés
- **Focus**: États visibles clairs

## 🔧 Technologies

### Frontend Stack
- **Laravel Blade**: Templating engine
- **Tailwind CSS v4**: Utility-first CSS
- **Alpine.js**: Reactive JavaScript
- **Chart.js**: Graphiques et visualisations
- **Inter**: Police principale (Google Fonts)

### Features
- **Component-based**: Architecture modulaire
- **Utility classes**: Tailwind CSS
- **Reactive data**: Alpine.js
- **Charts**: Chart.js intégré
- **Forms**: Validation et feedback

## 📊 Données

### Structure des données
```javascript
// Élève
{
    id: 1,
    nom: 'Dupont',
    prenom: 'Jean',
    matricule: 'ELE2024001',
    classe: { id: 1, nom: '6ème A' },
    statut: 'actif'
}

// Note
{
    id: 1,
    note: 15,
    note_sur: 20,
    type_evaluation: 'devoir',
    trimestre: 1,
    eleve: { ... },
    matiere: { ... }
}
```

### API Integration
- **Endpoints**: Routes Laravel API existantes
- **Axios**: Requêtes HTTP
- **Error handling**: Messages d'erreur
- **Loading states**: Indicateurs de chargement

## 🚀 Déploiement

### Build Process
```bash
# Installation des dépendances
npm install

# Build CSS/JS
npm run build

# Development
npm run dev
```

### Configuration
- **Vite**: Build tool
- **Tailwind**: PostCSS plugin
- **Autoprefixer**: Compatibility

## 🎨 Personnalisation

### Thèmes
- **Colors**: Modifier variables Tailwind
- **Fonts**: Changer police dans `app.css`
- **Spacing**: Adapter échelle d'espacement
- **Components**: Personnaliser composants

### Extensions
- **New components**: Créer dans `components/`
- **New pages**: Ajouter dans dossiers spécifiques
- **New features**: Étendre architecture existante

## 📝 Notes importantes

1. **Authentification**: Intégration avec Laravel Sanctum
2. **Permissions**: Gestion par rôle dans sidebar
3. **Validation**: Côté client et serveur
4. **Performance**: Lazy loading des images
5. **SEO**: Meta tags et structured data

## 🔍 Maintenance

### Updates
- **Dependencies**: npm update régulier
- **Tailwind**: Mise à jour v4+
- **Security**: Review des dépendances
- **Performance**: Monitoring des temps de chargement

### Best Practices
- **Code review**: Revue du code avant déploiement
- **Testing**: Tests navigateurs multiples
- **Accessibility**: Audit WCAG régulier
- **Performance**: Optimisation continue

---

**Ce frontend est prêt à être connecté à votre API Laravel existante et offre une expérience utilisateur moderne et professionnelle !** 🎉
