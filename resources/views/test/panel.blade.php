@extends('fontawesome-migrator::layout')

@section('title', 'Panneau de Tests - FontAwesome Migrator')

@section('head-extra')
    @include('fontawesome-migrator::partials.css.test-panel')
@endsection

@section('content')
<div class="container">
    <div class="header">
        <h1>🧪 Panneau de Tests</h1>
    </div>

    <!-- Statistiques des sauvegardes -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📁</div>
            <div class="stat-content">
                <div class="stat-value">{{ $backupStats['total_sessions'] }}</div>
                <div class="stat-label">Sessions</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💾</div>
            <div class="stat-content">
                <div class="stat-value">{{ $backupStats['total_backups'] }}</div>
                <div class="stat-label">Sauvegardes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <div class="stat-value">{{ number_format($backupStats['total_size'] / 1024, 1, ',', ' ') }} KB</div>
                <div class="stat-label">Taille totale</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏰</div>
            <div class="stat-content">
                <div class="stat-value">
                    @if($backupStats['last_session'])
                        {{ $backupStats['last_session']['created_at'] }}
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
        <h2>🚀 Tests de Migration</h2>
        <div class="test-buttons">
            <button onclick="runTest('dry-run')" class="btn btn-primary test-btn" data-type="dry-run">
                🔍 Test Dry-Run
            </button>
            <button onclick="runTest('icons-only')" class="btn btn-secondary test-btn" data-type="icons-only">
                🎯 Test Icônes Uniquement
            </button>
            <button onclick="runTest('assets-only')" class="btn btn-secondary test-btn" data-type="assets-only">
                🎨 Test Assets Uniquement
            </button>
            <button onclick="runTest('real')" class="btn btn-danger test-btn" data-type="real">
                ⚡ Test Réel (Attention!)
            </button>
        </div>

        <div id="test-output" class="test-output" style="display: none;">
            <h3>Résultat du test :</h3>
            <pre id="test-result"></pre>
        </div>
    </div>

    <!-- Sessions disponibles -->
    <div class="section">
        <h2>📋 Sessions Disponibles</h2>
        @if(count($sessions) > 0)
            <div class="sessions-grid">
                @foreach($sessions as $session)
                    <div class="session-card" data-session-id="{{ $session['session_id'] }}">
                        <div class="session-header">
                            <h3>Session {{ substr($session['session_id'], -8) }}</h3>
                            <div class="session-badges">
                                @if($session['dry_run'])
                                    <span class="badge badge-info">DRY-RUN</span>
                                @endif
                                <span class="badge badge-secondary">{{ $session['package_version'] ?? 'unknown' }}</span>
                            </div>
                        </div>
                        <div class="session-details">
                            <div class="session-stat">
                                <strong>📅 Créée :</strong> {{ $session['created_at'] }}
                            </div>
                            <div class="session-stat">
                                <strong>💾 Sauvegardes :</strong> {{ $session['backup_count'] }}
                            </div>
                            @if($session['duration'])
                                <div class="session-stat">
                                    <strong>⏱️ Durée :</strong> {{ $session['duration'] }}s
                                </div>
                            @endif
                        </div>
                        <div class="session-actions">
                            <button class="btn btn-sm btn-primary inspect-session-btn" data-session-id="{{ $session['session_id'] }}">
                                🔍 Inspecter
                            </button>
                            @if ($session['has_metadata'])
                                <span class="badge badge-success">✓ Métadonnées</span>
                            @else
                                <span class="badge badge-error">✗ Métadonnées</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>Aucune session trouvée</h3>
                <p>Lancez un test de migration pour créer votre première session.</p>
            </div>
        @endif
    </div>

    <!-- Modal d'inspection des sessions -->
    <div id="session-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🔍 Inspection de Session</h3>
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
        <h2>🧹 Nettoyage</h2>
        <div class="cleanup-buttons">
            <button onclick="cleanupSessions(7)" class="btn btn-secondary">
                🗑️ Nettoyer sessions > 7 jours
            </button>
            <button onclick="cleanupSessions(1)" class="btn btn-danger">
                🗑️ Nettoyer sessions > 1 jour
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @include('fontawesome-migrator::partials.js.test-panel')
@endsection