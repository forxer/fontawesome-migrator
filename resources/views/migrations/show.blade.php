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

    <!-- Navigation rapide améliorée -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="card-title section-title mb-0">
                    <i class="bi bi-compass text-primary me-2"></i>
                    Navigation rapide
                </h2>
                <small class="text-body-secondary">{{ collect([
                    ['#statistics', 'Statistiques'],
                    $stats['total_changes'] > 0 ? ['#recommendations-section', 'Recommandations'] : null,
                    ['#configuration-section', 'Configuration'],
                    ['#environment-section', 'Environnement'],
                    $stats['total_changes'] > 0 ? ['#details-section', 'Détails'] : null
                ])->filter()->count() }} sections</small>
            </div>

            <div class="row g-2">
                <!-- Statistiques -->
                <div class="col-md-6 col-lg-4">
                    <a href="#statistics" class="text-decoration-none">
                        <div class="p-3 border rounded hover-bg-light">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-graph-up text-primary fs-5 me-3"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">Statistiques</div>
                                    <small class="text-body-secondary">{{ number_formatted($stats['total_files']) }} fichiers</small>
                                </div>
                                @if ($stats['total_changes'] > 0)
                                    <span class="badge bg-success">{{ number_formatted($stats['total_changes']) }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>

                @if ($stats['total_changes'] > 0)
                <!-- Recommandations -->
                <div class="col-md-6 col-lg-4">
                    <a href="#recommendations-section" class="text-decoration-none">
                        <div class="p-3 border rounded hover-bg-light">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-lightbulb text-primary fs-5 me-3"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">Recommandations</div>
                                    <small class="text-body-secondary">Actions suggérées</small>
                                </div>
                                @if (($stats['warnings'] ?? 0) > 0)
                                    <span class="badge bg-warning">{{ $stats['warnings'] }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                </div>
                @endif

                <!-- Configuration -->
                <div class="col-md-6 col-lg-4">
                    <a href="#configuration-section" class="text-decoration-none">
                        <div class="p-3 border rounded hover-bg-light">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-gear text-primary fs-5 me-3"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">Configuration</div>
                                    <small class="text-body-secondary">Paramètres utilisés</small>
                                </div>
                                <span class="badge bg-secondary">{{ ucfirst($configuration['license_type'] ?? 'free') }}</span>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Environnement -->
                <div class="col-md-6 col-lg-4">
                    <a href="#environment-section" class="text-decoration-none">
                        <div class="p-3 border rounded hover-bg-light">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-server text-primary fs-5 me-3"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">Environnement</div>
                                    <small class="text-body-secondary">Contexte d'exécution</small>
                                </div>
                                <span class="badge {{ $migrationSource === 'web_interface' ? 'bg-info' : 'bg-primary' }}">
                                    {{ $migrationSource === 'web_interface' ? 'Web' : 'CLI' }}
                                </span>
                            </div>
                        </div>
                    </a>
                </div>

                @if ($backupsCount > 0)
                    <!-- Sauvegardes -->
                    <div class="col-md-6 col-lg-4">
                        <a href="#backups-section" class="text-decoration-none">
                            <div class="p-3 border rounded hover-bg-light">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-hdd text-primary fs-5 me-3"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold">Sauvegardes</div>
                                        <small class="text-body-secondary">Fichier(s) sauvegardé(s)</small>
                                    </div>
                                    <span class="badge bg-warning">{{ $backupsCount }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif

                <!-- Détails -->
                <div class="col-md-6 col-lg-4">
                    <a href="#details-section" class="text-decoration-none">
                        <div class="p-3 border rounded hover-bg-light">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-code-slash text-primary fs-5 me-3"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">Détails</div>
                                    <small class="text-body-secondary">Modifications par fichier</small>
                                </div>
                                <span class="badge bg-primary">{{ number_formatted($stats['modified_files']) }}</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
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
                            <div class="small text-body-secondary mt-2">
                                Prévisualisation uniquement
                            </div>
                        @else
                            <span class="text-success fw-bold">
                                <i class="bi bi-lightning-fill"></i> MIGRATION RÉELLE
                            </span>
                            <div class="small text-body-secondary mt-2">
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
                    <h2 class="card-title display-6 mb-3">{{ number_formatted($stats['total_files']) }}</h2>
                    <h5 class="card-subtitle text-body-secondary">Fichiers analysés</h5>
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
                    <h2 class="card-title display-6 mb-3">{{ number_formatted($stats['modified_files']) }}</h2>
                    <h5 class="card-subtitle text-body-secondary">Fichiers modifiés</h5>
                    @if ($stats['modified_files'] > 0)
                        <div class="mt-3 text-success small">
                            <i class="bi bi-pencil-square"></i>
                            {{ number_formatted($stats['modified_files']) }} fichier(s) optimisé(s)
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h2 class="card-title display-6 mb-3">{{ number_formatted($stats['total_changes']) }}</h2>
                    <h5 class="card-subtitle text-body-secondary">Total des changements</h5>
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
                    <h2 class="card-title display-6 mb-3">{{ number_formatted($stats['icons_migrated']) }}</h2>
                    <h5 class="card-subtitle text-body-secondary">Icônes migrées</h5>
                    @if (($stats['icons_migrated'] ?? 0) > 0)
                        <div class="mt-3 text-primary small">
                            <i class="bi bi-arrow-right"></i> FA{{ $migrationOptions['source_version'] }} → FA{{ $migrationOptions['target_version'] }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if (($stats['assets_migrated'] ?? 0) > 0)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h2 class="card-title display-6 mb-3">{{ number_formatted($stats['assets_migrated']) }}</h2>
                        <h5 class="card-subtitle text-body-secondary">Assets migrés</h5>
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
                        <h2 class="card-title display-6 mb-3 text-warning">{{ number_formatted($stats['warnings']) }}</h2>
                        <h5 class="card-subtitle text-body-secondary">Avertissements</h5>
                        <div class="mt-3 text-warning small">
                            <i class="bi bi-exclamation-triangle"></i> Icônes renommées/dépréciées
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Recommandations intelligentes -->
    @if ($stats['total_changes'] > 0)
    <div id="recommendations-section">
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
                                <p class="card-text">Exécutez <code>php artisan fontawesome:migrate</code> pour appliquer ces {{ number_formatted($stats['total_changes']) }} changements.</p>
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
                                <p class="card-text">{{ number_formatted($stats['warnings']) }} icône(s) renommée(s), dépréciée(s) ou Pro détectée(s). Vérifiez le rendu.</p>
                                <button class="btn btn-warning btn-sm" onclick="showWarningsModal()"><i class="bi bi-exclamation-triangle"></i> Voir les avertissements</button>
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
                                <p class="card-text">{{ number_formatted($migrationScore, 1) }} % de votre code a été optimisé pour Font Awesome 6 !</p>
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
                                <p class="card-text">{{ number_formatted($migrationScore, 1) }} % de votre code utilise maintenant Font Awesome 6.</p>
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
                            <tr>
                                <td><strong>Origine</strong></td>
                                <td>
                                    @if ($migrationSource === 'web_interface')
                                        <i class="bi bi-globe text-info"></i> Interface Web
                                    @else
                                        <i class="bi bi-terminal text-primary"></i> Ligne de commande
                                    @endif
                                </td>
                            </tr>
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
                        @if ($backupsCount > 0)
                        <tr><td><strong>Sauvegardes créées</strong></td><td>
                            <span class="text-success fw-bold">
                                {{ number_formatted($backupsCount) }} fichier(s) sauvegardé(s)
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

        <!-- Informations d'environnement et d'origine -->
        <div id="environment-section" class="row g-4 mt-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title section-title-sm mb-3"><i class="bi bi-info-circle text-primary"></i> Informations d'environnement</h3>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center p-3 border rounded">
                                    @if($migrationSource === 'web_interface')
                                        <i class="bi bi-globe text-info fs-4 me-3"></i>
                                        <div>
                                            <strong>Interface Web</strong>
                                            <small class="d-block text-body-secondary">Migration lancée depuis l'interface web</small>
                                        </div>
                                    @else
                                        <i class="bi bi-terminal text-primary fs-4 me-3"></i>
                                        <div>
                                            <strong>Ligne de Commande</strong>
                                            <small class="d-block text-body-secondary">Migration lancée via CLI</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <label class="small text-body-secondary fw-bold">User Agent</label>
                                    <div class="small">{{ $userAgent }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded">
                                    <label class="small text-body-secondary fw-bold">Adresse IP</label>
                                    <div class="small">{{ $ipAddress }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                    <div>
                                        <span><i class="bi bi-file-code text-primary"></i> {{ $result['file'] }}</span>
                                        @if(isset($result['backup']) && $result['backup'] !== null)
                                            <small class="text-success ms-2">
                                                <i class="bi bi-shield-check"></i>
                                            </small>
                                        @endif
                                    </div>
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
                                                                <div class="text-body-secondary small mt-1">
                                                                    <i class="bi bi-gem"></i> <em>Considérez une licence Pro pour conserver le style original</em>
                                                                </div>
                                                                @break
                                                            @case('renamed_icon')
                                                                <div class="text-body-secondary small mt-1">
                                                                    <i class="bi bi-check-square"></i> <em>Renommage automatique appliqué</em>
                                                                </div>
                                                                @break
                                                            @case('deprecated_icon')
                                                                <div class="text-body-secondary small mt-1">
                                                                    <i class="bi bi-eye"></i> <em>Vérifiez le rendu et remplacez manuellement si nécessaire</em>
                                                                </div>
                                                                @break
                                                            @case('manual_review')
                                                                <div class="text-body-secondary small mt-1">
                                                                    <i class="bi bi-eye"></i> <em>Révision manuelle recommandée</em>
                                                                </div>
                                                                @break
                                                        @endswitch
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="text-end text-body-secondary small">
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

                                @if(isset($result['backup']) && $result['backup'] !== null)
                                    <div class="border-top pt-3 mt-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-shield-check text-success me-2"></i>
                                                <span class="fw-semibold">Fichier sauvegardé</span>
                                            </div>
                                            <div class="d-flex align-items-center text-body-secondary">
                                                <small class="me-3">
                                                    <i class="bi bi-calendar3 me-1"></i>
                                                    {{ \Carbon\Carbon::parse($result['backup']['created_at'])->format('d/m/Y à H:i:s') }}
                                                </small>
                                                <small>
                                                    <i class="bi bi-file-earmark me-1"></i>
                                                    {{ human_readable_bytes_size($result['backup']['size']) }}
                                                </small>
                                            </div>
                                        </div>
                                        <small class="text-body-secondary d-block mt-2">
                                            <i class="bi bi-folder me-1"></i>
                                            {{ $result['backup']['backup_path'] }}
                                        </small>
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

        <div id="noResults" class="text-center py-5 text-body-secondary" style="display: none;">
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
                <div class="modal fade" id="testingTipsModal" tabindex="-1" style="z-index: 9999;">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-flask"></i> Conseils de test</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

            // Supprimer backdrop existant si présent
            const existingBackdrop = document.querySelector('.modal-backdrop');
            if (existingBackdrop) existingBackdrop.remove();

            // Ajouter la nouvelle modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Créer et afficher la modal avec le backdrop
            const modalElement = document.getElementById('testingTipsModal');
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
            modal.show();

            // Nettoyer après fermeture
            modalElement.addEventListener('hidden.bs.modal', function() {
                this.remove();
                // Supprimer aussi le backdrop au cas où
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            });
        }

        // Fonctions de surlignage optimisées
        function highlightText(element, searchTerm) {
            const textElements = element.querySelectorAll('.text-danger, .text-success');
            textElements.forEach(el => {
                const originalText = el.textContent;
                const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                el.innerHTML = originalText.replace(regex, '<mark class="p-0">$1</mark>');
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

        // Fonction pour afficher les avertissements dans une modal
        function showWarningsModal() {
            // Collecter tous les avertissements depuis PHP
            const warnings = [];

            // Parcourir tous les résultats avec des changements
            @foreach($results as $result)
                @if(!empty($result['changes']))
                    @foreach($result['changes'] as $change)
                        @php
                            $hasWarning = in_array($change['type'] ?? '', ['pro_fallback', 'renamed_icon', 'deprecated_icon', 'manual_review']);
                            $warningMessage = null;

                            if ($hasWarning && !empty($result['warnings'])) {
                                foreach ($result['warnings'] as $warning) {
                                    if (str_contains($warning, $change['from'] ?? '')) {
                                        $warningMessage = $warning;
                                        break;
                                    }
                                }
                            }
                        @endphp

                        @if($hasWarning)
                            warnings.push({
                                file: '{{ addslashes($result['file']) }}',
                                line: {{ $change['line'] ?? 0 }},
                                from: '{{ addslashes($change['from']) }}',
                                to: '{{ addslashes($change['to']) }}',
                                type: '{{ $change['type'] ?? 'unknown' }}',
                                message: '{{ addslashes($warningMessage ?? '') }}'
                            });
                        @endif
                    @endforeach
                @endif
            @endforeach

            if (warnings.length === 0) {
                showNotification('Aucun avertissement trouvé', 'info');
                return;
            }

            // Grouper les avertissements par type
            const warningsByType = {
                'pro_fallback': [],
                'renamed_icon': [],
                'deprecated_icon': [],
                'manual_review': [],
                'other': []
            };

            warnings.forEach(warning => {
                const type = warningsByType[warning.type] ? warning.type : 'other';
                warningsByType[type].push(warning);
            });

            // Créer le contenu HTML de la modal
            let warningsHtml = '';

            // Pro fallback warnings
            if (warningsByType.pro_fallback.length > 0) {
                warningsHtml += `
                    <div class="mb-4">
                        <h6 class="text-warning mb-3">
                            <i class="bi bi-gem"></i> Icônes Pro (${warningsByType.pro_fallback.length})
                        </h6>
                        <div class="list-group">`;
                warningsByType.pro_fallback.forEach(w => {
                    warningsHtml += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-bold small text-body-secondary">${w.file}:${w.line}</div>
                                    <div class="font-monospace small">
                                        <span class="text-danger">- ${w.from}</span> →
                                        <span class="text-success">+ ${w.to}</span>
                                    </div>
                                    <div class="text-body-secondary small mt-1">
                                        <i class="bi bi-info-circle"></i> ${w.message || 'Icône Pro remplacée par une alternative gratuite'}
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
                warningsHtml += '</div></div>';
            }

            // Renamed icons
            if (warningsByType.renamed_icon.length > 0) {
                warningsHtml += `
                    <div class="mb-4">
                        <h6 class="text-warning mb-3">
                            <i class="bi bi-arrow-left-right"></i> Icônes renommées (${warningsByType.renamed_icon.length})
                        </h6>
                        <div class="list-group">`;
                warningsByType.renamed_icon.forEach(w => {
                    warningsHtml += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-bold small text-body-secondary">${w.file}:${w.line}</div>
                                    <div class="font-monospace small">
                                        <span class="text-danger">- ${w.from}</span> →
                                        <span class="text-success">+ ${w.to}</span>
                                    </div>
                                    <div class="text-body-secondary small mt-1">
                                        <i class="bi bi-check-circle"></i> ${w.message || 'Nom d\'icône mis à jour automatiquement'}
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
                warningsHtml += '</div></div>';
            }

            // Deprecated icons
            if (warningsByType.deprecated_icon.length > 0) {
                warningsHtml += `
                    <div class="mb-4">
                        <h6 class="text-warning mb-3">
                            <i class="bi bi-exclamation-triangle"></i> Icônes dépréciées (${warningsByType.deprecated_icon.length})
                        </h6>
                        <div class="list-group">`;
                warningsByType.deprecated_icon.forEach(w => {
                    warningsHtml += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-bold small text-body-secondary">${w.file}:${w.line}</div>
                                    <div class="font-monospace small">
                                        <span class="text-danger">- ${w.from}</span> →
                                        <span class="text-success">+ ${w.to}</span>
                                    </div>
                                    <div class="text-body-secondary small mt-1">
                                        <i class="bi bi-eye"></i> ${w.message || 'Vérifiez le rendu de cette icône'}
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
                warningsHtml += '</div></div>';
            }

            // Manual review
            if (warningsByType.manual_review.length > 0) {
                warningsHtml += `
                    <div class="mb-4">
                        <h6 class="text-warning mb-3">
                            <i class="bi bi-hand-index"></i> Révision manuelle requise (${warningsByType.manual_review.length})
                        </h6>
                        <div class="list-group">`;
                warningsByType.manual_review.forEach(w => {
                    warningsHtml += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-bold small text-body-secondary">${w.file}:${w.line}</div>
                                    <div class="font-monospace small">
                                        <span class="text-danger">- ${w.from}</span> →
                                        <span class="text-success">+ ${w.to}</span>
                                    </div>
                                    <div class="text-body-secondary small mt-1">
                                        <i class="bi bi-pencil"></i> ${w.message || 'Vérification manuelle recommandée'}
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
                warningsHtml += '</div></div>';
            }

            const modalHtml = `
                <div class="modal fade" id="warningsModal" tabindex="-1" style="z-index: 9999;">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-warning bg-opacity-10">
                                <h5 class="modal-title">
                                    <i class="bi bi-exclamation-triangle text-warning"></i>
                                    Avertissements de migration (${warnings.length})
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                ${warningsHtml}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Supprimer modal existante si présente
            const existing = document.getElementById('warningsModal');
            if (existing) existing.remove();

            // Supprimer backdrop existant si présent
            const existingBackdrop = document.querySelector('.modal-backdrop');
            if (existingBackdrop) existingBackdrop.remove();

            // Ajouter la nouvelle modal
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Créer et afficher la modal avec le backdrop
            const modalElement = document.getElementById('warningsModal');
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
            modal.show();

            // Nettoyer après fermeture
            modalElement.addEventListener('hidden.bs.modal', function() {
                this.remove();
                // Supprimer aussi le backdrop au cas où
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            });
        }

    </script>


@endsection
