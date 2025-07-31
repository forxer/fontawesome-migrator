@extends('fontawesome-migrator::layout')

@section('title', 'Tests - FontAwesome Migrator')

@section('head-extra')
    @include('fontawesome-migrator::partials.css.tests')
@endsection

@section('content')
<div class="container">
    <div class="header">
        <h1><i class="fa-solid fa-flask"></i> Tests</h1>
        <p>Utilitaires de tests en ligne</p>
    </div>

    <!-- Statistiques des sauvegardes -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-regular fa-folder"></i></div>
            <div class="stat-content">
                <div class="stat-value">{{ $backupStats['total_sessions'] }}</div>
                <div class="stat-label">Sessions</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-regular fa-floppy-disk"></i></div>
            <div class="stat-content">
                <div class="stat-value">{{ $backupStats['total_backups'] }}</div>
                <div class="stat-label">Sauvegardes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-regular fa-chart-bar"></i></div>
            <div class="stat-content">
                <div class="stat-value">{{ human_readable_bytes_size($backupStats['total_size'], 2) }}</div>
                <div class="stat-label">Taille totale</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-regular fa-clock"></i></div>
            <div class="stat-content">
                <div class="stat-value">
                    @if($backupStats['last_session'])
                        {{ $backupStats['last_session']['created_at']->format('d/m/Y à H:i') }}
                    @else
                        Aucune
                    @endif
                </div>
                <div class="stat-label">Dernière session</div>
            </div>
        </div>
    </div>

    <!-- Boutons de test -->
    <div class="section">
        <h2 class="section-title"><i class="fa-solid fa-rocket"></i> Tests de Migration</h2>
        <div class="test-buttons">
            <button onclick="runTest('dry-run')" class="btn btn-primary test-btn" data-type="dry-run">
                <i class="fa-solid fa-bullseye"></i> Test Dry-Run
            </button>
            <button onclick="runTest('icons-only')" class="btn btn-secondary test-btn" data-type="icons-only">
                <i class="fa-solid fa-bullseye"></i> Test Icônes Uniquement
            </button>
            <button onclick="runTest('assets-only')" class="btn btn-secondary test-btn" data-type="assets-only">
                <i class="fa-solid fa-palette"></i> Test Assets Uniquement
            </button>
            <button onclick="runTest('real')" class="btn btn-danger test-btn" data-type="real">
                <i class="fa-solid fa-bolt"></i> Test Réel (Attention!)
            </button>
        </div>

        <div id="test-output" class="test-output" style="display: none;">
            <h3 class="section-title">Résultat du test :</h3>
            <pre id="test-result"></pre>
        </div>
    </div>

    <!-- Sessions disponibles -->
    <div class="section">
        <h2 class="section-title"><i class="fa-solid fa-chart-line"></i> Sessions Disponibles</h2>
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
                                <strong><i class="fa-regular fa-clock"></i> Créée :</strong> {{ $session['created_at']->format('d/m/Y à H:i') }}
                            </div>
                            <div class="session-stat">
                                <strong><i class="fa-regular fa-floppy-disk"></i> Sauvegardes :</strong> {{ $session['backup_count'] }}
                            </div>
                            @if($session['duration'])
                                <div class="session-stat">
                                    <strong><i class="fa-regular fa-clock"></i> Durée :</strong> {{ $session['duration'] }}s
                                </div>
                            @endif
                        </div>
                        <div class="session-actions">
                            <button class="btn btn-sm btn-primary inspect-session-btn" data-session-id="{{ $session['session_id'] }}">
                                <i class="fa-solid fa-bullseye"></i> Inspecter
                            </button>
                            @if ($session['has_metadata'])
                                <span class="badge badge-success"><i class="fa-regular fa-square-check"></i> Métadonnées</span>
                            @else
                                <span class="badge badge-error"><i class="fa-regular fa-square-xmark"></i> Métadonnées</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon"><i class="fa-regular fa-folder"></i></div>
                <h3 class="section-title">Aucune session trouvée</h3>
                <p>Lancez un test de migration pour créer votre première session.</p>
            </div>
        @endif
    </div>

    <!-- Modal d'inspection des sessions -->
    <div id="session-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="section-title"><i class="fa-solid fa-bullseye"></i> Inspection de Session</h3>
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
    <div class="section">
        <h2 class="section-title"><i class="fa-regular fa-trash-can"></i> Nettoyage</h2>
        <div class="cleanup-buttons">
            <button onclick="cleanupSessions(7)" class="btn btn-secondary">
                <i class="fa-regular fa-trash-can"></i> Nettoyer sessions > 7 jours
            </button>
            <button onclick="cleanupSessions(1)" class="btn btn-danger">
                <i class="fa-regular fa-trash-can"></i> Nettoyer sessions > 1 jour
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('fontawesome-migrator::partials.js.tests')
@endsection