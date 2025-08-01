@extends('fontawesome-migrator::layout')

@section('title', 'Rapport de migration')

@section('head-extra')
    @include('fontawesome-migrator::partials.css.bootstrap-common')
    @include('fontawesome-migrator::partials.css.reports-show')
@endsection

@section('content')
    <x-fontawesome-migrator::page-header
        icon="file-text"
        title="Rapport de migration"
        :subtitle="'Généré le ' . $timestamp"
        :hasActions="false"
    />

    <!-- Table des matières -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title section-title mb-3"><i class="bi bi-list"></i> Navigation rapide</h2>
            <ul class="list-unstyled mb-0">
            <li class="mb-2">
                <a href="#statistics" class="nav-link-item">
                    <i class="bi bi-graph-up text-primary"></i>
                    Statistiques générales
                </a>
            </li>
            <li class="mb-2">
                <a href="#timeline-section" class="nav-link-item">
                    <i class="bi bi-clock text-primary"></i>
                    Chronologie de migration
                </a>
            </li>
            <li class="mb-2">
                <a href="#recommendations-section" class="nav-link-item">
                    <i class="bi bi-lightbulb text-primary"></i>
                    Recommandations
                </a>
            </li>
            <li class="mb-2">
                <a href="#configuration-section" class="nav-link-item">
                    <i class="bi bi-gear text-primary"></i>
                    Configuration
                </a>
            </li>
            @if (isset($migrationOptions['created_backups']) && count($migrationOptions['created_backups']) > 0)
            <li class="mb-2">
                <a href="#backups-section" class="nav-link-item">
                    <i class="bi bi-hdd text-primary"></i>
                    Sauvegardes créées ({{ count($migrationOptions['created_backups']) }})
                </a>
            </li>
            @endif
            <li class="mb-2">
                <a href="#info-section" class="nav-link-item">
                    <i class="bi bi-info-circle text-primary"></i>
                    Informations supplémentaires
                </a>
            </li>
            @if ($stats['total_changes'] > 0)
            <li class="mb-2">
                <a href="#summary-section" class="nav-link-item">
                    <i class="bi bi-clipboard-check text-primary"></i>
                    Résumé de migration
                </a>
            </li>
            @if (!empty($stats['asset_types']))
            <li class="mb-2">
                <a href="#assets-section" class="nav-link-item">
                    <i class="bi bi-box text-primary"></i>
                    Assets détectés
                </a>
            </li>
            @endif
            <li class="mb-2">
                <a href="#details-section" class="nav-link-item">
                    <i class="bi bi-code-slash text-primary"></i>
                    Détail des modifications
                </a>
            </li>
            @endif
        </ul>
        </div>
    </div>

    <!-- Statistiques générales -->
    <div id="statistics" class="row g-3 mb-4">
        <!-- Indicateur DRY-RUN / RÉEL en premier -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 {{ $isDryRun ? 'border-warning' : 'border-success' }} border-2">
                <div class="card-body text-center">
                    <div class="fs-1 mb-3">
                        @if ($isDryRun)
                            <i class="bi bi-eye text-warning"></i>
                        @else
                            <i class="bi bi-check-circle text-success"></i>
                        @endif
                    </div>
                    <h5 class="card-title">Mode d'exécution</h5>
                    <div class="mt-3">
                        @if ($isDryRun)
                            <span class="text-warning fw-bold">
                                <i class="bi bi-eye"></i> DRY-RUN
                            </span>
                            <div class="small text-muted mt-2">
                                Prévisualisation uniquement
                            </div>
                        @else
                            <span class="text-success fw-bold">
                                <i class="bi bi-lightning-fill"></i> MIGRATION RÉELLE
                            </span>
                            <div class="small text-muted mt-2">
                                Fichiers modifiés
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h2 class="card-title display-6 mb-3">{{ number_format($stats['total_files'], 0, ',', ' ') }}</h2>
                    <h5 class="card-subtitle text-muted">Fichiers analysés</h5>
                    @if ($stats['total_files'] > 0)
                        <div class="mt-3 text-primary small">
                            <i class="bi bi-search"></i> Scan terminé
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h2 class="card-title display-6 mb-3">{{ number_format($stats['modified_files'], 0, ',', ' ') }}</h2>
                    <h5 class="card-subtitle text-muted">Fichiers modifiés</h5>
                    @if ($stats['modified_files'] > 0)
                        <div class="mt-3 text-success small">
                            <i class="bi bi-pencil-square"></i>
                            {{ number_format($stats['modified_files'], 0, ',', ' ') }} fichier(s) optimisé(s)
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h2 class="card-title display-6 mb-3">{{ number_format($stats['total_changes'], 0, ',', ' ') }}</h2>
                    <h5 class="card-subtitle text-muted">Total des changements</h5>
                    @if ($stats['total_changes'] > 0)
                        <div class="mt-3 text-success small">
                            <i class="bi bi-check-circle"></i> Prêt pour Font Awesome 6
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h2 class="card-title display-6 mb-3">{{ number_format($stats['icons_migrated'] ?? 0, 0, ',', ' ') }}</h2>
                    <h5 class="card-subtitle text-muted">Icônes migrées</h5>
                    @if (($stats['icons_migrated'] ?? 0) > 0)
                        <div class="mt-3 text-primary small">
                            <i class="bi bi-arrow-right"></i> FA5 → FA6
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if (($stats['assets_migrated'] ?? 0) > 0)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h2 class="card-title display-6 mb-3">{{ number_format($stats['assets_migrated'], 0, ',', ' ') }}</h2>
                    <h5 class="card-subtitle text-muted">Assets migrés</h5>
                    <div class="mt-3 text-info small">
                        <i class="bi bi-box"></i> CDN + NPM
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if (!empty($stats['warnings']) && $stats['warnings'] > 0)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-warning border-2">
                <div class="card-body text-center">
                    <h2 class="card-title display-6 mb-3 text-warning">{{ number_format($stats['warnings'], 0, ',', ' ') }}</h2>
                    <h5 class="card-subtitle text-muted">Avertissements</h5>
                    <div class="mt-3 text-warning small">
                        <i class="bi bi-exclamation-triangle"></i> Icônes renommées/dépréciées
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if ($stats['total_changes'] > 0)
    <!-- Chronologie de migration -->
    <div id="timeline-section" class="card mb-4">
        <div class="card-body">
            <h2 class="card-title section-title"><i class="bi bi-clock"></i> Chronologie de migration</h2>
            <div class="timeline-container">
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4 class="section-title"><i class="bi bi-search"></i> Analyse effectuée</h4>
                    <p>{{ number_format($stats['total_files'], 0, ',', ' ') }} fichier(s) analysé(s) pour détecter Font Awesome 5</p>
                    <small>{{ $timestamp }}</small>
                </div>
            </div>

            @if ($stats['modified_files'] > 0)
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4 class="section-title"><i class="bi bi-bullseye"></i> Fichiers ciblés</h4>
                    <p>{{ number_format($stats['modified_files'], 0, ',', ' ') }} fichier(s) contenant du code Font Awesome 5</p>
                    <small>Détection automatique</small>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-content">
                    <h4 class="section-title"><i class="bi bi-arrow-left-right"></i> Migration appliquée</h4>
                    <p>{{ number_format($stats['total_changes'], 0, ',', ' ') }} changement(s) {{ $isDryRun ? 'identifiés' : 'appliqués' }}</p>
                    <small>{{ $isDryRun ? 'Mode prévisualisation' : 'Modifications effectives' }}</small>
                </div>
            </div>
            @endif

            @if (($stats['assets_migrated'] ?? 0) > 0)
            <div class="timeline-item">
                <div class="timeline-content">
                    <h4 class="section-title"><i class="bi bi-box"></i> Assets migrés</h4>
                    <p>{{ number_format($stats['assets_migrated'], 0, ',', ' ') }} asset(s) CDN/NPM {{ $isDryRun ? 'détectés' : 'mis à jour' }}</p>
                    <small>Packages et liens modernisés</small>
                </div>
            </div>
            @endif

            <div class="timeline-item">
                <div class="timeline-content">
                    @if ($stats['migration_success'] ?? true)
                        <h4 class="section-title"><i class="bi bi-check-square"></i> Migration {{ $isDryRun ? 'planifiée' : 'terminée' }}</h4>
                        <p>Votre code est {{ $isDryRun ? 'prêt pour' : 'maintenant compatible avec' }} Font Awesome 6</p>
                    @else
                        <h4 class="section-title"><i class="bi bi-search"></i> Migration partielle</h4>
                        <p>Certains éléments nécessitent une vérification manuelle</p>
                    @endif
                    <small>{{ $timestamp }}</small>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- Recommandations intelligentes -->
    <div id="recommendations-section" class="mb-4">
        <h2 class="section-title mb-3"><i class="bi bi-lightbulb"></i> Recommandations</h2>
        <div class="row mb-4">
            @if ($isDryRun && $stats['total_changes'] > 0)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-success border-2">
                        <div class="card-body d-flex">
                            <div class="me-3">
                                <i class="bi bi-play-fill fs-2 text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title">Prêt pour la migration</h5>
                                <p class="card-text">Exécutez <code>php artisan fontawesome:migrate</code> pour appliquer ces {{ number_format($stats['total_changes'], 0, ',', ' ') }} changements.</p>
                                <button class="btn btn-primary btn-sm" onclick="copyCommand('php artisan fontawesome:migrate')"><i class="bi bi-clipboard"></i> Copier la commande</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (!$isDryRun && $stats['total_changes'] > 0)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-info border-2">
                        <div class="card-body d-flex">
                            <div class="me-3">
                                <i class="bi bi-flask fs-2 text-info"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title">Tests recommandés</h5>
                                <p class="card-text">Testez votre application pour vérifier que les icônes s'affichent correctement.</p>
                                <button class="btn btn-info btn-sm" onclick="showTestingTips()"><i class="bi bi-flask"></i> Conseils de test</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (($stats['warnings'] ?? 0) > 0)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-warning border-2">
                        <div class="card-body d-flex">
                            <div class="me-3">
                                <i class="bi bi-exclamation-triangle fs-2 text-warning"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title">Icônes à vérifier</h5>
                                <p class="card-text">{{ number_format($stats['warnings'], 0, ',', ' ') }} icône(s) renommée(s), dépréciée(s) ou Pro détectée(s). Vérifiez le rendu.</p>
                                <button class="btn btn-warning btn-sm" onclick="scrollToWarnings()"><i class="bi bi-exclamation-triangle"></i> Voir les avertissements</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if (($stats['assets_migrated'] ?? 0) > 0)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body d-flex">
                            <div class="me-3">
                                <i class="bi bi-download fs-2 text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title">Mise à jour des dépendances</h5>
                                <p class="card-text">N'oubliez pas d'exécuter <code>npm install</code> pour installer les nouvelles versions.</p>
                                <button class="btn btn-primary btn-sm" onclick="copyCommand('npm install')"><i class="bi bi-clipboard"></i> Copier npm install</button>
                            </div>
                        </div>
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
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-success border-2">
                        <div class="card-body d-flex">
                            <div class="me-3">
                                <i class="bi bi-trophy fs-2 text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title">Excellent score de migration</h5>
                                <p class="card-text">{{ number_format($migrationScore, 1, ',', ' ') }} % de votre code a été optimisé pour Font Awesome 6 !</p>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($migrationScore >= 50)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body d-flex">
                            <div class="me-3">
                                <i class="bi bi-check-square fs-2 text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title">Bonne migration</h5>
                                <p class="card-text">{{ number_format($migrationScore, 1, ',', ' ') }} % de votre code utilise maintenant Font Awesome 6.</p>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($stats['total_changes'] == 0)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-success border-2">
                        <div class="card-body d-flex">
                            <div class="me-3">
                                <i class="bi bi-check-circle fs-2 text-success"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title">Code déjà optimisé</h5>
                                <p class="card-text">Votre code semble déjà compatible avec Font Awesome 6 !</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body d-flex">
                        <div class="me-3">
                            <i class="bi bi-book fs-2 text-info"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title">Documentation officielle</h5>
                            <p class="card-text">Consultez le guide de migration Font Awesome 6 pour plus d'informations.</p>
                            <a href="https://fontawesome.com/v6/docs/web/setup/upgrade/" target="_blank" class="btn btn-primary btn-sm"><i class="bi bi-box-arrow-up-right"></i> Guide officiel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Configuration et options -->
    <div id="configuration-section" class="mb-4">
        <h2 class="section-title mb-3"><i class="bi bi-gear"></i> Configuration de migration</h2>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="card-title section-title-sm mb-3"><i class="bi bi-sliders text-primary"></i> Options utilisées</h3>
                        <table class="table table-striped table-sm">
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
                            <span class="text-success fw-bold">
                                {{ number_format($migrationOptions['backups_count'], 0, ',', ' ') }} fichier(s) sauvegardé(s)
                            </span>
                        </td></tr>
                        @endif
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="card-title section-title-sm mb-3"><i class="bi bi-gear-fill text-info"></i> Configuration</h3>
                        <table class="table table-striped table-sm">
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
        </div>
        </div>

    @if (isset($migrationOptions['created_backups']) && count($migrationOptions['created_backups']) > 0)
    <!-- Section des sauvegardes créées -->
    <div id="backups-section" class="card mb-4">
        <div class="card-body">
            <h2 class="card-title section-title"><i class="bi bi-hdd"></i> Sauvegardes créées ({{ count($migrationOptions['created_backups']) }})</h2>
        <p>Liste des fichiers sauvegardés avant modification :</p>

        <div class="backups-list">
            @foreach ($migrationOptions['created_backups'] as $backup)
            <div class="backup-item">
                <div class="backup-header">
                    <span class="backup-file"><i class="bi bi-download"></i> {{ $backup['relative_path'] }}</span>
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
            <i class="bi bi-info-circle"></i> <strong>Note :</strong> Ces sauvegardes peuvent être utilisées pour restaurer les fichiers originaux en cas de besoin.
            Utilisez la commande <code>php artisan fontawesome:backup</code> pour gérer les sauvegardes.
        </div>
    </div>
    @endif

    <!-- Informations supplémentaires -->
    <div id="info-section" class="card mb-4">
        <div class="card-body">
            <h2 class="card-title section-title"><i class="bi bi-info-circle"></i> Informations supplémentaires</h2>
            <p><strong>Rapport généré :</strong> {{ $timestamp }}</p>
            <p><strong>Package :</strong> FontAwesome Migrator version {{ $packageVersion }}</p>

            @if ($stats['total_changes'] > 0 && !$isDryRun)
                <div class="alert alert-info">
                    <i class="bi bi-lightbulb"></i> <strong>Conseil :</strong> Testez votre application pour vous assurer que tous les changements fonctionnent correctement.
                </div>
            @endif

            @if ($isDryRun && $stats['total_changes'] > 0)
                <div class="alert alert-warning">
                    <i class="bi bi-play-fill"></i> <strong>Prêt pour la migration :</strong> Exécutez <code>php artisan fontawesome:migrate</code> pour appliquer ces changements.
                </div>
            @endif
        </div>
    </div>

    @if ($stats['total_changes'] > 0)
        <!-- Résumé de migration -->
        <div id="summary-section" class="section">
            <h2 class="section-title"><i class="bi bi-clipboard-check"></i> Résumé de la migration</h2>

            @if ($stats['migration_success'])
                <div class="alert alert-success">
                    <i class="bi bi-check-square"></i> Migration terminée avec succès ! {{ number_format($stats['total_changes'], 0, ',', ' ') }} changement(s) appliqué(s) sur {{ number_format($stats['modified_files'], 0, ',', ' ') }} fichier(s).
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="bi bi-search"></i> Migration partielle. Certains éléments n'ont pas pu être migrés automatiquement.
                </div>
            @endif

            @if (!empty($stats['changes_by_type']))
                <table class="table table-striped table-sm">
                    <thead>
                        <tr><th>Type de changement</th><th>Nombre</th><th>Pourcentage</th></tr>
                    </thead>
                    <tbody>
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
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Section des assets si présents -->
        @if (!empty($stats['asset_types']))
            <div id="assets-section" class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title section-title"><i class="bi bi-box"></i> Assets détectés</h2>
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr><th>Type d'asset</th><th>Nombre</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            @foreach($stats['asset_types'] as $assetType => $count)
                                <tr>
                                    <td><strong>{{ $assetType }}</strong></td>
                                    <td>{{ number_format($count, 0, ',', ' ') }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $assetType)) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    @endif

    <!-- Détail des fichiers modifiés avec recherche -->
    <div id="details-section" class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="card-title section-title mb-0"><i class="bi bi-code-slash"></i> Détail des modifications</h2>

                <div class="btn-group btn-group-sm" role="group" aria-label="Actions sur le rapport">
                    <button class="btn btn-primary" onclick="copyToClipboard()">
                        <i class="bi bi-clipboard"></i> Copier le rapport
                    </button>
                    <button class="btn btn-outline-primary" onclick="toggleAllDetails()">
                        <i class="bi bi-arrows-expand"></i> Développer/Réduire
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <input type="text"
                       class="form-control"
                       id="searchBox"
                       placeholder="Rechercher dans les fichiers, changements ou extensions..."
                       onkeyup="filterChanges()">
            </div>

        <div id="modificationsContainer">
            @if ($stats['total_changes'] > 0)
                @foreach($results as $index => $result)
                    @if (!empty($result['changes']))
                        <div class="card mb-3" data-file="{{ $result['file'] }}" data-index="{{ $index }}">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-file-code text-primary"></i> {{ $result['file'] }}</span>
                                    <button class="btn btn-outline-primary btn-sm" onclick="toggleFileDetails({{ $index }})">
                                        <i id="toggle-icon-{{ $index }}" class="bi bi-chevron-right"></i>
                                        {{ count($result['changes']) }} changement(s)
                                    </button>
                                </div>
                            </div>

                            <div class="collapse" id="details-{{ $index }}">
                                <div class="card-body">
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

                                    <div class="border-start border-3 ps-3 mb-3 {{ $hasWarning ? 'border-warning' : 'border-success' }}"
                                         data-change-from="{{ $change['from'] }}"
                                         data-change-to="{{ $change['to'] }}">

                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="text-danger font-monospace small">- {{ $change['from'] }}</div>
                                                <div class="text-success font-monospace small">+ {{ $change['to'] }}</div>

                                                {{-- Afficher l'avertissement spécifique si présent --}}
                                                @if ($hasWarning && $warningMessage)
                                                    <div class="alert alert-warning py-2 px-3 mt-2 small">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <i class="bi bi-exclamation-triangle text-warning me-2"></i>
                                                            <span>{{ $warningMessage }}</span>
                                                        </div>

                                                        {{-- Conseils contextuels selon le type --}}
                                                        @switch($change['type'])
                                                            @case('pro_fallback')
                                                                <div class="text-muted small mt-1">
                                                                    <i class="bi bi-gem"></i> <em>Considérez une licence Pro pour conserver le style original</em>
                                                                </div>
                                                                @break
                                                            @case('renamed_icon')
                                                                <div class="text-muted small mt-1">
                                                                    <i class="bi bi-check-square"></i> <em>Renommage automatique appliqué</em>
                                                                </div>
                                                                @break
                                                            @case('deprecated_icon')
                                                                <div class="text-muted small mt-1">
                                                                    <i class="bi bi-eye"></i> <em>Vérifiez le rendu et remplacez manuellement si nécessaire</em>
                                                                </div>
                                                                @break
                                                            @case('manual_review')
                                                                <div class="text-muted small mt-1">
                                                                    <i class="bi bi-eye"></i> <em>Révision manuelle recommandée</em>
                                                                </div>
                                                                @break
                                                        @endswitch
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="text-end text-muted small">
                                                @if (isset($change['line']))
                                                    <span class="badge bg-primary mb-1">
                                                        <i class="bi bi-hash"></i> L.{{ $change['line'] }}
                                                    </span><br>
                                                @endif

                                                @if (isset($change['type']))
                                                    @php
                                                        $typeBadges = [
                                                            'style_update' => ['label' => 'Style', 'class' => 'bg-primary'],
                                                            'renamed_icon' => ['label' => 'Renommé', 'class' => 'bg-warning'],
                                                            'pro_fallback' => ['label' => 'Fallback', 'class' => 'bg-danger'],
                                                            'deprecated_icon' => ['label' => 'Déprécié', 'class' => 'bg-danger'],
                                                            'manual_review' => ['label' => 'Manuel', 'class' => 'bg-warning'],
                                                            'asset' => ['label' => 'Asset', 'class' => 'bg-success'],
                                                        ];
                                                        $badgeInfo = $typeBadges[$change['type']] ?? ['label' => ucfirst($change['type']), 'class' => 'bg-secondary'];
                                                    @endphp
                                                    <span class="badge {{ $badgeInfo['class'] }}">
                                                        {{ $badgeInfo['label'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if (!empty($result['assets']))
                                    <div class="mt-3 p-3 bg-light rounded">
                                        <strong class="text-info"><i class="bi bi-box"></i> Assets détectés :</strong>
                                        @foreach($result['assets'] as $asset)
                                            <div class="mt-2 font-monospace small">
                                                <i class="bi bi-box text-primary"></i> {{ $asset['type'] ?? 'unknown' }}: <code>{{ $asset['original'] ?? '' }}</code>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <!-- Aucun changement -->
                <div class="alert alert-info">
                    <i class="bi bi-check-circle"></i> Aucun changement nécessaire. Votre code semble déjà compatible avec Font Awesome 6.
                </div>
            @endif
        </div>

        <div id="noResults" class="text-center py-5 text-muted" style="display: none;">
            <div class="display-3 mb-3"><i class="bi bi-search"></i></div>
            <p class="mb-0">Aucun résultat trouvé pour votre recherche</p>
        </div>
        </div>
    </div>

    {{-- JavaScript optimisé pour les rapports --}}
    <script>
        // Variables globales
        let allExpanded = false;

        // Cache des éléments DOM
        const cache = {
            searchBox: null,
            fileItems: null,
            noResults: null
        };

        // Initialisation du cache DOM
        function initCache() {
            cache.searchBox = document.getElementById('searchBox');
            cache.noResults = document.getElementById('noResults');
        }

        // Initialisation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            initCache();
        });

        // Fonction de recherche optimisée avec debounce
        let searchTimeout;
        function filterChanges() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch();
            }, 150);
        }

        function performSearch() {
            const searchTerm = cache.searchBox?.value.toLowerCase() || '';

            // Mise à jour du cache des fichiers si nécessaire
            if (!cache.fileItems) {
                const container = document.getElementById('modificationsContainer');
                cache.fileItems = container ? container.querySelectorAll('.card[data-file]') : [];
            }

            let visibleCount = 0;
            const showAll = searchTerm === '';

            cache.fileItems.forEach(item => {
                const fileName = item.dataset.file?.toLowerCase() || '';
                const fileMatches = showAll || fileName.includes(searchTerm);

                let hasVisibleChanges = false;
                if (!showAll) {
                    const changeItems = item.querySelectorAll('[data-change-from]');
                    changeItems.forEach(changeItem => {
                        const changeFrom = changeItem.dataset.changeFrom?.toLowerCase() || '';
                        const changeTo = changeItem.dataset.changeTo?.toLowerCase() || '';
                        const matches = changeFrom.includes(searchTerm) || changeTo.includes(searchTerm);

                        changeItem.style.display = (matches || fileMatches) ? 'block' : 'none';
                        if (matches || fileMatches) {
                            hasVisibleChanges = true;
                            // Surlignage simple des correspondances
                            if (searchTerm) {
                                highlightText(changeItem, searchTerm);
                            }
                        } else if (searchTerm) {
                            // Supprimer le surlignage si pas de correspondance
                            removeHighlight(changeItem);
                        }
                    });
                } else {
                    hasVisibleChanges = true;
                    // Supprimer tous les surlignages si recherche vide
                    const changeItems = item.querySelectorAll('[data-change-from]');
                    changeItems.forEach(changeItem => removeHighlight(changeItem));
                }

                const shouldShow = showAll || fileMatches || hasVisibleChanges;
                item.style.display = shouldShow ? 'block' : 'none';
                if (shouldShow) visibleCount++;
            });

            if (cache.noResults) {
                cache.noResults.style.display = visibleCount === 0 ? 'block' : 'none';
            }
        }

        // Fonction optimisée pour toggle un fichier
        function toggleFileDetails(index) {
            const details = document.getElementById(`details-${index}`);
            const icon = document.getElementById(`toggle-icon-${index}`);

            if (!details || !icon) return;

            const isVisible = details.classList.contains('show');
            details.classList.toggle('show', !isVisible);
            icon.className = isVisible ? 'bi bi-chevron-right' : 'bi bi-chevron-down';
        }

        // Toggle optimisé pour développer/réduire tous les détails
        function toggleAllDetails() {
            allExpanded = !allExpanded;
            const iconClass = allExpanded ? 'bi bi-chevron-down' : 'bi bi-chevron-right';

            document.querySelectorAll('[id^="details-"]').forEach(detail => {
                detail.classList.toggle('show', allExpanded);
            });

            document.querySelectorAll('[id^="toggle-icon-"]').forEach(icon => {
                icon.className = iconClass;
            });
        }

        // Fonction basique de copie (simplifée)
        function copyToClipboard() {
            const text = `Rapport de migration FontAwesome\nGénéré le ${new Date().toLocaleDateString('fr-FR')}`;
            navigator.clipboard.writeText(text).catch(() => {
                showNotification('Impossible de copier dans le presse-papier', 'error');
            });
        }

        // Fonction optimisée pour copier les commandes
        function copyCommand(command) {
            navigator.clipboard.writeText(command).then(() => {
                showNotification(`Commande copiée : ${command}`, 'success');
            }).catch(() => {
                showNotification('Erreur lors de la copie', 'error');
            });
        }

        // Système de notifications léger
        function showNotification(message, type = 'info') {
            // Supprimer les notifications existantes
            const existing = document.querySelector('.temp-notification');
            if (existing) existing.remove();

            const notification = document.createElement('div');
            notification.className = `alert alert-${type} temp-notification`;
            notification.style.cssText = `
                position: fixed; top: 20px; right: 20px; z-index: 9999;
                min-width: 300px; opacity: 0; transition: opacity 0.3s ease;
            `;
            notification.textContent = message;

            document.body.appendChild(notification);

            // Animation d'apparition
            setTimeout(() => notification.style.opacity = '1', 10);

            // Suppression automatique
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Modal simple pour les conseils de test
        function showTestingTips() {
            const modalHtml = `
                <div class="modal fade" id="testingTipsModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-flask"></i> Conseils de test</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <ul class="list-unstyled">
                                    <li class="mb-3"><i class="bi bi-eye text-primary"></i> <strong>Vérification visuelle :</strong> Naviguez sur votre site et vérifiez que toutes les icônes s'affichent correctement.</li>
                                    <li class="mb-3"><i class="bi bi-phone text-primary"></i> <strong>Tests multi-appareils :</strong> Testez sur différentes tailles d'écrans (mobile, tablette, desktop).</li>
                                    <li class="mb-3"><i class="bi bi-browsers text-primary"></i> <strong>Compatibilité navigateurs :</strong> Vérifiez Chrome, Firefox, Safari et Edge.</li>
                                    <li class="mb-3"><i class="bi bi-speedometer2 text-primary"></i> <strong>Performance :</strong> Utilisez les outils de développement pour vérifier les temps de chargement.</li>
                                    <li class="mb-3"><i class="bi bi-palette text-primary"></i> <strong>Cohérence design :</strong> Assurez-vous que le style et la taille des icônes restent cohérents.</li>
                                    <li class="mb-0"><i class="bi bi-arrow-clockwise text-primary"></i> <strong>Cache navigateur :</strong> Videz le cache ou testez en navigation privée.</li>
                                </ul>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Supprimer modal existante si présente
            const existing = document.getElementById('testingTipsModal');
            if (existing) existing.remove();

            // Ajouter la nouvelle modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Afficher la modal (Bootstrap 5)
            const modal = new bootstrap.Modal(document.getElementById('testingTipsModal'));
            modal.show();

            // Nettoyer après fermeture
            document.getElementById('testingTipsModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

        // Fonctions de surlignage optimisées
        function highlightText(element, searchTerm) {
            const textElements = element.querySelectorAll('.text-danger, .text-success');
            textElements.forEach(el => {
                const originalText = el.textContent;
                const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                el.innerHTML = originalText.replace(regex, '<mark>$1</mark>');
            });
        }

        function removeHighlight(element) {
            const highlighted = element.querySelectorAll('mark');
            highlighted.forEach(mark => {
                mark.outerHTML = mark.textContent;
            });
        }

        function escapeRegex(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        // Fonction simplifiée pour afficher les avertissements
        function scrollToWarnings() {
            // Chercher tous les éléments avec des avertissements
            const warningElements = document.querySelectorAll('.border-warning');

            if (warningElements.length === 0) {
                showNotification('Aucun avertissement trouvé', 'info');
                return;
            }

            // Développer tous les détails pour voir les avertissements
            document.querySelectorAll('[id^="details-"]').forEach(detail => {
                detail.classList.add('show');
            });
            document.querySelectorAll('[id^="toggle-icon-"]').forEach(icon => {
                icon.className = 'bi bi-chevron-down';
            });

            // Faire défiler vers le premier avertissement
            warningElements[0].scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            // Notification avec compteur
            showNotification(`${warningElements.length} avertissement(s) trouvé(s)`, 'warning');

            // Mise à jour de l'état global
            allExpanded = true;
        }

    </script>


@endsection
