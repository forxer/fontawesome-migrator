@extends('fontawesome-migrator::layout')

@section('title', 'Rapport de Migration Font Awesome 5 → 6')

@section('body-background', '#f5f5f5')

@section('head-extra')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ route('fontawesome-migrator.assets.css', 'migration-reports.css') }}">
@endsection

@section('content')
    <div class="header">
        <h1>📊 Rapport de Migration Font Awesome 5 → 6</h1>
        <p>Généré le {{ $timestamp }}</p>
    </div>

    <!-- Statistiques générales améliorées -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['total_files'], 0, ',', ' ') }}</div>
            <div class="stat-label">Fichiers analysés</div>
            @if($stats['total_files'] > 0)
                <div style="margin-top: 10px; color: var(--blue-500); font-size: 0.9em;">
                    🔍 Scan terminé
                </div>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['modified_files'], 0, ',', ' ') }}</div>
            <div class="stat-label">Fichiers modifiés</div>
            @if($stats['modified_files'] > 0)
                <div class="metric-improvement">
                    <span>🎯</span>
                    <span>{{ number_format($stats['modified_files'], 0, ',', ' ') }} fichier(s) optimisé(s)</span>
                </div>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['total_changes'], 0, ',', ' ') }}</div>
            <div class="stat-label">Total des changements</div>
            @if($stats['total_changes'] > 0)
                <div style="margin-top: 10px; font-size: 0.9em; color: var(--success-color);">
                    ⚡ Prêt pour Font Awesome 6
                </div>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['icons_migrated'] ?? 0, 0, ',', ' ') }}</div>
            <div class="stat-label">Icônes migrées</div>
            @if(($stats['icons_migrated'] ?? 0) > 0)
                <div style="margin-top: 10px; color: var(--primary-color); font-size: 0.9em;">
                    🎨 FA5 → FA6
                </div>
            @endif
        </div>

        @if(($stats['assets_migrated'] ?? 0) > 0)
        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['assets_migrated'], 0, ',', ' ') }}</div>
            <div class="stat-label">Assets migrés</div>
            <div style="margin-top: 10px; color: var(--secondary-color); font-size: 0.9em;">
                📦 CDN + NPM
            </div>
        </div>
        @endif
        
        @if(!empty($stats['warnings']) && $stats['warnings'] > 0)
        <div class="stat-card" style="border-left: 4px solid var(--warning-color);">
            <div class="stat-number" style="color: var(--warning-color);">{{ number_format($stats['warnings'], 0, ',', ' ') }}</div>
            <div class="stat-label">Avertissements</div>
            <div style="margin-top: 10px; color: var(--warning-color); font-size: 0.9em;">
                ⚠️ À vérifier
            </div>
        </div>
        @endif
    </div>
    
    @if($stats['total_changes'] > 0)
    <!-- Graphique des types de changements -->
    <div class="section enhanced-section">
        <h2>📊 Répartition des changements</h2>
        <div class="chart-container">
            <canvas id="changesChart"></canvas>
        </div>
    </div>
    
    <!-- Chronologie de migration -->
    <div class="section enhanced-section">
        <h2>🕒 Chronologie de migration</h2>
        <div class="timeline-container">
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4>🔍 Analyse effectuée</h4>
                    <p>{{ number_format($stats['total_files'], 0, ',', ' ') }} fichier(s) analysé(s) pour détecter Font Awesome 5</p>
                    <small>{{ $timestamp }}</small>
                </div>
            </div>
            
            @if($stats['modified_files'] > 0)
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4>🎯 Fichiers ciblés</h4>
                    <p>{{ number_format($stats['modified_files'], 0, ',', ' ') }} fichier(s) contenant du code Font Awesome 5</p>
                    <small>Détection automatique</small>
                </div>
            </div>
            
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4>⚡ Migration appliquée</h4>
                    <p>{{ number_format($stats['total_changes'], 0, ',', ' ') }} changement(s) {{ $isDryRun ? 'identifiés' : 'appliqués' }}</p>
                    <small>{{ $isDryRun ? 'Mode prévisualisation' : 'Modifications effectives' }}</small>
                </div>
            </div>
            @endif
            
            @if(($stats['assets_migrated'] ?? 0) > 0)
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4>📦 Assets migrés</h4>
                    <p>{{ number_format($stats['assets_migrated'], 0, ',', ' ') }} asset(s) CDN/NPM {{ $isDryRun ? 'détectés' : 'mis à jour' }}</p>
                    <small>Packages et liens modernisés</small>
                </div>
            </div>
            @endif
            
            <div class="timeline-item">
                <div class="timeline-content">
                    @if($stats['migration_success'] ?? true)
                        <h4>✅ Migration {{ $isDryRun ? 'planifiée' : 'terminée' }}</h4>
                        <p>Votre code est {{ $isDryRun ? 'prêt pour' : 'maintenant compatible avec' }} Font Awesome 6</p>
                    @else
                        <h4>⚠️ Migration partielle</h4>
                        <p>Certains éléments nécessitent une vérification manuelle</p>
                    @endif
                    <small>{{ $timestamp }}</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recommandations intelligentes -->
    <div class="section enhanced-section">
        <h2>🎯 Recommandations</h2>
        <div class="recommendations-grid">
            @if($isDryRun && $stats['total_changes'] > 0)
                <div class="recommendation-card priority-high">
                    <div class="rec-icon">🚀</div>
                    <div class="rec-content">
                        <h4>Prêt pour la migration</h4>
                        <p>Exécutez <code>php artisan fontawesome:migrate</code> pour appliquer ces {{ number_format($stats['total_changes'], 0, ',', ' ') }} changements.</p>
                        <button class="btn btn-primary btn-sm" onclick="copyCommand('php artisan fontawesome:migrate')">📋 Copier la commande</button>
                    </div>
                </div>
            @endif
            
            @if(!$isDryRun && $stats['total_changes'] > 0)
                <div class="recommendation-card priority-medium">
                    <div class="rec-icon">🧪</div>
                    <div class="rec-content">
                        <h4>Tests recommandés</h4>
                        <p>Testez votre application pour vérifier que les icônes s'affichent correctement.</p>
                        <button class="btn btn-primary btn-sm" onclick="showTestingTips()">📝 Conseils de test</button>
                    </div>
                </div>
            @endif
            
            @if(($stats['warnings'] ?? 0) > 0)
                <div class="recommendation-card priority-high">
                    <div class="rec-icon">⚠️</div>
                    <div class="rec-content">
                        <h4>Vérifications nécessaires</h4>
                        <p>{{ number_format($stats['warnings'], 0, ',', ' ') }} avertissement(s) détecté(s). Vérifiez manuellement ces éléments.</p>
                        <button class="btn btn-warning btn-sm" onclick="scrollToWarnings()">👀 Voir les avertissements</button>
                    </div>
                </div>
            @endif
            
            @if(($stats['assets_migrated'] ?? 0) > 0)
                <div class="recommendation-card priority-medium">
                    <div class="rec-icon">📦</div>
                    <div class="rec-content">
                        <h4>Mise à jour des dépendances</h4>
                        <p>N'oubliez pas d'exécuter <code>npm install</code> pour installer les nouvelles versions.</p>
                        <button class="btn btn-primary btn-sm" onclick="copyCommand('npm install')">📋 Copier npm install</button>
                    </div>
                </div>
            @endif
            
            @php
                $migrationScore = 0;
                if ($stats['total_files'] > 0) {
                    $migrationScore = round(($stats['modified_files'] / $stats['total_files']) * 100);
                }
            @endphp
            
            @if($migrationScore >= 80)
                <div class="recommendation-card priority-success">
                    <div class="rec-icon">🏆</div>
                    <div class="rec-content">
                        <h4>Excellent score de migration</h4>
                        <p>{{ number_format($migrationScore, 1, ',', ' ') }} % de votre code a été optimisé pour Font Awesome 6 !</p>
                    </div>
                </div>
            @elseif($migrationScore >= 50)
                <div class="recommendation-card priority-medium">
                    <div class="rec-icon">👍</div>
                    <div class="rec-content">
                        <h4>Bonne migration</h4>
                        <p>{{ number_format($migrationScore, 1, ',', ' ') }} % de votre code utilise maintenant Font Awesome 6.</p>
                    </div>
                </div>
            @elseif($stats['total_changes'] == 0)
                <div class="recommendation-card priority-success">
                    <div class="rec-icon">✨</div>
                    <div class="rec-content">
                        <h4>Code déjà optimisé</h4>
                        <p>Votre code semble déjà compatible avec Font Awesome 6 !</p>
                    </div>
                </div>
            @endif
            
            <div class="recommendation-card priority-info">
                <div class="rec-icon">📚</div>
                <div class="rec-content">
                    <h4>Documentation officielle</h4>
                    <p>Consultez le guide de migration Font Awesome 6 pour plus d'informations.</p>
                    <a href="https://fontawesome.com/v6/docs/web/setup/upgrade/" target="_blank" class="btn btn-primary btn-sm">🔗 Guide officiel</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Configuration et options -->
    <div class="section">
        <h2>⚙️ Configuration de migration</h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <h3 style="margin: 0 0 10px 0; color: var(--gray-700);">Options utilisées</h3>
                <table style="margin-top: 0;">
                    <tr><td><strong>Mode</strong></td><td>{{ $isDryRun ? 'Dry-run (prévisualisation)' : 'Migration complète' }}</td></tr>
                    @if(!empty($migrationOptions['custom_path']))
                        <tr><td><strong>Chemin personnalisé</strong></td><td><code>{{ $migrationOptions['custom_path'] }}</code></td></tr>
                    @endif
                    @if($migrationOptions['icons_only'] ?? false)
                        <tr><td><strong>Migration</strong></td><td>Icônes uniquement</td></tr>
                    @elseif($migrationOptions['assets_only'] ?? false)
                        <tr><td><strong>Migration</strong></td><td>Assets uniquement</td></tr>
                    @else
                        <tr><td><strong>Migration</strong></td><td>Complète (icônes + assets)</td></tr>
                    @endif
                    <tr><td><strong>Sauvegarde</strong></td><td>
                        @if($migrationOptions['no_backup'] ?? false)
                            Désactivée
                        @elseif($migrationOptions['backup'] ?? false)
                            Forcée
                        @else
                            {{ ($configuration['backup_enabled'] ?? true) ? 'Activée' : 'Désactivée' }}
                        @endif
                    </td></tr>
                </table>
            </div>

            <div>
                <h3 style="margin: 0 0 10px 0; color: var(--gray-700);">Configuration</h3>
                <table style="margin-top: 0;">
                    <tr><td><strong>Type de licence</strong></td><td>{{ ucfirst($configuration['license_type'] ?? 'free') }}</td></tr>
                    <tr><td><strong>Chemins scannés</strong></td><td>
                        @if(!empty($configuration['scan_paths']))
                            @foreach($configuration['scan_paths'] as $path)
                                <code>{{ $path }}</code>@if(!$loop->last), @endif
                            @endforeach
                        @else
                            Non définis
                        @endif
                    </td></tr>
                    <tr><td><strong>Extensions</strong></td><td>
                        @if(!empty($configuration['file_extensions']))
                            @foreach($configuration['file_extensions'] as $ext)
                                <code>{{ $ext }}</code>@if(!$loop->last), @endif
                            @endforeach
                        @else
                            Toutes
                        @endif
                    </td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Informations de fin -->
    <div class="section">
        <h2>ℹ️ Informations supplémentaires</h2>
        <p><strong>Rapport généré :</strong> {{ $timestamp }}</p>
        <p><strong>Package :</strong> FontAwesome Migrator version {{ $packageVersion }}</p>

        @if($stats['total_changes'] > 0 && !$isDryRun)
            <div class="alert alert-info">
                💡 <strong>Conseil :</strong> Testez votre application pour vous assurer que tous les changements fonctionnent correctement.
            </div>
        @endif

        @if($isDryRun && $stats['total_changes'] > 0)
            <div class="alert alert-warning">
                🚀 <strong>Prêt pour la migration :</strong> Exécutez <code>php artisan fontawesome:migrate</code> pour appliquer ces changements.
            </div>
        @endif
    </div>

    @if($stats['total_changes'] > 0)
        <!-- Résumé de migration -->
        <div class="section">
            <h2>📋 Résumé de la migration</h2>

            @if($stats['migration_success'])
                <div class="alert alert-success">
                    ✅ Migration terminée avec succès ! {{ number_format($stats['total_changes'], 0, ',', ' ') }} changement(s) appliqué(s) sur {{ number_format($stats['modified_files'], 0, ',', ' ') }} fichier(s).
                </div>
            @else
                <div class="alert alert-warning">
                    ⚠️ Migration partielle. Certains éléments n'ont pas pu être migrés automatiquement.
                </div>
            @endif

            @if(!empty($stats['changes_by_type']))
                <table>
                    <tr><th>Type de changement</th><th>Nombre</th><th>Pourcentage</th></tr>
                    @foreach($stats['changes_by_type'] as $type => $count)
                        @php
                            $percentage = $stats['total_changes'] > 0 ? round(($count / $stats['total_changes']) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td><span class="badge">{{ ucfirst($type) }}</span></td>
                            <td>{{ number_format($count, 0, ',', ' ') }}</td>
                            <td>{{ number_format($percentage, 1, ',', ' ') }} %</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>

        <!-- Section des assets si présents -->
        @if(!empty($stats['asset_types']))
            <div class="section">
                <h2>🎨 Assets détectés</h2>
                <table>
                    <tr><th>Type d'asset</th><th>Nombre</th><th>Description</th></tr>
                    @foreach($stats['asset_types'] as $assetType => $count)
                        <tr>
                            <td><strong>{{ $assetType }}</strong></td>
                            <td>{{ number_format($count, 0, ',', ' ') }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $assetType)) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

    @endif

    <!-- Détail des fichiers modifiés avec recherche -->
    <div class="section enhanced-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>📄 Détail des modifications</h2>
            
            <div class="export-buttons">
                <button class="btn btn-primary btn-sm" onclick="copyToClipboard()">
                    📋 Copier le rapport
                </button>
                <button class="btn btn-primary btn-sm" onclick="toggleAllDetails()">
                    🔄 Développer/Réduire
                </button>
            </div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <input type="text" 
                   class="search-box" 
                   id="searchBox" 
                   placeholder="🔍 Rechercher dans les fichiers, changements ou extensions..."
                   onkeyup="filterChanges()"
                   style="display: block; width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px;">
        </div>

        <div id="modificationsContainer">
            @if($stats['total_changes'] > 0)
                @foreach($results as $index => $result)
                    @if(!empty($result['changes']))
                        <div class="file-item" data-file="{{ $result['file'] }}" data-index="{{ $index }}">
                            <div class="file-path" style="display: flex; justify-content: space-between; align-items: center;">
                                <span>📁 {{ $result['file'] }}</span>
                                <button class="toggle-btn" onclick="toggleFileDetails({{ $index }})">
                                    <span id="toggle-icon-{{ $index }}">▼</span>
                                    {{ count($result['changes']) }} changement(s)
                                </button>
                            </div>

                            <div class="collapsible-content active" id="details-{{ $index }}" style="max-height: none; overflow: visible;">
                                @foreach($result['changes'] as $changeIndex => $change)
                                    <div class="change-item" data-change-from="{{ $change['from'] }}" data-change-to="{{ $change['to'] }}">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div style="flex: 1;">
                                                <div class="change-from">- {{ $change['from'] }}</div>
                                                <div class="change-to">+ {{ $change['to'] }}</div>
                                            </div>
                                            <div style="text-align: right; color: var(--gray-500); font-size: 0.8em;">
                                                @if(isset($change['line']))
                                                    📍 Ligne {{ $change['line'] }}<br>
                                                @endif
                                                @if(isset($change['type']))
                                                    <span class="badge badge-{{ $change['type'] }}">{{ ucfirst($change['type']) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if(!empty($result['warnings']))
                                    @foreach($result['warnings'] as $warning)
                                        <div class="alert alert-warning">⚠️ {{ $warning }}</div>
                                    @endforeach
                                @endif
                                
                                @if(!empty($result['assets']))
                                    <div class="timeline-item">
                                        <strong>Assets détectés :</strong>
                                        @foreach($result['assets'] as $asset)
                                            <div style="margin: 5px 0; font-family: monospace; font-size: 0.9em;">
                                                🎨 {{ $asset['type'] ?? 'unknown' }}: <code>{{ $asset['original'] ?? '' }}</code>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <!-- Aucun changement -->
                <div class="alert alert-info">
                    ℹ️ Aucun changement nécessaire. Votre code semble déjà compatible avec Font Awesome 6.
                </div>
            @endif
        </div>
        
        <div id="noResults" style="display: none; text-align: center; padding: 40px; color: var(--gray-500);">
            <div style="font-size: 3em;">🔍</div>
            <p>Aucun résultat trouvé pour votre recherche</p>
        </div>
    </div>

    {{-- Script externe pour les rapports --}}
    <script src="{{ route('fontawesome-migrator.assets.js', 'migration-reports.js') }}"></script>
    
    {{-- Données pour les scripts externes --}}
    <script>
        // Données de migration pour les scripts externes
        window.migrationData = {
            timestamp: '{{ $timestamp }}',
            packageVersion: '{{ $packageVersion }}',
            stats: @json($stats),
            isDryRun: {{ $isDryRun ? 'true' : 'false' }},
            files: @json($results)
        };
        
        // Données pour le graphique
        const chartData = {
            labels: [
                @if(!empty($stats['changes_by_type']))
                    @foreach($stats['changes_by_type'] as $type => $count)
                        '{{ ucfirst(str_replace("_", " ", $type)) }}',
                    @endforeach
                @endif
            ],
            data: [
                @if(!empty($stats['changes_by_type']))
                    @foreach($stats['changes_by_type'] as $type => $count)
                        {{ $count }},
                    @endforeach
                @endif
            ],
            colors: [
                '#4299e1', '#667eea', '#059669', '#f59e0b', '#f56565', '#8b5cf6', '#06b6d4'
            ]
        };
        
        // Initialiser le graphique si des changements existent
        @if($stats['total_changes'] > 0 && !empty($stats['changes_by_type']))
            initializeChart(chartData, true);
        @endif
    </script>

@endsection