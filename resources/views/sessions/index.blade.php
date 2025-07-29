@extends('fontawesome-migrator::reports.layout')

@section('title', 'Sessions de Migration')

@section('head-extra')
    @include('fontawesome-migrator::reports.partials.css.common')
@endsection

@section('content')
    <div class="header">
        <h1>🗂️ Sessions de Migration</h1>
        <p>Gestion des sessions et métadonnées</p>
    </div>

    @if (count($sessions) > 0)
        <!-- Statistiques globales -->
        <div class="stats-summary">
            <h2 class="section-title">📈 Statistiques des sessions</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">{{ $stats['total_sessions'] }}</div>
                    <div class="stat-label">Sessions</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $stats['total_backups'] }}</div>
                    <div class="stat-label">Fichiers</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ number_format($stats['total_size'] / 1024, 1, ',', ' ') }}</div>
                    <div class="stat-label">KB Total</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">
                        @if($stats['last_session'])
                            {{ date('d/m', strtotime($stats['last_session']['created_at'])) }}
                        @else
                            -
                        @endif
                    </div>
                    <div class="stat-label">Dernière</div>
                </div>
            </div>
        </div>
    @endif

    <div class="actions">
        <a href="{{ route('fontawesome-migrator.reports.index') }}" class="btn btn-secondary">
            📊 Voir Rapports
        </a>
        
        <a href="{{ route('fontawesome-migrator.test.panel') }}" class="btn btn-secondary">
            🧪 Tests
        </a>
        
        <button onclick="refreshSessions()" class="btn btn-primary">
            <span id="refresh-icon">🔄</span> Actualiser
        </button>

        <button onclick="cleanupSessions()" class="btn btn-danger">
            🗑️ Nettoyer (30j+)
        </button>

        <div style="margin-left: auto; color: var(--gray-500); font-weight: 500;">
            🗂️ {{ count($sessions) }} session(s) disponible(s)
        </div>
    </div>

    <div id="alerts"></div>

    @if (count($sessions) > 0)
        <div class="reports-grid">
            @foreach ($sessions as $session)
                <div class="report-card" data-session="{{ $session['session_id'] }}">
                    <div class="report-header">
                        <div class="report-icon">🗂️</div>
                        <div class="report-title">
                            <h3>Session {{ $session['session_id'] }}</h3>
                            <div class="report-date">
                                🕒 {{ $session['created_at'] }}
                            </div>
                        </div>
                    </div>

                    <div class="report-meta">
                        <div class="meta-item">
                            <div class="meta-value">{{ $session['backup_count'] }}</div>
                            <div class="meta-label">📂 Fichiers</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-value">
                                @if(isset($session['dry_run']) && $session['dry_run'])
                                    🔍 Dry-run
                                @else
                                    ✅ Réel
                                @endif
                            </div>
                            <div class="meta-label">⚙️ Mode</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-value">{{ $session['package_version'] ?? '?' }}</div>
                            <div class="meta-label">📦 Version</div>
                        </div>
                    </div>

                    <div class="report-actions">
                        <a href="{{ route('fontawesome-migrator.sessions.show', $session['session_id']) }}" class="btn btn-primary btn-sm">
                            👁️ Détails
                        </a>

                        <button onclick="deleteSession('{{ $session['session_id'] }}')" class="btn btn-danger btn-sm">
                            🗑️ Supprimer
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">🗂️</div>
            <div class="empty-title">Aucune session disponible</div>
            <div class="empty-description">
                Les sessions sont créées automatiquement lors des migrations.
                Exécutez une migration pour voir les sessions apparaître ici.
            </div>
            <div class="empty-code">
                php artisan fontawesome:migrate --dry-run --report
            </div>
        </div>
    @endif
@endsection

@section('scripts')
<script>
    // Configuration CSRF pour les requêtes AJAX
    window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function showAlert(message, type = 'success') {
        const alertsContainer = document.getElementById('alerts');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;

        alertsContainer.appendChild(alert);

        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    function refreshSessions() {
        const icon = document.getElementById('refresh-icon');
        icon.innerHTML = '<span class="spinner"></span>';

        setTimeout(() => {
            window.location.reload();
        }, 500);
    }

    async function deleteSession(sessionId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette session ?')) {
            return;
        }

        try {
            const response = await fetch(`/fontawesome-migrator/sessions/${sessionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (response.ok) {
                showAlert('Session supprimée avec succès');
                const card = document.querySelector(`[data-session="${sessionId}"]`);
                if (card) {
                    card.style.opacity = '0.5';
                    card.style.pointerEvents = 'none';
                    setTimeout(() => card.remove(), 300);
                }
            } else {
                showAlert(data.error || 'Erreur lors de la suppression', 'error');
            }
        } catch (error) {
            showAlert('Erreur de connexion', 'error');
        }
    }

    async function cleanupSessions() {
        if (!confirm('Supprimer toutes les sessions de plus de 30 jours ?')) {
            return;
        }

        try {
            const response = await fetch('/fontawesome-migrator/sessions/cleanup', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ days: 30 })
            });

            const data = await response.json();

            if (response.ok) {
                showAlert(`${data.deleted} session(s) supprimée(s)`);
                if (data.deleted > 0) {
                    setTimeout(() => window.location.reload(), 1500);
                }
            } else {
                showAlert('Erreur lors du nettoyage', 'error');
            }
        } catch (error) {
            showAlert('Erreur de connexion', 'error');
        }
    }
</script>
@endsection