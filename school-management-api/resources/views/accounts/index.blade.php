@extends('layouts.app')

@section('title', 'Gestion des Comptes')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            👥 Gestion des Comptes Utilisateurs
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">
            Créez manuellement les comptes des élèves et parents
        </p>
    </div>

    <!-- Onglets -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg mb-8">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex -mb-px">
                <button onclick="showTab('create')" id="create-tab" class="tab-button py-4 px-6 border-b-2 border-blue-500 font-medium text-blue-600 dark:text-blue-400">
                    ➕ Créer un compte
                </button>
                <button onclick="showTab('list')" id="list-tab" class="tab-button py-4 px-6 border-b-2 border-transparent font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    📋 Liste des comptes
                </button>
            </nav>
        </div>

        <!-- Onglet Création -->
        <div id="create-content" class="p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Créer un nouveau compte</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Formulaire Élève -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Nouveau Compte Élève
                    </h3>
                    
                    <form action="{{ route('accounts.create.manual') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="type" value="eleve">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom *</label>
                            <input type="text" name="nom" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prénom *</label>
                            <input type="text" name="prenom" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email *</label>
                            <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mot de passe (laisser vide pour générer automatiquement)</label>
                            <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Classe *</label>
                            <select name="classe_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="">Sélectionner une classe</option>
                                @foreach(\App\Models\Classe::all() as $classe)
                                <option value="{{ $classe->id }}">{{ $classe->nom }} ({{ $classe->niveau }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="send_email" value="1" class="mr-2">
                            <label class="text-sm text-gray-700 dark:text-gray-300">Envoyer les identifiants par email</label>
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                            Créer le compte élève
                        </button>
                    </form>
                </div>

                <!-- Formulaire Parent -->
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Nouveau Compte Parent
                    </h3>
                    
                    <form action="{{ route('accounts.create.manual') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="type" value="parent">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom *</label>
                            <input type="text" name="nom" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prénom *</label>
                            <input type="text" name="prenom" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email *</label>
                            <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Mot de passe (laisser vide pour générer automatiquement)</label>
                            <input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Téléphone</label>
                            <input type="tel" name="telephone" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Profession</label>
                            <input type="text" name="profession" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Enfant(s) à associer</label>
                            <select name="eleve_ids[]" multiple class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 dark:bg-gray-700 dark:text-white" size="4">
                                <option value="">Sélectionner un ou plusieurs élèves</option>
                                @foreach($eleves as $eleve)
                                <option value="{{ $eleve->id }}">{{ $eleve->prenom }} {{ $eleve->nom }} - {{ $eleve->classe->nom ?? 'Non assigné' }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maintenez Ctrl pour sélectionner plusieurs élèves</p>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="send_email" value="1" class="mr-2">
                            <label class="text-sm text-gray-700 dark:text-gray-300">Envoyer les identifiants par email</label>
                        </div>
                        
                        <button type="submit" class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors">
                            Créer le compte parent
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Onglet Liste -->
        <div id="list-content" class="p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📊 Statut des comptes existants</h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Élèves -->
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-3">👦 Élèves</h4>
                    <div class="space-y-2">
                        @foreach($eleves as $eleve)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer transition-colors" onclick="selectEleve({{ $eleve->id }}, '{{ $eleve->nom }}', '{{ $eleve->prenom }}', '{{ $eleve->classe->id ?? '' }}')">
                            <div class="flex items-center">
                                @if($eleve->user)
                                    <span class="text-green-600 mr-2">✅</span>
                                @else
                                    <span class="text-red-600 mr-2">❌</span>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $eleve->prenom }} {{ $eleve->nom }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $eleve->classe->nom ?? 'Non assigné' }}
                                    </div>
                                </div>
                            </div>
                            @if($eleve->user)
                                <form action="{{ route('accounts.reset-password', $eleve->user_id) }}" method="POST" class="inline" onclick="event.stopPropagation()">
                                    @csrf
                                    <button type="submit" class="text-sm bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700 transition-colors">
                                        🔄 Reset MDP
                                    </button>
                                </form>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Parents -->
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-3">👨‍👩‍👧‍👦 Parents</h4>
                    <div class="space-y-2">
                        @foreach($parents as $parent)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer transition-colors" onclick="selectParent({{ $parent->id }}, '{{ $parent->nom }}', '{{ $parent->prenom }}', '{{ $parent->email ?? '' }}', '{{ $parent->telephone ?? '' }}', '{{ $parent->profession ?? '' }}')">
                            <div class="flex items-center">
                                @if($parent->user)
                                    <span class="text-green-600 mr-2">✅</span>
                                @else
                                    <span class="text-red-600 mr-2">❌</span>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $parent->prenom }} {{ $parent->nom }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $parent->email ?? 'Pas d\'email' }}
                                    </div>
                                </div>
                            </div>
                            @if($parent->user)
                                <form action="{{ route('accounts.reset-password', $parent->user_id) }}" method="POST" class="inline" onclick="event.stopPropagation()">
                                    @csrf
                                    <button type="submit" class="text-sm bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700 transition-colors">
                                        🔄 Reset MDP
                                    </button>
                                </form>
                            @endif
                        </div>
                        @endforeach
                        
                        @if($parents->isEmpty())
                            <div class="text-gray-500 dark:text-gray-400 text-center py-4">
                                Aucun parent trouvé dans la base de données
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages de succès/erreur -->
    @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-green-800 dark:text-green-200">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-red-800 dark:text-red-200">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Résultats de création -->
    @if(session('account_created'))
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">✅ Compte créé avec succès</h3>
        
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Type:</p>
                    <p class="text-gray-700 dark:text-gray-300">{{ session('account_created.type') }}</p>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Nom:</p>
                    <p class="text-gray-700 dark:text-gray-300">{{ session('account_created.name') }}</p>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Email:</p>
                    <p class="text-gray-700 dark:text-gray-300">{{ session('account_created.email') }}</p>
                </div>
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Mot de passe:</p>
                    <p class="text-gray-700 dark:text-gray-300 font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                        {{ session('account_created.password') }}
                    </p>
                </div>
            </div>
            
            @if(session('account_created.email_sent'))
                <p class="text-green-600 mt-3">📧 Email envoyé avec succès</p>
            @else
                <p class="text-yellow-600 mt-3">📧 Email non envoyé</p>
            @endif
        </div>
    </div>
    @endif
</div>

<script>
function showTab(tab) {
    // Masquer tous les contenus
    document.getElementById('create-content').classList.add('hidden');
    document.getElementById('list-content').classList.add('hidden');
    
    // Réinitialiser tous les onglets
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('border-blue-500', 'text-blue-600', 'dark:text-blue-400');
        btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
    });
    
    // Afficher l'onglet sélectionné
    if (tab === 'create') {
        document.getElementById('create-content').classList.remove('hidden');
        document.getElementById('create-tab').classList.add('border-blue-500', 'text-blue-600', 'dark:text-blue-400');
        document.getElementById('create-tab').classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
    } else {
        document.getElementById('list-content').classList.remove('hidden');
        document.getElementById('list-tab').classList.add('border-blue-500', 'text-blue-600', 'dark:text-blue-400');
        document.getElementById('list-tab').classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
    }
}

function selectEleve(id, nom, prenom, classeId) {
    // Basculer vers l'onglet de création
    showTab('create');
    
    // Remplir le formulaire élève
    const form = document.querySelector('form input[name="type"][value="eleve"]').closest('form');
    form.querySelector('input[name="nom"]').value = nom;
    form.querySelector('input[name="prenom"]').value = prenom;
    form.querySelector('select[name="classe_id"]').value = classeId;
    
    // Ajouter un champ caché pour l'ID de l'élève
    let existingIdField = form.querySelector('input[name="eleve_id"]');
    if (!existingIdField) {
        existingIdField = document.createElement('input');
        existingIdField.type = 'hidden';
        existingIdField.name = 'eleve_id';
        form.appendChild(existingIdField);
    }
    existingIdField.value = id;
    
    // Effacer le mot de passe pour en générer un nouveau
    form.querySelector('input[name="password"]').value = '';
    
    // Scroller vers le formulaire
    form.scrollIntoView({ behavior: 'smooth' });
    
    // Mettre en évidence le formulaire
    form.parentElement.classList.add('ring-2', 'ring-blue-500');
    setTimeout(() => {
        form.parentElement.classList.remove('ring-2', 'ring-blue-500');
    }, 2000);
}

function selectParent(id, nom, prenom, email, telephone, profession) {
    // Basculer vers l'onglet de création
    showTab('create');
    
    // Remplir le formulaire parent
    const form = document.querySelector('form input[name="type"][value="parent"]').closest('form');
    form.querySelector('input[name="nom"]').value = nom;
    form.querySelector('input[name="prenom"]').value = prenom;
    form.querySelector('input[name="email"]').value = email;
    form.querySelector('input[name="telephone"]').value = telephone;
    form.querySelector('input[name="profession"]').value = profession;
    
    // Effacer le mot de passe pour en générer un nouveau
    form.querySelector('input[name="password"]').value = '';
    
    // Scroller vers le formulaire
    form.scrollIntoView({ behavior: 'smooth' });
    
    // Mettre en évidence le formulaire
    form.parentElement.classList.add('ring-2', 'ring-green-500');
    setTimeout(() => {
        form.parentElement.classList.remove('ring-2', 'ring-green-500');
    }, 2000);
}
</script>
@endsection
