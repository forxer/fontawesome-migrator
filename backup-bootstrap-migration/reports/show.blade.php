@extends('fontawesome-migrator::layout')

@section('title', 'Rapport de migration')

@section('head-extra')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @include('fontawesome-migrator::partials.css.reports-show')
@endsection

@section('content')
    <div class="header">
        <h1><i class="fa-solid fa-file-lines"></i> Rapport de migration</h1>
        <p>Généré le {{ $timestamp }}</p>
    </div>

    <!-- Table des matières -->
    <div class="table-of-contents">
        <h3 class="toc-title"><i class="fa-solid fa-list"></i> Navigation rapide</h3>
        <ul class="toc-list">
            <li class="toc-item">
                <a href="#statistics" class="toc-link">
                    <span class="toc-icon"><i class="fa-solid fa-chart-simple"></i></span>
                    Statistiques générales
                </a>
            </li>
            @if ($stats['total_changes'] > 0)
            <li class="toc-item">
                <a href="#chart-section" class="toc-link">
                    <span class="toc-icon"><i class="fa-solid fa-chart-pie"></i></span>
                    Répartition des changements
                </a>
            </li>
            @endif
            <li class="toc-item">
                <a href="#timeline-section" class="toc-link">
                    <span class="toc-icon"><i class="fa-regular fa-clock"></i></span>
                    Chronologie de migration
                </a>
            </li>
            <li class="toc-item">
                <a href="#recommendations-section" class="toc-link">
                    <span class="toc-icon"><i class="fa-solid fa-lightbulb"></i></span>
                    Recommandations
                </a>
            </li>
            <li class="toc-item">
                <a href="#configuration-section" class="toc-link">
                    <span class="toc-icon"><i class="fa-solid fa-gear"></i></span>
                    Configuration
                </a>
            </li>
            @if (isset($migrationOptions['created_backups']) && count($migrationOptions['created_backups']) > 0)
            <li class="toc-item">
                <a href="#backups-section" class="toc-link">
                    <span class="toc-icon"><i class="fa-regular fa-floppy-disk"></i></span>
                    Sauvegardes créées ({{ count($migrationOptions['created_backups']) }})
                </a>
            </li>
            @endif
            <li class="toc-item">
                <a href="#info-section" class="toc-link">
                    <span class="toc-icon"><i class="fa-solid fa-circle-info"></i></span>
                    Informations supplémentaires
                </a>
            </li>
            @if ($stats['total_changes'] > 0)
            <li class="toc-item">
                <a href="#summary-section" class="toc-link">
                    <span class="toc-icon"><i class="fa-solid fa-clipboard-check"></i></span>
                    Résumé de migration
                </a>
            </li>
            @if (!empty($stats['asset_types']))
            <li class="toc-item">
                <a href="#assets-section" class="toc-link">
                    <span class="toc-icon"><i class="fa-solid fa-cubes"></i></span>
                    Assets détectés
                </a>
            </li>
            @endif
            <li class="toc-item">
                <a href="#details-section" class="toc-link">
                    <span class="toc-icon"><i class="fa-solid fa-code-compare"></i></span>
                    Détail des modifications
                </a>
            </li>
            @endif
        </ul>
    </div>

    <!-- Statistiques générales -->
    <div id="statistics" class="stats-grid">
        <!-- Indicateur DRY-RUN / RÉEL en premier -->
        <div class="stat-card {{ $isDryRun ? 'stat-card-warning' : 'stat-card-success' }}">
            <div class="stat-number">
                @if ($isDryRun)
                    <i class="fa-regular fa-eye"></i>
                @else
                    <i class="fa-solid fa-check-circle"></i>
                @endif
            </div>
            <div class="stat-label">Mode d'exécution</div>
            <div style="margin-top: 10px; font-size: 1.1em; font-weight: bold;">
                @if ($isDryRun)
                    <span style="color: var(--warning-color);">
                        <i class="fa-solid fa-eye"></i> DRY-RUN
                    </span>
                    <div style="font-size: 0.8em; margin-top: 5px; color: var(--text-secondary);">
                        Prévisualisation uniquement
                    </div>
                @else
                    <span style="color: var(--success-color);">
                        <i class="fa-solid fa-bolt"></i> MIGRATION RÉELLE
                    </span>
                    <div style="font-size: 0.8em; margin-top: 5px; color: var(--text-secondary);">
                        Fichiers modifiés
                    </div>
                @endif
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['total_files'], 0, ',', ' ') }}</div>
            <div class="stat-label">Fichiers analysés</div>
            @if ($stats['total_files'] > 0)
                <div style="margin-top: 10px; color: var(--blue-500); font-size: 0.9em;">
                    <i class="fa-solid fa-magnifying-glass"></i> Scan terminé
                </div>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['modified_files'], 0, ',', ' ') }}</div>
            <div class="stat-label">Fichiers modifiés</div>
            @if ($stats['modified_files'] > 0)
                <div class="metric-improvement">
                    <span><i class="fa-solid fa-file-pen"></i></span>
                    <span>{{ number_format($stats['modified_files'], 0, ',', ' ') }} fichier(s) optimisé(s)</span>
                </div>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['total_changes'], 0, ',', ' ') }}</div>
            <div class="stat-label">Total des changements</div>
            @if ($stats['total_changes'] > 0)
                <div style="margin-top: 10px; font-size: 0.9em; color: var(--success-color);">
                    <i class="fa-solid fa-check-circle"></i> Prêt pour Font Awesome 6
                </div>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['icons_migrated'] ?? 0, 0, ',', ' ') }}</div>
            <div class="stat-label">Icônes migrées</div>
            @if (($stats['icons_migrated'] ?? 0) > 0)
                <div style="margin-top: 10px; color: var(--primary-color); font-size: 0.9em;">
                    <i class="fa-solid fa-arrow-right"></i> FA5 → FA6
                </div>
            @endif
        </div>

        @if (($stats['assets_migrated'] ?? 0) > 0)
        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['assets_migrated'], 0, ',', ' ') }}</div>
            <div class="stat-label">Assets migrés</div>
            <div style="margin-top: 10px; color: var(--secondary-color); font-size: 0.9em;">
                <i class="fa-solid fa-cubes"></i> CDN + NPM
            </div>
        </div>
        @endif

        @if (!empty($stats['warnings']) && $stats['warnings'] > 0)
        <div class="stat-card" style="border-left: 4px solid var(--warning-color);">
            <div class="stat-number" style="color: var(--warning-color);">{{ number_format($stats['warnings'], 0, ',', ' ') }}</div>
            <div class="stat-label">Avertissements</div>
            <div style="margin-top: 10px; color: var(--warning-color); font-size: 0.9em;">
                <i class="fa-solid fa-triangle-exclamation"></i> Icônes renommées/dépréciées
            </div>
        </div>
        @endif
    </div>

    @if ($stats['total_changes'] > 0)
    <!-- Graphique des types de changements -->
    <div id="chart-section" class="section enhanced-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 class="section-title" style="margin: 0;"><i class="fa-solid fa-chart-pie"></i> Répartition par type de changement</h2>
            <button onclick="showChartHelpModal()" style="background: var(--primary-color); color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; transition: background-color 0.2s;">
                <i class="fa-solid fa-circle-question"></i> Comprendre les types
            </button>
        </div>
        <div class="chart-container">
            <canvas id="changesChart"></canvas>
        </div>
    </div>

    <!-- Chronologie de migration -->
    <div id="timeline-section" class="section enhanced-section">
        <h2 class="section-title"><i class="fa-regular fa-clock"></i> Chronologie de migration</h2>
        <div class="timeline-container">
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4 class="section-title"><i class="fa-solid fa-magnifying-glass"></i> Analyse effectuée</h4>
                    <p>{{ number_format($stats['total_files'], 0, ',', ' ') }} fichier(s) analysé(s) pour détecter Font Awesome 5</p>
                    <small>{{ $timestamp }}</small>
                </div>
            </div>

            @if ($stats['modified_files'] > 0)
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4 class="section-title"><i class="fa-solid fa-crosshairs"></i> Fichiers ciblés</h4>
                    <p>{{ number_format($stats['modified_files'], 0, ',', ' ') }} fichier(s) contenant du code Font Awesome 5</p>
                    <small>Détection automatique</small>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-content">
                    <h4 class="section-title"><i class="fa-solid fa-arrow-right-arrow-left"></i> Migration appliquée</h4>
                    <p>{{ number_format($stats['total_changes'], 0, ',', ' ') }} changement(s) {{ $isDryRun ? 'identifiés' : 'appliqués' }}</p>
                    <small>{{ $isDryRun ? 'Mode prévisualisation' : 'Modifications effectives' }}</small>
                </div>
            </div>
            @endif

            @if (($stats['assets_migrated'] ?? 0) > 0)
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4 class="section-title"><i class="fa-solid fa-cubes"></i> Assets migrés</h4>
                    <p>{{ number_format($stats['assets_migrated'], 0, ',', ' ') }} asset(s) CDN/NPM {{ $isDryRun ? 'détectés' : 'mis à jour' }}</p>
                    <small>Packages et liens modernisés</small>
                </div>
            </div>
            @endif

            <div class="timeline-item">
                <div class="timeline-content">
                    @if ($stats['migration_success'] ?? true)
                        <h4 class="section-title"><i class="fa-regular fa-square-check"></i> Migration {{ $isDryRun ? 'planifiée' : 'terminée' }}</h4>
                        <p>Votre code est {{ $isDryRun ? 'prêt pour' : 'maintenant compatible avec' }} Font Awesome 6</p>
                    @else
                        <h4 class="section-title"><i class="fa-solid fa-magnifying-glass"></i> Migration partielle</h4>
                        <p>Certains éléments nécessitent une vérification manuelle</p>
                    @endif
                    <small>{{ $timestamp }}</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommandations intelligentes -->
    <div id="recommendations-section" class="section enhanced-section">
        <h2 class="section-title"><i class="fa-solid fa-lightbulb"></i> Recommandations</h2>
        <div class="recommendations-grid">
            @if ($isDryRun && $stats['total_changes'] > 0)
                <div class="recommendation-card priority-high">
                    <div class="rec-icon"><i class="fa-solid fa-play"></i></div>
                    <div class="rec-content">
                        <h4 class="section-title">Prêt pour la migration</h4>
                        <p>Exécutez <code>php artisan fontawesome:migrate</code> pour appliquer ces {{ number_format($stats['total_changes'], 0, ',', ' ') }} changements.</p>
                        <button class="btn btn-primary btn-sm" onclick="copyCommand('php artisan fontawesome:migrate')"><i class="fa-regular fa-copy"></i> Copier la commande</button>
                    </div>
                </div>
            @endif

            @if (!$isDryRun && $stats['total_changes'] > 0)
                <div class="recommendation-card priority-medium">
                    <div class="rec-icon"><i class="fa-solid fa-flask"></i></div>
                    <div class="rec-content">
                        <h4 class="section-title">Tests recommandés</h4>
                        <p>Testez votre application pour vérifier que les icônes s'affichent correctement.</p>
                        <button class="btn btn-primary btn-sm" onclick="showTestingTips()"><i class="fa-solid fa-flask"></i> Conseils de test</button>
                    </div>
                </div>
            @endif

            @if (($stats['warnings'] ?? 0) > 0)
                <div class="recommendation-card priority-high">
                    <div class="rec-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="rec-content">
                        <h4 class="section-title">Icônes à vérifier</h4>
                        <p>{{ number_format($stats['warnings'], 0, ',', ' ') }} icône(s) renommée(s), dépréciée(s) ou Pro détectée(s). Vérifiez le rendu.</p>
                        <button class="btn btn-warning btn-sm" onclick="scrollToWarnings()"><i class="fa-solid fa-triangle-exclamation"></i> Voir les avertissements</button>
                    </div>
                </div>
            @endif

            @if (($stats['assets_migrated'] ?? 0) > 0)
                <div class="recommendation-card priority-medium">
                    <div class="rec-icon"><i class="fa-solid fa-download"></i></div>
                    <div class="rec-content">
                        <h4 class="section-title">Mise à jour des dépendances</h4>
                        <p>N'oubliez pas d'exécuter <code>npm install</code> pour installer les nouvelles versions.</p>
                        <button class="btn btn-primary btn-sm" onclick="copyCommand('npm install')"><i class="fa-regular fa-copy"></i> Copier npm install</button>
                    </div>
                </div>
            @endif

            @php
                $migrationScore = 0;
                if ($stats['total_files'] > 0) {
                    $migrationScore = round(($stats['modified_files'] / $stats['total_files']) * 100);
                }
            @endphp

            @if ($migrationScore >= 80)
                <div class="recommendation-card priority-success">
                    <div class="rec-icon"><i class="fa-solid fa-trophy"></i></div>
                    <div class="rec-content">
                        <h4 class="section-title">Excellent score de migration</h4>
                        <p>{{ number_format($migrationScore, 1, ',', ' ') }} % de votre code a été optimisé pour Font Awesome 6 !</p>
                    </div>
                </div>
            @elseif($migrationScore >= 50)
                <div class="recommendation-card priority-medium">
                    <div class="rec-icon"><i class="fa-regular fa-square-check"></i></div>
                    <div class="rec-content">
                        <h4 class="section-title">Bonne migration</h4>
                        <p>{{ number_format($migrationScore, 1, ',', ' ') }} % de votre code utilise maintenant Font Awesome 6.</p>
                    </div>
                </div>
            @elseif($stats['total_changes'] == 0)
                <div class="recommendation-card priority-success">
                    <div class="rec-icon"><i class="fa-solid fa-check-circle"></i></div>
                    <div class="rec-content">
                        <h4 class="section-title">Code déjà optimisé</h4>
                        <p>Votre code semble déjà compatible avec Font Awesome 6 !</p>
                    </div>
                </div>
            @endif

            <div class="recommendation-card priority-info">
                <div class="rec-icon"><i class="fa-solid fa-book"></i></div>
                <div class="rec-content">
                    <h4 class="section-title">Documentation officielle</h4>
                    <p>Consultez le guide de migration Font Awesome 6 pour plus d'informations.</p>
                    <a href="https://fontawesome.com/v6/docs/web/setup/upgrade/" target="_blank" class="btn btn-primary btn-sm"><i class="fa-solid fa-external-link"></i> Guide officiel</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Configuration et options -->
    <div id="configuration-section" class="section">
        <h2 class="section-title"><i class="fa-solid fa-gear"></i> Configuration de migration</h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <h3 class="section-title" style="margin: 0 0 10px 0; color: var(--gray-700);">Options utilisées</h3>
                <table style="margin-top: 0;">
                    <tr><td><strong>Mode</strong></td><td>{{ $isDryRun ? 'Dry-run (prévisualisation)' : 'Migration complète' }}</td></tr>
                    @if (!empty($migrationOptions['custom_path']))
                        <tr><td><strong>Chemin personnalisé</strong></td><td><code>{{ $migrationOptions['custom_path'] }}</code></td></tr>
                    @endif
                    @if ($migrationOptions['icons_only'] ?? false)
                        <tr><td><strong>Migration</strong></td><td>Icônes uniquement</td></tr>
                    @elseif($migrationOptions['assets_only'] ?? false)
                        <tr><td><strong>Migration</strong></td><td>Assets uniquement</td></tr>
                    @else
                        <tr><td><strong>Migration</strong></td><td>Complète (icônes + assets)</td></tr>
                    @endif
                    <tr><td><strong>Sauvegarde</strong></td><td>
                        @if ($migrationOptions['no_backup'] ?? false)
                            Désactivée
                        @elseif($migrationOptions['backup'] ?? false)
                            Forcée
                        @else
                            {{ ($configuration['backup_enabled'] ?? true) ? 'Activée' : 'Désactivée' }}
                        @endif
                    </td></tr>
                    @if (isset($migrationOptions['backups_count']) && $migrationOptions['backups_count'] > 0)
                    <tr><td><strong>Sauvegardes créées</strong></td><td>
                        <span style="color: var(--success-color); font-weight: bold;">
                            {{ number_format($migrationOptions['backups_count'], 0, ',', ' ') }} fichier(s) sauvegardé(s)
                        </span>
                    </td></tr>
                    @endif
                </table>
            </div>

            <div>
                <h3 class="section-title" style="margin: 0 0 10px 0; color: var(--gray-700);">Configuration</h3>
                <table style="margin-top: 0;">
                    <tr><td><strong>Type de licence</strong></td><td>{{ ucfirst($configuration['license_type'] ?? 'free') }}</td></tr>
                    <tr><td><strong>Chemins scannés</strong></td><td>
                        @if (!empty($configuration['scan_paths']))
                            @foreach($configuration['scan_paths'] as $path)
                                <code>{{ $path }}</code>@if (!$loop->last), @endif
                            @endforeach
                        @else
                            Non définis
                        @endif
                    </td></tr>
                    <tr><td><strong>Extensions</strong></td><td>
                        @if (!empty($configuration['file_extensions']))
                            @foreach($configuration['file_extensions'] as $ext)
                                <code>{{ $ext }}</code>@if (!$loop->last), @endif
                            @endforeach
                        @else
                            Toutes
                        @endif
                    </td></tr>
                </table>
            </div>
        </div>
    </div>

    @if (isset($migrationOptions['created_backups']) && count($migrationOptions['created_backups']) > 0)
    <!-- Section des sauvegardes créées -->
    <div id="backups-section" class="section enhanced-section">
        <h2 class="section-title"><i class="fa-regular fa-floppy-disk"></i> Sauvegardes créées ({{ count($migrationOptions['created_backups']) }})</h2>
        <p>Liste des fichiers sauvegardés avant modification :</p>

        <div class="backups-list">
            @foreach ($migrationOptions['created_backups'] as $backup)
            <div class="backup-item">
                <div class="backup-header">
                    <span class="backup-file"><i class="fa-solid fa-file-arrow-down"></i> {{ $backup['relative_path'] }}</span>
                    <span class="backup-date">{{ $backup['created_at'] }}</span>
                </div>
                <div class="backup-details">
                    <span class="backup-size">Taille: {{ human_readable_bytes_size($backup['size'], 2) }}</span>
                    <span class="backup-path">Sauvegarde: {{ basename($backup['backup_path']) }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="alert alert-info" style="margin-top: 20px;">
            <i class="fa-solid fa-circle-info"></i> <strong>Note :</strong> Ces sauvegardes peuvent être utilisées pour restaurer les fichiers originaux en cas de besoin.
            Utilisez la commande <code>php artisan fontawesome:backup</code> pour gérer les sauvegardes.
        </div>
    </div>
    @endif

    <!-- Informations de fin -->
    <div id="info-section" class="section">
        <h2 class="section-title"><i class="fa-solid fa-circle-info"></i> Informations supplémentaires</h2>
        <p><strong>Rapport généré :</strong> {{ $timestamp }}</p>
        <p><strong>Package :</strong> FontAwesome Migrator version {{ $packageVersion }}</p>

        @if ($stats['total_changes'] > 0 && !$isDryRun)
            <div class="alert alert-info">
                <i class="fa-solid fa-lightbulb"></i> <strong>Conseil :</strong> Testez votre application pour vous assurer que tous les changements fonctionnent correctement.
            </div>
        @endif

        @if ($isDryRun && $stats['total_changes'] > 0)
            <div class="alert alert-warning">
                <i class="fa-solid fa-play"></i> <strong>Prêt pour la migration :</strong> Exécutez <code>php artisan fontawesome:migrate</code> pour appliquer ces changements.
            </div>
        @endif
    </div>

    @if ($stats['total_changes'] > 0)
        <!-- Résumé de migration -->
        <div id="summary-section" class="section">
            <h2 class="section-title"><i class="fa-solid fa-clipboard-check"></i> Résumé de la migration</h2>

            @if ($stats['migration_success'])
                <div class="alert alert-success">
                    <i class="fa-regular fa-square-check"></i> Migration terminée avec succès ! {{ number_format($stats['total_changes'], 0, ',', ' ') }} changement(s) appliqué(s) sur {{ number_format($stats['modified_files'], 0, ',', ' ') }} fichier(s).
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fa-solid fa-magnifying-glass"></i> Migration partielle. Certains éléments n'ont pas pu être migrés automatiquement.
                </div>
            @endif

            @if (!empty($stats['changes_by_type']))
                <table>
                    <tr><th>Type de changement</th><th>Nombre</th><th>Pourcentage</th></tr>
                    @foreach($stats['changes_by_type'] as $type => $count)
                        @php
                            $percentage = $stats['total_changes'] > 0 ? round(($count / $stats['total_changes']) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td>
                                <span class="badge">
                                    @switch($type)
                                        @case('style_update') Mise à jour de style @break
                                        @case('renamed_icon') Icône renommée @break
                                        @case('pro_fallback') Fallback Pro→Free @break
                                        @case('asset') Asset migré @break
                                        @case('deprecated_icon') Icône dépréciée @break
                                        @case('manual_review') Révision manuelle @break
                                        @default {{ ucfirst(str_replace('_', ' ', $type)) }}
                                    @endswitch
                                </span>
                            </td>
                            <td>{{ number_format($count, 0, ',', ' ') }}</td>
                            <td>{{ number_format($percentage, 1, ',', ' ') }} %</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>

        <!-- Section des assets si présents -->
        @if (!empty($stats['asset_types']))
            <div id="assets-section" class="section">
                <h2 class="section-title"><i class="fa-solid fa-cubes"></i> Assets détectés</h2>
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
    <div id="details-section" class="section enhanced-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 class="section-title"><i class="fa-solid fa-code-compare"></i> Détail des modifications</h2>

            <div class="export-buttons">
                <button class="btn btn-primary btn-sm" onclick="copyToClipboard()">
                    <i class="fa-regular fa-copy"></i> Copier le rapport
                </button>
                <button class="btn btn-primary btn-sm" onclick="toggleAllDetails()">
                    <i class="fa-solid fa-expand"></i> Développer/Réduire
                </button>
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <input type="text"
                   class="search-box"
                   id="searchBox"
                   placeholder="Rechercher dans les fichiers, changements ou extensions..."
                   onkeyup="filterChanges()"
                   style="display: block; width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 16px;">
        </div>

        <div id="modificationsContainer">
            @if ($stats['total_changes'] > 0)
                @foreach($results as $index => $result)
                    @if (!empty($result['changes']))
                        <div class="file-item" data-file="{{ $result['file'] }}" data-index="{{ $index }}">
                            <div class="file-path" style="display: flex; justify-content: space-between; align-items: center;">
                                <span><i class="fa-solid fa-file-code"></i> {{ $result['file'] }}</span>
                                <button class="toggle-btn" onclick="toggleFileDetails({{ $index }})">
                                    <span id="toggle-icon-{{ $index }}">▶</span>
                                    {{ count($result['changes']) }} changement(s)
                                </button>
                            </div>

                            <div class="collapsible-content" id="details-{{ $index }}">
                                @foreach($result['changes'] as $changeIndex => $change)
                                    @php
                                        // Chercher si ce changement a un avertissement correspondant
                                        $hasWarning = in_array($change['type'] ?? '', ['pro_fallback', 'renamed_icon', 'deprecated_icon', 'manual_review']);
                                        $warningMessage = null;

                                        if ($hasWarning && !empty($result['warnings'])) {
                                            // Essayer de trouver le warning correspondant
                                            foreach ($result['warnings'] as $warning) {
                                                if (str_contains($warning, $change['from'] ?? '')) {
                                                    $warningMessage = $warning;
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp

                                    <div class="change-item {{ $hasWarning ? 'change-with-warning' : '' }}"
                                         data-change-from="{{ $change['from'] }}"
                                         data-change-to="{{ $change['to'] }}">

                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 15px;">
                                            <div style="flex: 1;">
                                                <div class="change-from">- {{ $change['from'] }}</div>
                                                <div class="change-to">+ {{ $change['to'] }}</div>

                                                {{-- Afficher l'avertissement spécifique si présent --}}
                                                @if ($hasWarning && $warningMessage)
                                                    <div style="margin-top: 8px; padding: 8px 12px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; font-size: 0.9em;">
                                                        <div style="display: flex; align-items: center; gap: 8px;">
                                                            <span style="color: var(--warning-color); font-weight: bold;"><i class="fa-solid fa-triangle-exclamation"></i></span>
                                                            <span style="color: var(--gray-700);">{{ $warningMessage }}</span>
                                                        </div>

                                                        {{-- Conseils contextuels selon le type --}}
                                                        @switch($change['type'])
                                                            @case('pro_fallback')
                                                                <div style="margin-top: 6px; font-size: 0.8em; color: var(--gray-600);">
                                                                    <i class="fa-solid fa-crown"></i> <em>Considérez une licence Pro pour conserver le style original</em>
                                                                </div>
                                                                @break
                                                            @case('renamed_icon')
                                                                <div style="margin-top: 6px; font-size: 0.8em; color: var(--gray-600);">
                                                                    <i class="fa-regular fa-square-check"></i> <em>Renommage automatique appliqué</em>
                                                                </div>
                                                                @break
                                                            @case('deprecated_icon')
                                                                <div style="margin-top: 6px; font-size: 0.8em; color: var(--gray-600);">
                                                                    <i class="fa-regular fa-eye"></i> <em>Vérifiez le rendu et remplacez manuellement si nécessaire</em>
                                                                </div>
                                                                @break
                                                            @case('manual_review')
                                                                <div style="margin-top: 6px; font-size: 0.8em; color: var(--gray-600);">
                                                                    <i class="fa-regular fa-eye"></i> <em>Révision manuelle recommandée</em>
                                                                </div>
                                                                @break
                                                        @endswitch
                                                    </div>
                                                @endif
                                            </div>

                                            <div style="text-align: right; color: var(--gray-500); font-size: 0.8em; min-width: 120px;">
                                                @if (isset($change['line']))
                                                    <div style="background: var(--primary-color); color: white; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; margin-bottom: 6px; display: inline-block; min-width: 35px; text-align: center; white-space: nowrap;">
                                                        <i class="fa-solid fa-hashtag"></i> L.{{ $change['line'] }}
                                                    </div><br>
                                                @endif

                                                @if (isset($change['type']))
                                                    @php
                                                        $typeLabels = [
                                                            'style_update' => ['label' => 'Style', 'color' => 'var(--primary-color)'],
                                                            'renamed_icon' => ['label' => 'Renommé', 'color' => 'var(--warning-color)'],
                                                            'pro_fallback' => ['label' => 'Fallback', 'color' => 'var(--error-color)'],
                                                            'deprecated_icon' => ['label' => 'Déprécié', 'color' => 'var(--error-color)'],
                                                            'manual_review' => ['label' => 'Manuel', 'color' => 'var(--warning-color)'],
                                                            'asset' => ['label' => 'Asset', 'color' => 'var(--success-color)'],
                                                        ];
                                                        $typeInfo = $typeLabels[$change['type']] ?? ['label' => ucfirst($change['type']), 'color' => 'var(--gray-500)'];
                                                    @endphp
                                                    <span style="background: {{ $typeInfo['color'] }}; color: white; padding: 3px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; text-transform: uppercase; white-space: nowrap; display: inline-block;">
                                                        {{ $typeInfo['label'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if (!empty($result['assets']))
                                    <div class="timeline-item">
                                        <strong>Assets détectés :</strong>
                                        @foreach($result['assets'] as $asset)
                                            <div style="margin: 5px 0; font-family: monospace; font-size: 0.9em;">
                                                <i class="fa-solid fa-cube"></i> {{ $asset['type'] ?? 'unknown' }}: <code>{{ $asset['original'] ?? '' }}</code>
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
                    <i class="fa-solid fa-check-circle"></i> Aucun changement nécessaire. Votre code semble déjà compatible avec Font Awesome 6.
                </div>
            @endif
        </div>

        <div id="noResults" style="display: none; text-align: center; padding: 40px; color: var(--gray-500);">
            <div style="font-size: 3em;"><i class="fa-regular fa-eye"></i></div>
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
                showNotification('<i class="fa-regular fa-trash-can"></i> Données du rapport non disponibles', 'error');
                return;
            }

            const textReport = generateTextReport(window.migrationData);

            navigator.clipboard.writeText(textReport).then(() => {
                showNotification('<i class="fa-regular fa-chart-bar"></i> Rapport copié dans le presse-papier !', 'success');
            }).catch(() => {
                showNotification('<i class="fa-regular fa-trash-can"></i> Erreur lors de la copie', 'error');
            });
        }

        // Génération du rapport texte
        function generateTextReport(data) {
            let report = `RAPPORT DE MIGRATION FONT AWESOME\n`;
            report += `${'='.repeat(50)}\n\n`;
            report += `Généré le: ${data.timestamp}\n`;
            report += `Version: FontAwesome Migrator ${data.packageVersion}\n`;
            report += `Mode: ${data.isDryRun ? 'Dry-run (prévisualisation)' : 'Migration complète'}\n\n`;

            report += `STATISTIQUES:\n`;
            report += `- Fichiers analysés: ${formatNumber(data.stats.total_files)}\n`;
            report += `- Fichiers modifiés: ${formatNumber(data.stats.modified_files)}\n`;
            report += `- Total changements: ${formatNumber(data.stats.total_changes)}\n`;
            report += `- Icônes migrées: ${formatNumber(data.stats.icons_migrated || 0)}\n`;
            report += `- Assets migrés: ${formatNumber(data.stats.assets_migrated || 0)}\n\n`;

            if (data.files.length > 0) {
                report += `DÉTAIL DES MODIFICATIONS:\n`;
                data.files.forEach(file => {
                    if (file.changes && file.changes.length > 0) {
                        report += `\n${file.file}\n`;
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
                showNotification(`<i class="fa-regular fa-chart-bar"></i> Commande copiée: ${command}`, 'success');
            }).catch(() => {
                showNotification('<i class="fa-regular fa-trash-can"></i> Erreur lors de la copie', 'error');
            });
        }

        function showTestingTips() {
            const content = `
                <ul class="tips-list">
                    <li><strong><i class="fa-regular fa-eye"></i> Vérification visuelle :</strong> Naviguez sur votre site et vérifiez que toutes les icônes s'affichent correctement.</li>
                    <li><strong><i class="fa-regular fa-trash-can"></i> Tests multi-appareils :</strong> Testez sur différentes tailles d'écrans (mobile, tablette, desktop).</li>
                    <li><strong><i class="fa-solid fa-bullseye"></i> Compatibilité navigateurs :</strong> Vérifiez Chrome, Firefox, Safari et Edge.</li>
                    <li><strong><i class="fa-solid fa-bolt"></i> Performance :</strong> Utilisez les outils de développement pour vérifier les temps de chargement.</li>
                    <li><strong><i class="fa-solid fa-gear"></i> Cohérence design :</strong> Assurez-vous que le style et la taille des icônes restent cohérents.</li>
                    <li><strong><i class="fa-solid fa-bolt"></i> Cache navigateur :</strong> Videz le cache ou testez en navigation privée.</li>
                </ul>
            `;

            ModalSystem.show('<i class="fa-solid fa-bullseye"></i> Conseils de test', content, {
                id: 'testing-tips-modal',
                simpleHeader: false
            });
        }

        function scrollToWarnings() {
            showWarningsModal();
        }

        // Fonction pour afficher les avertissements dans une modal
        function showWarningsModal() {
            // Utiliser les données enrichies depuis PHP
            const enrichedWarnings = @json($enrichedWarnings ?? []);

            if (enrichedWarnings.length === 0) {
                ModalSystem.show('<i class="fa-regular fa-square-check"></i> Aucun avertissement',
                    '<p style="text-align: center; color: var(--success-color); font-size: 18px; margin: 20px 0;"><i class="fa-solid fa-bolt"></i> Félicitations ! Aucun avertissement détecté dans cette migration.</p>',
                    { id: 'no-warnings-modal', simpleHeader: true }
                );
                return;
            }

            // Grouper les avertissements par fichier
            const warningsByFile = {};
            enrichedWarnings.forEach(warning => {
                if (!warningsByFile[warning.file]) {
                    warningsByFile[warning.file] = [];
                }
                warningsByFile[warning.file].push(warning);
            });

            const warningCount = enrichedWarnings.length;

            // Construire le contenu de la modal
            let content = `
                <div style="margin-bottom: 20px; padding: 15px; background: var(--gray-50); border-radius: 8px; border-left: 4px solid var(--warning-color);">
                    <strong style="color: var(--warning-color);"><i class="fa-solid fa-magnifying-glass"></i> ${warningCount} avertissement(s) détecté(s)</strong>
                    <p style="margin: 5px 0 0 0; color: var(--gray-600);">
                        Ces éléments nécessitent une vérification manuelle après la migration.
                    </p>
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
            `;

            Object.keys(warningsByFile).forEach(fileName => {
                const fileWarnings = warningsByFile[fileName];
                content += `
                    <div style="margin-bottom: 20px; border: 1px solid var(--gray-200); border-radius: 8px; overflow: hidden;">
                        <div style="background: var(--gray-100); padding: 12px; border-bottom: 1px solid var(--gray-200);">
                            <strong style="color: var(--gray-700);"><i class="fa-regular fa-folder"></i> ${fileName}</strong>
                            <span style="background: var(--warning-color); color: white; padding: 4px 12px; border-radius: 16px; font-size: 12px; font-weight: bold; margin-left: 10px; white-space: nowrap; display: inline-block; min-width: 20px; text-align: center;">
                                ${fileWarnings.length} avertissement(s)
                            </span>
                        </div>
                        <div style="padding: 12px;">
                `;

                fileWarnings.forEach(warning => {
                    content += `
                        <div style="margin-bottom: 10px; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                <div style="font-size: 14px; color: var(--gray-700); flex: 1;">
                                    ${warning.message}
                                </div>
                                ${warning.line && warning.line !== 'N/A' && warning.line !== null ? `
                                    <div style="background: var(--primary-color); color: white; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; margin-left: 10px; white-space: nowrap; display: inline-block; min-width: 30px; text-align: center;">
                                        L.${warning.line}
                                    </div>
                                ` : ''}
                            </div>
                            ${warning.change ? `
                                <div style="font-size: 12px; margin-top: 8px; padding: 8px; background: #f8f9fa; border-radius: 4px;">
                                    <code style="color: var(--error-color);">${warning.change.from}</code>
                                    →
                                    <code style="color: var(--success-color);">${warning.change.to}</code>
                                </div>
                            ` : ''}
                        </div>
                    `;
                });

                content += `
                        </div>
                    </div>
                `;
            });

            content += `</div>`;

            // Ajouter les conseils d'action
            content += `
                <div style="margin-top: 20px; padding: 15px; background-color: #e6f3ff; border-radius: 8px; border-left: 4px solid var(--primary-color);">
                    <strong style="color: var(--gray-700);"><i class="fa-solid fa-bolt"></i> Actions recommandées :</strong>
                    <ul style="margin: 10px 0 0 0; padding-left: 20px; color: var(--gray-600);">
                        <li>Vérifiez visuellement chaque icône concernée</li>
                        <li>Testez le rendu sur différents navigateurs</li>
                        <li>Remplacez manuellement les icônes dépréciées si nécessaire</li>
                        <li>Considérez une licence Pro si vous utilisez des styles Pro</li>
                    </ul>
                </div>
            `;

            ModalSystem.show(`<i class="fa-solid fa-magnifying-glass"></i> Avertissements de migration (${warningCount})`, content, {
                id: 'warnings-modal',
                simpleHeader: true
            });
        }

        // Système de gestion des modales unifié
        const ModalSystem = {
            // Styles CSS pour les modales
            getModalStyles: function() {
                return {
                    overlay: `
                        position: fixed;
                        z-index: 1000;
                        left: 0;
                        top: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0,0,0,0.6);
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    `,
                    container: `
                        background: white;
                        border-radius: 12px;
                        max-width: 700px;
                        width: 90%;
                        max-height: 80vh;
                        overflow-y: auto;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                        position: relative;
                    `,
                    header: `
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: 20px 30px;
                        border-bottom: 1px solid #e5e7eb;
                    `,
                    closeButton: `
                        position: absolute;
                        top: 15px;
                        right: 20px;
                        background: none;
                        border: none;
                        font-size: 24px;
                        color: var(--gray-500);
                        cursor: pointer;
                        padding: 0;
                        line-height: 1;
                    `,
                    content: `
                        padding: 20px 30px;
                    `
                };
            },

            // Créer et afficher une modal
            show: function(title, content, options = {}) {
                const modalId = options.id || 'modal-' + Date.now();
                const styles = this.getModalStyles();

                const modal = document.createElement('div');
                modal.id = modalId;
                modal.className = 'modal';
                modal.style.cssText = styles.overlay;

                const useSimpleHeader = options.simpleHeader !== false;

                modal.innerHTML = `
                    <div style="${styles.container}">
                        ${useSimpleHeader ? `
                            <button onclick="ModalSystem.hide('${modalId}')" style="${styles.closeButton}">
                                ×
                            </button>
                            <div style="${styles.content}">
                                <h3 class="section-title" style="margin: 0 0 20px 0; color: var(--gray-700); font-size: 24px;">${title}</h3>
                                ${content}
                            </div>
                        ` : `
                            <div style="${styles.header}">
                                <h3 class="section-title" style="margin: 0;">${title}</h3>
                                <span onclick="ModalSystem.hide('${modalId}')" style="
                                    font-size: 28px;
                                    font-weight: bold;
                                    cursor: pointer;
                                    color: #6b7280;
                                ">&times;</span>
                            </div>
                            <div style="${styles.content}">
                                ${content}
                            </div>
                        `}
                    </div>
                `;

                document.body.appendChild(modal);
                document.body.style.overflow = 'hidden';

                // Gestion des événements
                modal.onclick = (event) => {
                    if (event.target === modal) {
                        this.hide(modalId);
                    }
                };

                // Fermeture avec Escape
                const escapeHandler = (event) => {
                    if (event.key === 'Escape') {
                        this.hide(modalId);
                    }
                };
                document.addEventListener('keydown', escapeHandler);
                modal._escapeHandler = escapeHandler;

                return modalId;
            },

            // Fermer une modal
            hide: function(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    if (modal._escapeHandler) {
                        document.removeEventListener('keydown', modal._escapeHandler);
                    }
                    document.body.removeChild(modal);
                    document.body.style.overflow = 'auto';
                }
            },

            // Fermer toutes les modales
            hideAll: function() {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    if (modal._escapeHandler) {
                        document.removeEventListener('keydown', modal._escapeHandler);
                    }
                    document.body.removeChild(modal);
                });
                document.body.style.overflow = 'auto';
            }
        };

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
            performanceSection.id = 'performance-section';
            performanceSection.className = 'section enhanced-section';
            performanceSection.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 class="section-title" style="margin: 0;"><i class="fa-regular fa-chart-bar"></i> Métriques de performance</h2>
                    <button onclick="showPerformanceHelpModal()" style="background: var(--primary-color); color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; transition: background-color 0.2s;">
                        <i class="fa-solid fa-palette"></i> Comprendre les métriques
                    </button>
                </div>
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
                @if (!empty($stats['changes_by_type']))
                    @foreach($stats['changes_by_type'] as $type => $count)
                        '@switch($type)
                            @case('style_update') Mise à jour de style @break
                            @case('renamed_icon') Icône renommée @break
                            @case('pro_fallback') Fallback Pro→Free @break
                            @case('asset') Asset migré @break
                            @case('deprecated_icon') Icône dépréciée @break
                            @case('manual_review') Révision manuelle @break
                            @default {{ ucfirst(str_replace("_", " ", $type)) }}
                        @endswitch',
                    @endforeach
                @endif
            ],
            data: [
                @if (!empty($stats['changes_by_type']))
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
        @if ($stats['total_changes'] > 0 && !empty($stats['changes_by_type']))
            initializeChart(chartData, true);
        @endif

        // Fonction pour la modal d'aide des types de changements
        window.showChartHelpModal = function() {
            const content = `
                <p style="color: var(--gray-600); margin-bottom: 25px; line-height: 1.6;">
                    Le graphique montre la répartition des différents types de modifications effectuées lors de la migration Font Awesome 5 vers 6 :
                </p>

                <div style="display: grid; gap: 16px;">
                    <div style="padding: 16px; border-radius: 8px; border: 1px solid var(--gray-200); background: var(--gray-50);">
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <div style="width: 12px; height: 12px; background: var(--primary-color); border-radius: 50%; margin-right: 12px;"></div>
                            <strong style="color: var(--gray-700); font-size: 16px;">Mise à jour de style</strong>
                        </div>
                        <p style="margin: 0; color: var(--gray-600); line-height: 1.5;">
                            Conversion automatique des styles FA5 vers FA6.<br>
                            <strong>Exemple :</strong> <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 4px;">fas fa-home</code> → <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 4px;">fa-regular fa-bookmark</code>
                        </p>
                    </div>

                    <div style="padding: 16px; border-radius: 8px; border: 1px solid var(--gray-200); background: var(--gray-50);">
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <div style="width: 12px; height: 12px; background: var(--secondary-color); border-radius: 50%; margin-right: 12px;"></div>
                            <strong style="color: var(--gray-700); font-size: 16px;">Icône renommée</strong>
                        </div>
                        <p style="margin: 0; color: var(--gray-600); line-height: 1.5;">
                            Icônes qui ont changé de nom entre Font Awesome 5 et 6.<br>
                            <strong>Exemple :</strong> <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 4px;">fa-times</code> → <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 4px;">fa-xmark</code>
                        </p>
                    </div>

                    <div style="padding: 16px; border-radius: 8px; border: 1px solid var(--gray-200); background: var(--gray-50);">
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <div style="width: 12px; height: 12px; background: var(--warning-color); border-radius: 50%; margin-right: 12px;"></div>
                            <strong style="color: var(--gray-700); font-size: 16px;">Fallback Pro→Free</strong>
                        </div>
                        <p style="margin: 0; color: var(--gray-600); line-height: 1.5;">
                            Conversion automatique des styles Pro vers Free quand vous avez une licence Free.<br>
                            <strong>Exemple :</strong> <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 4px;">fal fa-star</code> → <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 4px;">far fa-star</code>
                        </p>
                    </div>

                    <div style="padding: 16px; border-radius: 8px; border: 1px solid var(--gray-200); background: var(--gray-50);">
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <div style="width: 12px; height: 12px; background: var(--success-color); border-radius: 50%; margin-right: 12px;"></div>
                            <strong style="color: var(--gray-700); font-size: 16px;">Asset migré</strong>
                        </div>
                        <p style="margin: 0; color: var(--gray-600); line-height: 1.5;">
                            Migration des ressources externes (CDN, imports, packages).<br>
                            <strong>Exemple :</strong> URLs CDN v5 → v6, imports JavaScript, package.json
                        </p>
                    </div>

                    <div style="padding: 16px; border-radius: 8px; border: 1px solid var(--gray-200); background: var(--gray-50);">
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <div style="width: 12px; height: 12px; background: var(--error-color); border-radius: 50%; margin-right: 12px;"></div>
                            <strong style="color: var(--gray-700); font-size: 16px;">Icône dépréciée</strong>
                        </div>
                        <p style="margin: 0; color: var(--gray-600); line-height: 1.5;">
                            Icônes qui n'existent plus en Font Awesome 6 et nécessitent une révision manuelle.
                        </p>
                    </div>

                    <div style="padding: 16px; border-radius: 8px; border: 1px solid var(--gray-200); background: var(--gray-50);">
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <div style="width: 12px; height: 12px; background: #8b5cf6; border-radius: 50%; margin-right: 12px;"></div>
                            <strong style="color: var(--gray-700); font-size: 16px;">Révision manuelle</strong>
                        </div>
                        <p style="margin: 0; color: var(--gray-600); line-height: 1.5;">
                            Changements qui nécessitent une vérification et une intervention manuelle.
                        </p>
                    </div>
                </div>

                <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--gray-200); text-align: center;">
                    <button onclick="ModalSystem.hide('chartHelpModal')" style="background: var(--primary-color); color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 16px;">
                        Compris !
                    </button>
                </div>
            `;

            ModalSystem.show('<i class="fa-regular fa-chart-bar"></i> Comprendre les types de changements', content, {
                id: 'chartHelpModal',
                simpleHeader: true
            });
        };

        // Fonction pour la modal d'aide des métriques de performance
        window.showPerformanceHelpModal = function() {
            const content = `
                <p style="color: var(--gray-600); margin-bottom: 25px; line-height: 1.6;">
                    Ces métriques vous aident à évaluer la qualité et l'efficacité de votre migration Font Awesome 5 vers 6 :
                </p>

                <div style="display: grid; gap: 16px;">
                    <div style="padding: 16px; border-radius: 8px; border: 1px solid var(--gray-200); background: var(--gray-50);">
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <div style="width: 12px; height: 12px; background: var(--primary-color); border-radius: 50%; margin-right: 12px;"></div>
                            <strong style="color: var(--gray-700); font-size: 16px;">Taux de migration</strong>
                        </div>
                        <p style="margin: 0; color: var(--gray-600); line-height: 1.5;">
                            Pourcentage de fichiers modifiés par rapport au total scanné.<br>
                            <span class="metric-trend trend-up">↗ Excellent</span> > 50% des fichiers migrés<br>
                            <span class="metric-trend trend-down">→ Partiel</span> ≤ 50% des fichiers migrés
                        </p>
                    </div>

                    <div style="padding: 16px; border-radius: 8px; border: 1px solid var(--gray-200); background: var(--gray-50);">
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <div style="width: 12px; height: 12px; background: var(--secondary-color); border-radius: 50%; margin-right: 12px;"></div>
                            <strong style="color: var(--gray-700); font-size: 16px;">Changements par fichier</strong>
                        </div>
                        <p style="margin: 0; color: var(--gray-600); line-height: 1.5;">
                            Densité moyenne des modifications par fichier.<br>
                            <span class="metric-trend trend-up">↗ Léger</span> < 5 changements par fichier (peu d'icônes FA)<br>
                            <span class="metric-trend trend-down">↗ Intensif</span> ≥ 5 changements par fichier (beaucoup d'icônes FA)
                        </p>
                    </div>

                    <div style="padding: 16px; border-radius: 8px; border: 1px solid var(--gray-200); background: var(--gray-50);">
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <div style="width: 12px; height: 12px; background: var(--success-color); border-radius: 50%; margin-right: 12px;"></div>
                            <strong style="color: var(--gray-700); font-size: 16px;">Taux de succès</strong>
                        </div>
                        <p style="margin: 0; color: var(--gray-600); line-height: 1.5;">
                            Pourcentage de changements réussis sans avertissement.<br>
                            <span class="metric-trend trend-up">↗ Parfait</span> > 95% sans avertissement<br>
                            <span class="metric-trend trend-up">↗ Bon</span> 80-95% sans avertissement<br>
                            <span class="metric-trend trend-down">→ À améliorer</span> < 80% sans avertissement
                        </p>
                    </div>

                    <div style="padding: 16px; border-radius: 8px; border: 1px solid var(--gray-200); background: var(--gray-50);">
                        <div style="display: flex; align-items: center; margin-bottom: 8px;">
                            <div style="width: 12px; height: 12px; background: var(--warning-color); border-radius: 50%; margin-right: 12px;"></div>
                            <strong style="color: var(--gray-700); font-size: 16px;">Optimisations totales</strong>
                        </div>
                        <p style="margin: 0; color: var(--gray-600); line-height: 1.5;">
                            Nombre total de changements appliqués (icônes + assets).<br>
                            <span class="metric-trend trend-up">↗ Modernisé</span> Votre code utilise maintenant Font Awesome 6
                        </p>
                    </div>
                </div>

                <div style="margin-top: 25px; padding: 15px; background-color: #e6f3ff; border-radius: 8px; border-left: 4px solid var(--primary-color);">
                    <strong style="color: var(--gray-700);"><i class="fa-solid fa-bolt"></i> Conseil d'interprétation :</strong>
                    <p style="margin: 5px 0 0 0; color: var(--gray-600); line-height: 1.5;">
                        Un bon score combine un taux de migration élevé et un taux de succès élevé.
                        Si le taux de succès est faible, vérifiez les avertissements pour identifier les éléments nécessitant une révision manuelle.
                    </p>
                </div>

                <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--gray-200); text-align: center;">
                    <button onclick="ModalSystem.hide('performanceHelpModal')" style="background: var(--primary-color); color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 16px;">
                        Compris !
                    </button>
                </div>
            `;

            ModalSystem.show('<i class="fa-regular fa-chart-bar"></i> Comprendre les métriques de performance', content, {
                id: 'performanceHelpModal',
                simpleHeader: true
            });
        };

        // Fonction de retour en haut avec animation fluide
        window.scrollToTop = function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        };

        // Initialisation après chargement du DOM
        document.addEventListener('DOMContentLoaded', function() {
            // Gestion du bouton retour en haut
            const backToTopBtn = document.getElementById('backToTop');

            if (backToTopBtn) {
                // Afficher/masquer le bouton selon le scroll
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 300) {
                        backToTopBtn.classList.add('show');
                    } else {
                        backToTopBtn.classList.remove('show');
                    }
                });
            }

            // Amélioration de la navigation de la table des matières
            document.querySelectorAll('.toc-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    const targetId = this.getAttribute('href').substring(1);
                    const targetElement = document.getElementById(targetId);

                    if (targetElement) {
                        // Scroll fluide vers la section
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });

                        // Animation de highlight de la section
                        targetElement.style.animation = 'pulse 1.5s ease-in-out';
                        setTimeout(() => {
                            targetElement.style.animation = '';
                        }, 1500);
                    }
                });
            });
        });
    </script>

    <!-- Bouton retour en haut -->
    <button id="backToTop" class="back-to-top" onclick="scrollToTop()" title="Retour en haut">
        <i class="fa-solid fa-chevron-up"></i>
    </button>

@endsection