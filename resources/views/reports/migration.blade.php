@extends('fontawesome-migrator::layout')

@section('title', 'Rapport de Migration Font Awesome 5 → 6')

@section('body-background', '#f5f5f5')

@section('head-extra')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Variables CSS */
        :root {
            --primary-color: #4299e1;
            --primary-hover: #3182ce;
            --secondary-color: #667eea;
            --success-color: #48bb78;
            --error-color: #e53e3e;
            --warning-color: #ed8936;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-700: #374151;
            --blue-500: #3b82f6;
        }

        /* Sections améliorées */
        .enhanced-section {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 20px;
        }

        .enhanced-section h2 {
            color: var(--gray-700);
            margin-bottom: 20px;
        }

        /* Boîte de recherche */
        .search-box {
            padding: 12px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            width: 100%;
            font-size: 16px;
            transition: border-color 0.2s;
        }

        .search-box:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        /* Statistiques */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary-color);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .stat-label {
            color: var(--gray-700);
            font-weight: 500;
        }

        .metric-improvement {
            margin-top: 10px;
            font-size: 0.9em;
            color: var(--success-color);
        }

        /* Boutons */
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9em;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-warning {
            background: var(--warning-color);
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8em;
        }

        .export-buttons {
            display: flex;
            gap: 10px;
        }

        /* Graphiques */
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
            border-radius: 8px;
            padding: 20px;
        }

        /* Timeline */
        .timeline-container {
            position: relative;
            padding-left: 30px;
        }

        .timeline-container:before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--gray-300);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .timeline-item:before {
            content: '';
            position: absolute;
            left: -37px;
            top: 20px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-color);
        }

        /* Recommandations */
        .recommendations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .recommendation-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            gap: 15px;
        }

        .priority-high {
            border-left: 4px solid var(--error-color);
        }

        .priority-medium {
            border-left: 4px solid var(--warning-color);
        }

        .priority-success {
            border-left: 4px solid var(--success-color);
        }

        .priority-info {
            border-left: 4px solid var(--blue-500);
        }

        .rec-icon {
            font-size: 2rem;
        }

        .rec-content h4 {
            margin: 0 0 10px 0;
            color: var(--gray-700);
        }

        /* Métriques de performance */
        .performance-metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .metric-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .metric-label {
            color: var(--gray-700);
            font-weight: 500;
            margin-bottom: 10px;
        }

        .metric-trend {
            font-size: 0.9em;
            font-weight: 500;
        }

        .trend-up {
            color: var(--success-color);
        }

        .trend-down {
            color: var(--error-color);
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        th {
            background: var(--gray-50);
            font-weight: 600;
            color: var(--gray-700);
        }

        .badge {
            background: var(--primary-color);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8em;
        }

        .badge-style {
            background: var(--secondary-color);
        }

        .badge-icon {
            background: var(--success-color);
        }

        .badge-asset {
            background: var(--warning-color);
        }

        /* Alertes */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
        }

        .alert-warning {
            background: #fefce8;
            border: 1px solid #fde047;
            color: #a16207;
        }

        .alert-info {
            background: #eff6ff;
            border: 1px solid #93c5fd;
            color: #1e40af;
        }

        /* Contenu pliable */
        .collapsible-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .collapsible-content.active {
            max-height: 2000px !important;
            overflow: visible !important;
            transition: max-height 0.3s ease-in;
        }

        /* Fichiers et changements */
        .file-item {
            background: white;
            border: 1px solid var(--gray-200);
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
        }

        .file-path {
            background: var(--gray-50);
            padding: 15px;
            border-bottom: 1px solid var(--gray-200);
        }

        .toggle-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.2s;
        }

        .toggle-btn:hover {
            background: var(--primary-hover);
        }

        .change-item {
            padding: 15px;
            border-bottom: 1px solid var(--gray-100);
            background: white;
        }

        .change-item:last-child {
            border-bottom: none;
        }

        .change-from {
            color: var(--error-color);
            font-family: monospace;
            background: #fef2f2;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 5px;
        }

        .change-to {
            color: var(--success-color);
            font-family: monospace;
            background: #f0fdf4;
            padding: 8px;
            border-radius: 4px;
        }

        /* Surlignage de recherche */
        .highlight-match {
            background: #fef3c7;
            color: #92400e;
            padding: 2px 4px;
            border-radius: 3px;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        /* Section générale */
        .section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .section h2 {
            margin-top: 0;
            color: var(--gray-700);
            border-bottom: 2px solid var(--gray-200);
            padding-bottom: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .header h1 {
            margin: 0;
            color: var(--gray-700);
        }

        /* Tips list */
        .tips-list {
            list-style: none;
            padding: 0;
        }

        .tips-list li {
            margin-bottom: 15px;
            padding: 10px;
            background: var(--gray-50);
            border-radius: 6px;
            border-left: 4px solid var(--primary-color);
        }
    </style>
@endsection

@section('content')
    <div class="header">
        <div style="margin-bottom: 15px;">
            <a href="{{ route('fontawesome-migrator.reports.index') }}" class="btn btn-primary">
                ← Retour aux rapports
            </a>
        </div>
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
                ⚠️ Icônes renommées/dépréciées
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
                        <h4>Icônes à vérifier</h4>
                        <p>{{ number_format($stats['warnings'], 0, ',', ' ') }} icône(s) renommée(s), dépréciée(s) ou Pro détectée(s). Vérifiez le rendu.</p>
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
                                    <span id="toggle-icon-{{ $index }}">▶</span>
                                    {{ count($result['changes']) }} changement(s)
                                </button>
                            </div>

                            <div class="collapsible-content" id="details-{{ $index }}">
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

    {{-- JavaScript complet pour les rapports --}}
    <script>
        // Formatage français des nombres
        function formatNumber(number, decimals = 0) {
            return new Intl.NumberFormat('fr-FR', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(number);
        }

        function formatPercentage(number, decimals = 1) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'percent',
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(number / 100);
        }

        // Variables globales
        let allExpanded = false;

        // Animation des cartes statistiques
        function animateStatCards() {
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(el => {
                const originalText = el.textContent;
                const finalValue = parseInt(originalText.replace(/\s/g, ''));
                if (finalValue > 0) {
                    let currentValue = 0;
                    const increment = Math.ceil(finalValue / 30);
                    const timer = setInterval(() => {
                        currentValue += increment;
                        if (currentValue >= finalValue) {
                            el.textContent = formatNumber(finalValue);
                            clearInterval(timer);
                        } else {
                            el.textContent = formatNumber(currentValue);
                        }
                    }, 50);
                }
            });
        }

        // Fonction de recherche et filtrage
        function filterChanges() {
            const searchTerm = document.getElementById('searchBox').value.toLowerCase();
            const container = document.getElementById('modificationsContainer');
            const fileItems = container.querySelectorAll('.file-item');
            const noResults = document.getElementById('noResults');
            let visibleCount = 0;

            fileItems.forEach(item => {
                const fileName = item.dataset.file.toLowerCase();
                const changeItems = item.querySelectorAll('.change-item');
                let hasVisibleChanges = false;

                const fileMatches = fileName.includes(searchTerm);
                
                changeItems.forEach(changeItem => {
                    const changeFrom = changeItem.dataset.changeFrom.toLowerCase();
                    const changeTo = changeItem.dataset.changeTo.toLowerCase();
                    const matches = changeFrom.includes(searchTerm) || changeTo.includes(searchTerm);
                    
                    if (matches || fileMatches || searchTerm === '') {
                        changeItem.style.display = 'block';
                        hasVisibleChanges = true;
                        
                        if (searchTerm !== '') {
                            highlightMatches(changeItem, searchTerm);
                        } else {
                            removeHighlights(changeItem);
                        }
                    } else {
                        changeItem.style.display = 'none';
                    }
                });

                if (hasVisibleChanges || fileMatches || searchTerm === '') {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        // Surlignage des correspondances de recherche
        function highlightMatches(element, searchTerm) {
            const fromEl = element.querySelector('.change-from');
            const toEl = element.querySelector('.change-to');
            
            [fromEl, toEl].forEach(el => {
                if (el) {
                    const originalText = el.textContent;
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    el.innerHTML = originalText.replace(regex, '<span class="highlight-match">$1</span>');
                }
            });
        }

        function removeHighlights(element) {
            const highlighted = element.querySelectorAll('.highlight-match');
            highlighted.forEach(el => {
                el.outerHTML = el.innerHTML;
            });
        }

        // Toggle pour afficher/masquer les détails d'un fichier
        function toggleFileDetails(index) {
            const details = document.getElementById(`details-${index}`);
            const icon = document.getElementById(`toggle-icon-${index}`);
            
            if (details.classList.contains('active')) {
                details.classList.remove('active');
                icon.textContent = '▶';
            } else {
                details.classList.add('active');
                icon.textContent = '▼';
            }
        }

        // Toggle pour développer/réduire tous les détails
        function toggleAllDetails() {
            const allDetails = document.querySelectorAll('.collapsible-content');
            const allIcons = document.querySelectorAll('[id^="toggle-icon-"]');
            
            allExpanded = !allExpanded;
            
            allDetails.forEach(detail => {
                if (allExpanded) {
                    detail.classList.add('active');
                } else {
                    detail.classList.remove('active');
                }
            });
            
            allIcons.forEach(icon => {
                icon.textContent = allExpanded ? '▼' : '▶';
            });
        }

        // Copier le rapport dans le presse-papier
        function copyToClipboard() {
            if (typeof window.migrationData === 'undefined') {
                showNotification('❌ Données du rapport non disponibles', 'error');
                return;
            }
            
            const textReport = generateTextReport(window.migrationData);
            
            navigator.clipboard.writeText(textReport).then(() => {
                showNotification('📋 Rapport copié dans le presse-papier !', 'success');
            }).catch(() => {
                showNotification('❌ Erreur lors de la copie', 'error');
            });
        }

        // Génération du rapport texte
        function generateTextReport(data) {
            let report = `📊 RAPPORT DE MIGRATION FONT AWESOME 5 → 6\n`;
            report += `${'='.repeat(50)}\n\n`;
            report += `📅 Généré le: ${data.timestamp}\n`;
            report += `📦 Version: FontAwesome Migrator ${data.packageVersion}\n`;
            report += `🔄 Mode: ${data.isDryRun ? 'Dry-run (prévisualisation)' : 'Migration complète'}\n\n`;
            
            report += `📈 STATISTIQUES:\n`;
            report += `- Fichiers analysés: ${formatNumber(data.stats.total_files)}\n`;
            report += `- Fichiers modifiés: ${formatNumber(data.stats.modified_files)}\n`;
            report += `- Total changements: ${formatNumber(data.stats.total_changes)}\n`;
            report += `- Icônes migrées: ${formatNumber(data.stats.icons_migrated || 0)}\n`;
            report += `- Assets migrés: ${formatNumber(data.stats.assets_migrated || 0)}\n\n`;
            
            if (data.files.length > 0) {
                report += `📄 DÉTAIL DES MODIFICATIONS:\n`;
                data.files.forEach(file => {
                    if (file.changes && file.changes.length > 0) {
                        report += `\n📁 ${file.file}\n`;
                        file.changes.forEach(change => {
                            report += `  - ${change.from}\n`;
                            report += `  + ${change.to}\n`;
                        });
                    }
                });
            }
            
            return report;
        }

        // Affichage des notifications
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                animation: slideIn 0.3s ease;
            `;
            notification.innerHTML = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // Fonctions pour les recommandations
        function copyCommand(command) {
            navigator.clipboard.writeText(command).then(() => {
                showNotification(`📋 Commande copiée: ${command}`, 'success');
            }).catch(() => {
                showNotification('❌ Erreur lors de la copie', 'error');
            });
        }

        function showTestingTips() {
            showModal('🧪 Conseils de test', `
                <ul class="tips-list">
                    <li><strong>🔍 Vérification visuelle :</strong> Naviguez sur votre site et vérifiez que toutes les icônes s'affichent correctement.</li>
                    <li><strong>📱 Tests multi-appareils :</strong> Testez sur différentes tailles d'écrans (mobile, tablette, desktop).</li>
                    <li><strong>🌐 Compatibilité navigateurs :</strong> Vérifiez Chrome, Firefox, Safari et Edge.</li>
                    <li><strong>⚡ Performance :</strong> Utilisez les outils de développement pour vérifier les temps de chargement.</li>
                    <li><strong>🎨 Cohérence design :</strong> Assurez-vous que le style et la taille des icônes restent cohérents.</li>
                    <li><strong>🔄 Cache navigateur :</strong> Videz le cache ou testez en navigation privée.</li>
                </ul>
            `);
        }

        function scrollToWarnings() {
            const warnings = document.querySelectorAll('.alert-warning');
            if (warnings.length > 0) {
                warnings[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                warnings[0].style.animation = 'pulse 2s';
            } else {
                showNotification('ℹ️ Aucun avertissement dans cette vue', 'info');
            }
        }

        // Gestion des modales
        function showModal(title, content) {
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.style.cssText = `
                display: block;
                position: fixed;
                z-index: 10000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0,0,0,0.4);
            `;
            modal.innerHTML = `
                <div style="
                    background: white;
                    margin: 15% auto;
                    padding: 0;
                    border-radius: 8px;
                    width: 80%;
                    max-width: 600px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                ">
                    <div style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 20px;
                        border-bottom: 1px solid #e5e7eb;
                    ">
                        <h3 style="margin: 0;">${title}</h3>
                        <span onclick="closeModal(this)" style="
                            font-size: 28px;
                            font-weight: bold;
                            cursor: pointer;
                            color: #aaa;
                        ">&times;</span>
                    </div>
                    <div style="padding: 20px;">
                        ${content}
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            modal.onclick = function(event) {
                if (event.target === modal) {
                    closeModal(modal.querySelector('span'));
                }
            };
        }

        function closeModal(closeBtn) {
            const modal = closeBtn.closest('div[class="modal"]') || closeBtn.parentElement.parentElement.parentElement;
            document.body.removeChild(modal);
        }

        // Initialisation du graphique Chart.js
        function initializeChart(chartData, hasChanges) {
            if (!hasChanges || !chartData.labels.length) return;
            
            const ctx = document.getElementById('changesChart');
            if (!ctx) return;
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.data,
                        backgroundColor: chartData.colors,
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const percentage = formatPercentage((value / total) * 100);
                                        return {
                                            text: `${label}: ${formatNumber(value)} (${percentage})`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].backgroundColor[i],
                                            pointStyle: 'circle'
                                        };
                                    });
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = formatPercentage((context.parsed / total) * 100);
                                    return `${context.label}: ${formatNumber(context.parsed)} (${percentage})`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Ajout d'une section métriques de performance
        function addPerformanceMetrics() {
            if (typeof window.migrationData === 'undefined') return;
            
            const stats = window.migrationData.stats;
            
            const migrationRate = stats.total_files > 0 ? (stats.modified_files / stats.total_files) * 100 : 0;
            const changesDensity = stats.modified_files > 0 ? (stats.total_changes / stats.modified_files) : 0;
            const successRate = stats.total_changes > 0 ? ((stats.total_changes - (stats.warnings || 0)) / stats.total_changes) * 100 : 100;
            
            const performanceSection = document.createElement('div');
            performanceSection.className = 'section enhanced-section';
            performanceSection.innerHTML = `
                <h2>📈 Métriques de performance</h2>
                <div class="performance-metrics">
                    <div class="metric-card">
                        <div class="metric-value">${formatPercentage(migrationRate)}</div>
                        <div class="metric-label">Taux de migration</div>
                        <div class="metric-trend ${migrationRate > 50 ? 'trend-up' : 'trend-down'}">
                            ${migrationRate > 50 ? '↗ Excellent' : '→ Partiel'}
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-value">${formatNumber(changesDensity, 1)}</div>
                        <div class="metric-label">Changements par fichier</div>
                        <div class="metric-trend ${changesDensity < 5 ? 'trend-up' : 'trend-down'}">
                            ${changesDensity < 5 ? '↗ Léger' : '↗ Intensif'}
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-value">${formatPercentage(successRate)}</div>
                        <div class="metric-label">Taux de succès</div>
                        <div class="metric-trend ${successRate > 95 ? 'trend-up' : successRate > 80 ? 'trend-up' : 'trend-down'}">
                            ${successRate > 95 ? '↗ Parfait' : successRate > 80 ? '↗ Bon' : '→ À améliorer'}
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-value">${formatNumber(stats.total_changes || 0)}</div>
                        <div class="metric-label">Optimisations totales</div>
                        <div class="metric-trend trend-up">
                            ↗ Modernisé
                        </div>
                    </div>
                </div>
            `;
            
            const statsGrid = document.querySelector('.stats-grid');
            if (statsGrid && stats.total_changes > 0) {
                statsGrid.parentNode.insertBefore(performanceSection, statsGrid.nextSibling);
            }
        }

        // Améliorer les animations
        function enhanceAnimations() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            document.querySelectorAll('.section, .recommendation-card').forEach(el => {
                observer.observe(el);
            });
        }

        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // S'assurer que tous les détails sont fermés par défaut
            const allDetails = document.querySelectorAll('.collapsible-content');
            allDetails.forEach(detail => {
                detail.classList.remove('active');
            });
            
            const allIcons = document.querySelectorAll('[id^="toggle-icon-"]');
            allIcons.forEach(icon => {
                icon.textContent = '▶';
            });
            
            allExpanded = false;
            
            // Animation des cartes statistiques
            animateStatCards();
            
            // Initialiser les améliorations si les données sont disponibles
            if (typeof window.migrationData !== 'undefined') {
                addPerformanceMetrics();
                enhanceAnimations();
            }
        });
    </script>
    
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