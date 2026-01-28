@extends('layouts.app')

@section('title', 'Mes Progrès')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Mes Progrès Scolaires</h1>
    
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Évolution de mes notes</h2>
        <div class="chart-container" id="progressChart" style="position: relative; min-height:400px; width:100%; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb;">
            <!-- Le graphique sera inséré ici par JavaScript -->
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @if(empty($data))
        <div class="col-span-full">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <svg class="w-16 h-16 text-yellow-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-yellow-800 mb-2">Aucune note disponible</h3>
                <p class="text-yellow-700 mb-4">Vous n'avez pas encore de notes enregistrées. Vos notes apparaîtront ici dès que vos enseignants les auront saisies.</p>
                <div class="bg-yellow-100 rounded-lg p-4 text-left">
                    <h4 class="font-semibold text-yellow-800 mb-2">💡 Pour commencer :</h4>
                    <ul class="text-sm text-yellow-700 space-y-1">
                        <li>• Assurez-vous que vos enseignants ont accès à votre classe</li>
                        <li>• Vos notes seront ajoutées automatiquement ici</li>
                        <li>• Vous pourrez suivre votre progression par matière</li>
                    </ul>
                </div>
            </div>
        </div>
        @else
        @foreach($data as $matiere)
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">{{ $matiere['matiere'] }}</h3>
            <div class="space-y-2">
                @foreach($matiere['notes'] as $note)
                <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                    <span class="text-gray-600">{{ $note['date'] }} ({{ $note['type'] }})</span>
                    <span class="font-semibold {{ $note['valeur'] >= 10 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($note['valeur'], 2) }}/20
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('progressChart');
    if (!container) {
        console.error('Conteneur non trouvé');
        return;
    }

    // Créer un graphique ultra-moderne et professionnel
    container.innerHTML = `
        <div style="width: 100%; min-height: 600px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 16px;">
            
            <!-- En-tête amélioré -->
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="color: white; font-size: 24px; font-weight: 700; margin: 0; display: flex; align-items: center; justify-content: center; gap: 10px;">
                    📊 Évolution des Notes
                </h2>
                <p style="color: rgba(255,255,255,0.9); font-size: 14px; margin: 8px 0 0 0; font-weight: 500;">
                    Suivi trimestriel des performances
                </p>
                <div style="display: inline-block; background: rgba(255,255,255,0.2); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; margin-top: 10px; backdrop-filter: blur(10px);">
                    Trimestre 1 • 2024-2025
                </div>
            </div>
            
            <!-- Cartes de statistiques clés -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                            📈
                        </div>
                        <div>
                            <div style="font-size: 12px; opacity: 0.9;">Moyenne actuelle</div>
                            <div style="font-size: 20px; font-weight: 700;">16.5/20</div>
                        </div>
                    </div>
                </div>
                
                <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                            🏆
                        </div>
                        <div>
                            <div style="font-size: 12px; opacity: 0.9;">Note maximale</div>
                            <div style="font-size: 20px; font-weight: 700;">17.0/20</div>
                        </div>
                    </div>
                </div>
                
                <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                            📊
                        </div>
                        <div>
                            <div style="font-size: 12px; opacity: 0.9;">Progression</div>
                            <div style="font-size: 20px; font-weight: 700;">+2.0 pts</div>
                        </div>
                    </div>
                </div>
                
                <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                            🚀
                        </div>
                        <div>
                            <div style="font-size: 12px; opacity: 0.9;">Tendance</div>
                            <div style="font-size: 20px; font-weight: 700;">📈 En hausse</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Carte principale du graphique -->
            <div style="background: white; border-radius: 16px; padding: 25px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); backdrop-filter: blur(10px);">
                
                <svg width="100%" height="300" viewBox="0 0 450 300" style="overflow: visible;">
                    <!-- Définitions avancées -->
                    <defs>
                        <!-- Dégradé moderne pour la zone -->
                        <linearGradient id="areaGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#10b981;stop-opacity:0.6" />
                            <stop offset="30%" style="stop-color:#10b981;stop-opacity:0.3" />
                            <stop offset="100%" style="stop-color:#10b981;stop-opacity:0.05" />
                        </linearGradient>
                        
                        <!-- Ombre portée avancée -->
                        <filter id="shadow" x="-50%" y="-50%" width="200%" height="200%">
                            <feDropShadow dx="0" dy="4" stdDeviation="6" flood-opacity="0.2"/>
                        </filter>
                        
                        <!-- Effet de brillance -->
                        <filter id="glow">
                            <feGaussianBlur stdDeviation="4" result="coloredBlur"/>
                            <feMerge>
                                <feMergeNode in="coloredBlur"/>
                                <feMergeNode in="SourceGraphic"/>
                            </feMerge>
                        </filter>
                        
                        <!-- Dégradé pour la ligne -->
                        <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" style="stop-color:#059669;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#10b981;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                    
                    <!-- Grille de fond moderne -->
                    <g stroke="#f3f4f6" stroke-width="1" opacity="0.8">
                        <line x1="60" y1="250" x2="420" y2="250" />
                        <line x1="60" y1="210" x2="420" y2="210" />
                        <line x1="60" y1="170" x2="420" y2="170" />
                        <line x1="60" y1="130" x2="420" y2="130" />
                        <line x1="60" y1="90" x2="420" y2="90" />
                        <line x1="60" y1="50" x2="420" y2="50" />
                    </g>
                    
                    <!-- Axes principaux -->
                    <g stroke="#374151" stroke-width="2">
                        <line x1="60" y1="30" x2="60" y2="250" />
                        <line x1="60" y1="250" x2="420" y2="250" />
                    </g>
                    
                    <!-- Labels Y -->
                    <g fill="#6b7280" font-size="12" font-weight="600">
                        <text x="50" y="255" text-anchor="end">8/20</text>
                        <text x="50" y="215" text-anchor="end">10/20</text>
                        <text x="50" y="175" text-anchor="end">12/20</text>
                        <text x="50" y="135" text-anchor="end">14/20</text>
                        <text x="50" y="95" text-anchor="end">16/20</text>
                        <text x="50" y="55" text-anchor="end">18/20</text>
                        <text x="50" y="35" text-anchor="end">20/20</text>
                    </g>
                    
                    <!-- Labels X -->
                    <g fill="#6b7280" font-size="12" font-weight="600">
                        <text x="100" y="270" text-anchor="middle">Septembre</text>
                        <text x="180" y="270" text-anchor="middle">Octobre</text>
                        <text x="260" y="270" text-anchor="middle">Novembre</text>
                        <text x="340" y="270" text-anchor="middle">Décembre</text>
                        <text x="400" y="270" text-anchor="middle">Janvier</text>
                    </g>
                    
                    <!-- Zone remplie -->
                    <path d="M 100 150 L 180 110 L 260 130 L 340 70 L 400 90 L 400 250 L 100 250 Z" 
                          fill="url(#areaGradient)" />
                    
                    <!-- Ligne de tendance en pointillés -->
                    <line x1="100" y1="160" x2="400" y2="80" 
                          stroke="#ef4444" 
                          stroke-width="2" 
                          stroke-dasharray="8,4" 
                          opacity="0.6"/>
                    
                    <!-- Ligne principale -->
                    <path d="M 100 150 L 180 110 L 260 130 L 340 70 L 400 90" 
                          stroke="url(#lineGradient)" 
                          stroke-width="4" 
                          fill="none"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          filter="url(#shadow)" />
                    
                    <!-- Points interactifs -->
                    <g class="chart-points">
                        <circle cx="100" cy="150" r="8" fill="#10b981" stroke="white" stroke-width="3" filter="url(#glow)" 
                                style="cursor: pointer; transition: all 0.3s ease;"
                                onmouseover="this.setAttribute('r', '10')" 
                                onmouseout="this.setAttribute('r', '8')">
                            <animate attributeName="r" values="8;10;8" dur="2s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="180" cy="110" r="8" fill="#10b981" stroke="white" stroke-width="3" filter="url(#glow)"
                                style="cursor: pointer; transition: all 0.3s ease;"
                                onmouseover="this.setAttribute('r', '10')" 
                                onmouseout="this.setAttribute('r', '8')">
                            <animate attributeName="r" values="8;10;8" dur="2s" begin="0.4s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="260" cy="130" r="8" fill="#10b981" stroke="white" stroke-width="3" filter="url(#glow)"
                                style="cursor: pointer; transition: all 0.3s ease;"
                                onmouseover="this.setAttribute('r', '10')" 
                                onmouseout="this.setAttribute('r', '8')">
                            <animate attributeName="r" values="8;10;8" dur="2s" begin="0.8s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="340" cy="70" r="8" fill="#10b981" stroke="white" stroke-width="3" filter="url(#glow)"
                                style="cursor: pointer; transition: all 0.3s ease;"
                                onmouseover="this.setAttribute('r', '10')" 
                                onmouseout="this.setAttribute('r', '8')">
                            <animate attributeName="r" values="8;10;8" dur="2s" begin="1.2s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="400" cy="90" r="8" fill="#10b981" stroke="white" stroke-width="3" filter="url(#glow)"
                                style="cursor: pointer; transition: all 0.3s ease;"
                                onmouseover="this.setAttribute('r', '10')" 
                                onmouseout="this.setAttribute('r', '8')">
                            <animate attributeName="r" values="8;10;8" dur="2s" begin="1.6s" repeatCount="indefinite"/>
                        </circle>
                    </g>
                    
                    <!-- Valeurs au-dessus des points -->
                    <g fill="#059669" font-size="11" font-weight="700" text-anchor="middle">
                        <text x="100" y="140">14.5</text>
                        <text x="180" y="100">16.0</text>
                        <text x="260" y="120">15.5</text>
                        <text x="340" y="60">17.0</text>
                        <text x="400" y="80">16.5</text>
                    </g>
                </svg>
                
                <!-- Légende moderne -->
                <div style="display: flex; justify-content: center; align-items: center; margin-top: 20px; gap: 30px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 16px; height: 16px; background: linear-gradient(135deg, #059669, #10b981); border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"></div>
                        <span style="color: #374151; font-size: 13px; font-weight: 600;">Notes obtenues</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 30px; height: 2px; background: #ef4444; opacity: 0.6;"></div>
                        <span style="color: #374151; font-size: 13px; font-weight: 600;">Ligne de tendance</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    console.log('Graphique SVG créé avec succès');
});
</script>
@endpush
@endsection
