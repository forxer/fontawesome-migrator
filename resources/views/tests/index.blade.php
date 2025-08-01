@extends('fontawesome-migrator::layout')

@section('title', 'Tests - FontAwesome Migrator')

@section('head-extra')
    @include('fontawesome-migrator::partials.css.bootstrap-common')
    @include('fontawesome-migrator::partials.css.tests')
@endsection

@section('content')
<div class="container">
    <div class="mb-4">
        <h1 class="display-5 d-flex align-items-center gap-2">
            <i class="bi bi-flask"></i> Tests
        </h1>
        <p class="text-muted">Utilitaires de tests en ligne</p>
    </div>

    <!-- Statistiques des sauvegardes -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-folder fs-1 text-primary mb-2"></i>
                    <h3 class="card-title">{{ $backupStats['total_sessions'] }}</h3>
                    <p class="card-text text-muted">Sessions</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-hdd fs-1 text-primary mb-2"></i>
                    <h3 class="card-title">{{ $backupStats['total_backups'] }}</h3>
                    <p class="card-text text-muted">Sauvegardes</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-file-text fs-1 text-primary mb-2"></i>
                    <h3 class="card-title">{{ human_readable_bytes_size($backupStats['total_size'], 2) }}</h3>
                    <p class="card-text text-muted">Taille totale</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card text-center">
                <div class="card-body">
                    <i class="bi bi-clock fs-1 text-primary mb-2"></i>
                    <h3 class="card-title">
                        @if($backupStats['last_session'])
                            {{ $backupStats['last_session']['created_at']->format('d/m/Y à H:i') }}
                        @else
                            Aucune
                        @endif
                    </h3>
                    <p class="card-text text-muted">Dernière session</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Boutons de test -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title section-title"><i class="bi bi-rocket"></i> Tests de Migration</h2>
            <div class="btn-group btn-group-lg flex-wrap justify-content-center" role="group" aria-label="Tests de migration">
                <button onclick="runTest('dry-run')" class="btn btn-primary test-btn" data-type="dry-run">
                    <i class="bi bi-bullseye"></i> Test Dry-Run
                </button>
                <button onclick="runTest('icons-only')" class="btn btn-secondary test-btn" data-type="icons-only">
                    <i class="bi bi-bullseye"></i> Test Icônes Uniquement
                </button>
                <button onclick="runTest('assets-only')" class="btn btn-secondary test-btn" data-type="assets-only">
                    <i class="bi bi-palette"></i> Test Assets Uniquement
                </button>
                <button onclick="runTest('real')" class="btn btn-danger test-btn" data-type="real">
                    <i class="bi bi-lightning-fill"></i> Test Réel (Attention!)
                </button>
        </div>

            </div>
            <div id="test-output" class="test-output mt-4" style="display: none;">
                <h3 class="section-title">Résultat du test :</h3>
                <pre id="test-result" class="bg-dark text-light p-3 rounded"></pre>
            </div>
        </div>
    </div>

    <!-- Sessions disponibles -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title section-title"><i class="bi bi-graph-up"></i> Sessions Disponibles</h2>
        @if(count($sessions) > 0)
            <div class="sessions-grid">
                @foreach($sessions as $session)
                    <div class="session-card" data-session-id="{{ $session['session_id'] }}">
                        <div class="session-header">
                            <h3 class="section-title">Session {{ substr($session['session_id'], -8) }}</h3>
                            <div class="session-badges">
                                @if($session['dry_run'])
                                    <span class="badge badge-warning">DRY-RUN</span>
                                @else
                                    <span class="badge badge-success">RÉEL</span>
                                @endif
                                <span class="badge badge-secondary">{{ $session['package_version'] ?? 'unknown' }}</span>
                            </div>
                        </div>
                        <div class="session-details">
                            <div class="session-stat">
                                <strong><i class="bi bi-clock"></i> Créée :</strong> {{ $session['created_at']->format('d/m/Y à H:i') }}
                            </div>
                            <div class="session-stat">
                                <strong><i class="bi bi-hdd"></i> Sauvegardes :</strong> {{ $session['backup_count'] }}
                            </div>
                            @if($session['duration'])
                                <div class="session-stat">
                                    <strong><i class="bi bi-clock"></i> Durée :</strong> {{ $session['duration'] }}s
                                </div>
                            @endif
                        </div>
                        <div class="session-actions">
                            <button class="btn btn-sm btn-primary inspect-session-btn" data-session-id="{{ $session['session_id'] }}">
                                <i class="bi bi-search"></i> Inspecter
                            </button>
                            @if ($session['has_metadata'])
                                <span class="badge bg-success"><i class="bi bi-check-square"></i> Métadonnées</span>
                            @else
                                <span class="badge bg-danger"><i class="bi bi-x-square"></i> Métadonnées</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <div class="mb-3">
                    <i class="bi bi-folder display-1 text-muted"></i>
                </div>
                <h3 class="text-muted mb-2">Aucune session trouvée</h3>
                <p class="text-muted">Lancez un test de migration pour créer votre première session.</p>
            </div>
        @endif
        </div>
    </div>

    <!-- Modal d'inspection des sessions -->
    <div id="session-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="section-title"><i class="bi bi-search"></i> Inspection de Session</h3>
                <button onclick="closeModal('session-modal')" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="session-details">
                    <!-- Contenu chargé via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Actions de nettoyage -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title section-title"><i class="bi bi-trash"></i> Nettoyage</h2>
            <div class="btn-group" role="group" aria-label="Nettoyage des sessions">
                <button onclick="cleanupSessions(7)" class="btn btn-secondary">
                    <i class="bi bi-trash"></i> Nettoyer sessions > 7 jours
                </button>
                <button onclick="cleanupSessions(1)" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Nettoyer sessions > 1 jour
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('fontawesome-migrator::partials.js.tests')
@endsection