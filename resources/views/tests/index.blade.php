@extends('fontawesome-migrator::layout')

@section('title', 'Tests - FontAwesome Migrator')

@section('head-extra')
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
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="card-title section-title"><i class="bi bi-bar-chart"></i> Statistiques globales</h2>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="text-center">
                        <i class="bi bi-folder fs-1 text-primary mb-2"></i>
                        <div class="display-6 fw-bold text-primary">{{ $backupStats['total_sessions'] }}</div>
                        <div class="text-muted small">Sessions</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center">
                        <i class="bi bi-hdd fs-1 text-primary mb-2"></i>
                        <div class="display-6 fw-bold text-primary">{{ $backupStats['total_backups'] }}</div>
                        <div class="text-muted small">Sauvegardes</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center">
                        <i class="bi bi-file-text fs-1 text-primary mb-2"></i>
                        <div class="display-6 fw-bold text-primary">{{ human_readable_bytes_size($backupStats['total_size'], 2) }}</div>
                        <div class="text-muted small">Taille totale</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="text-center">
                        <i class="bi bi-clock fs-1 text-primary mb-2"></i>
                        <div class="display-6 fw-bold text-primary">
                            @if($backupStats['last_session'])
                                {{ $backupStats['last_session']['created_at']->format('d/m') }}
                            @else
                                -
                            @endif
                        </div>
                        <div class="text-muted small">Dernière session</div>
                    </div>
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
    <h2 class="section-title"><i class="bi bi-graph-up"></i> Sessions Disponibles</h2>
    <div class="mb-4">
        @if(count($sessions) > 0)
            <div class="row g-4">
                @foreach($sessions as $session)
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 entity-card" data-session-id="{{ $session['session_id'] }}">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <i class="bi bi-folder fs-2 text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1">Session <span data-bs-toggle="tooltip" title="ID complet : {{ $session['session_id'] }}">{{ substr($session['session_id'], -8) }}</span></h5>
                                    <div class="text-muted small">
                                        <i class="bi bi-clock"></i> {{ $session['created_at']->format('d/m/Y à H:i') }}
                                        @if($session['dry_run'])
                                            <span class="badge bg-warning text-dark ms-2">DRY-RUN</span>
                                        @else
                                            <span class="badge bg-success ms-2">RÉEL</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row text-center g-2 mb-3">
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="fw-bold text-primary">{{ $session['backup_count'] }}</div>
                                        <div class="small text-muted"><i class="bi bi-hdd"></i> Sauvegardes</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="fw-bold text-primary">{{ $session['package_version'] ?? '?' }}</div>
                                        <div class="small text-muted"><i class="bi bi-file-text"></i> Version</div>
                                    </div>
                                </div>
                                @if($session['duration'])
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="fw-bold text-primary">{{ $session['duration'] }}s</div>
                                        <div class="small text-muted"><i class="bi bi-clock"></i> Durée</div>
                                    </div>
                                </div>
                                @endif
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        @if ($session['has_metadata'])
                                            <div class="fw-bold text-success"><i class="bi bi-check-square"></i></div>
                                            <div class="small text-muted">Métadonnées</div>
                                        @else
                                            <div class="fw-bold text-danger"><i class="bi bi-x-square"></i></div>
                                            <div class="small text-muted">Métadonnées</div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="btn-group-vertical btn-group-sm d-grid" role="group" aria-label="Actions de la session">
                                <button class="btn btn-primary inspect-session-btn" data-session-id="{{ $session['session_id'] }}">
                                    <i class="bi bi-search"></i> Inspecter
                                </button>
                            </div>
                        </div>
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