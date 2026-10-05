<div class="card overflow-x-auto">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                @if($avecEcole)<th>École</th>@endif
                <th>Utilisateur</th>
                <th>Action</th>
                <th>Élément</th>
                <th>Détails</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entrees as $entree)
                <tr>
                    <td class="whitespace-nowrap text-slate-500">{{ $entree->created_at->format('d/m/Y H:i') }}</td>
                    @if($avecEcole)<td>{{ $entree->ecole?->nom ?? '—' }}</td>@endif
                    <td>
                        {{ $entree->user_nom ?? 'Inconnu' }}
                        @if($entree->user_role)<span class="block text-xs text-slate-400">{{ $entree->user_role }}</span>@endif
                    </td>
                    <td>
                        <span class="badge-{{ match($entree->action) { 'suppression', 'connexion_echouee' => 'red', 'creation' => 'green', 'modification' => 'yellow', default => 'slate' } }}">{{ $entree->libelleAction() }}</span>
                    </td>
                    <td>
                        @if($entree->objet_type)
                            {{ $entree->objet_libelle ?? '—' }}
                            <span class="block text-xs text-slate-400">{{ $entree->objet_type }} #{{ $entree->objet_id }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-xs text-slate-600">
                        @if($entree->action === 'modification' && is_array($entree->changements))
                            @foreach($entree->changements as $champ => $valeurs)
                                <div><span class="font-medium">{{ $champ }}</span> : {{ is_array($valeurs) ? json_encode($valeurs['avant'] ?? null, JSON_UNESCAPED_UNICODE) : '' }} → {{ is_array($valeurs) ? json_encode($valeurs['apres'] ?? null, JSON_UNESCAPED_UNICODE) : json_encode($valeurs, JSON_UNESCAPED_UNICODE) }}</div>
                            @endforeach
                        @elseif($entree->changements)
                            <details>
                                <summary class="cursor-pointer text-slate-500">Voir</summary>
                                <pre class="whitespace-pre-wrap break-all">{{ json_encode($entree->changements, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                            </details>
                        @endif
                        @if($entree->ip)<span class="block text-slate-400">IP {{ $entree->ip }}</span>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ $avecEcole ? 6 : 5 }}" class="text-center text-slate-400 py-6">Aucune entrée dans le journal.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
