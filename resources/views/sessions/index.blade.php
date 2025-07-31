@extends('fontawesome-migrator::layout')

@section('title', 'Sessions de Migration')

@section('content')
    <div class="header">
        <h1><i class="fa-regular fa-folder"></i> Sessions</h1>
        <p>Gestion des sessions et métadonnées</p>
    </div>

    @if (count($sessions) > 0)
        <!-- Statistiques globales -->
        <div class="stats-summary">
            <h2 class="section-title"><i class="fa-regular fa-chart-bar"></i> Statistiques des sessions</h2>
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
                    <div class="stat-number">{{ human_readable_bytes_size($stats['total_size'], 2) }}</div>
                    <div class="stat-label">Taille totale</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">
                        @if($stats['last_session'])
                            {{ $stats['last_session']['created_at']->format('d/m') }}
                        @else
                            -
                        @endif
                    </div>
                    <div class="stat-label">Dernière session</div>
                </div>
            </div>
        </div>
    @endif

    <div class="actions">
        <button onclick="refreshSessions()" class="btn btn-primary">
            <span id="refresh-icon"><i class="fa-solid fa-arrows-rotate"></i></span> Actualiser
        </button>

        <button onclick="cleanupSessions()" class="btn btn-danger">
            <i class="fa-regular fa-trash-can"></i> Nettoyer (30j+)
        </button>

        <div style="margin-left: auto; color: var(--gray-500); font-weight: 500;">
            <i class="fa-regular fa-folder"></i> {{ count($sessions) }} session(s) disponible(s)
        </div>
    </div>

    <div id="alerts"></div>

    @if (count($sessions) > 0)
        <div class="reports-grid">
            @foreach ($sessions as $session)
                <div class="report-card" data-session="{{ $session['session_id'] }}">
                    <div class="report-header">
                        <div class="report-icon"><i class="fa-regular fa-folder"></i></div>
                        <div class="report-title">
                            <h3 class="section-title">Session <span data-tooltip="ID complet : {{ $session['session_id'] }}">{{ $session['short_id'] }}</span></h3>
                            <div class="report-date">
                                <i class="fa-regular fa-clock"></i> {{ $session['created_at']->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                    </div>

                    <div class="report-meta">
                        <div class="meta-item">
                            <div class="meta-value">{{ $session['backup_count'] }}</div>
                            <div class="meta-label"><i class="fa-regular fa-folder"></i> Fichiers</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-value">
                                @if(isset($session['dry_run']) && $session['dry_run'])
                                    <i class="fa-regular fa-eye"></i> Dry-run
                                @else
                                    <i class="fa-regular fa-square-check"></i> Réel
                                @endif
                            </div>
                            <div class="meta-label"><i class="fa-solid fa-gear"></i> Mode</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-value">{{ $session['package_version'] ?? '?' }}</div>
                            <div class="meta-label"><i class="fa-regular fa-chart-bar"></i> Version</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-value">{{ $session['created_at']->diffForHumans(['short' => true]) }}</div>
                            <div class="meta-label"><i class="fa-regular fa-clock"></i> Âge</div>
                        </div>
                    </div>

                    <div class="report-actions">
                        <a href="{{ route('fontawesome-migrator.sessions.show', $session['session_id']) }}" class="btn btn-primary btn-sm">
                            <i class="fa-regular fa-eye"></i> Détails
                        </a>

                        <button onclick="deleteSession('{{ $session['session_id'] }}')" class="btn btn-danger btn-sm">
                            <i class="fa-regular fa-trash-can"></i> Supprimer
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon"><i class="fa-regular fa-folder"></i></div>
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