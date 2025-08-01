@extends('fontawesome-migrator::layout')

@section('title', 'Sessions de Migration')

@section('head-extra')
    @include('fontawesome-migrator::partials.css.bootstrap-common')
@endsection

@section('content')
    <div class="mb-4">
        <h1 class="display-5 d-flex align-items-center gap-2">
            <i class="bi bi-folder"></i> Sessions
        </h1>
        <p class="text-muted">Gestion des sessions et métadonnées</p>
    </div>

    @if (count($sessions) > 0)
        <!-- Statistiques globales -->
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title section-title"><i class="bi bi-file-text"></i> Statistiques des sessions</h2>
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="display-6 fw-bold text-primary">{{ $stats['total_sessions'] }}</div>
                            <div class="text-muted small">Sessions</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="display-6 fw-bold text-primary">{{ $stats['total_backups'] }}</div>
                            <div class="text-muted small">Fichiers</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="display-6 fw-bold text-primary">{{ human_readable_bytes_size($stats['total_size'], 2) }}</div>
                            <div class="text-muted small">Taille totale</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-center">
                            <div class="display-6 fw-bold text-primary">
                                @if($stats['last_session'])
                                    {{ $stats['last_session']['created_at']->format('d/m') }}
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
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="btn-group">
            <button onclick="refreshSessions()" class="btn btn-primary">
                <span id="refresh-icon"><i class="bi bi-arrow-repeat"></i></span> Actualiser
            </button>

            <button onclick="cleanupSessions()" class="btn btn-danger">
                <i class="bi bi-trash"></i> Nettoyer (30j+)
            </button>
        </div>

        <div class="text-muted">
            <i class="bi bi-folder"></i> {{ count($sessions) }} session(s) disponible(s)
        </div>
    </div>

    <div id="alerts"></div>

    @if (count($sessions) > 0)
        <div class="row g-4">
            @foreach ($sessions as $session)
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 entity-card" data-session="{{ $session['session_id'] }}">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="bi bi-folder fs-2 text-primary"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-1">Session <span data-bs-toggle="tooltip" title="ID complet : {{ $session['session_id'] }}">{{ $session['short_id'] }}</span></h5>
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
                                    <div class="small text-muted"><i class="bi bi-folder"></i> Fichiers</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <div class="fw-bold text-primary">
                                        @if(isset($session['dry_run']) && $session['dry_run'])
                                            <i class="bi bi-eye"></i> Dry-run
                                        @else
                                            <i class="bi bi-check-square"></i> Réel
                                        @endif
                                    </div>
                                    <div class="small text-muted"><i class="bi bi-gear"></i> Mode</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <div class="fw-bold text-primary">{{ $session['package_version'] ?? '?' }}</div>
                                    <div class="small text-muted"><i class="bi bi-file-text"></i> Version</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <div class="fw-bold text-primary">{{ $session['created_at']->diffForHumans(['short' => true]) }}</div>
                                    <div class="small text-muted"><i class="bi bi-clock"></i> Âge</div>
                                </div>
                            </div>
                        </div>

                        <div class="btn-group-vertical btn-group-sm d-grid" role="group" aria-label="Actions de la session">
                            <a href="{{ route('fontawesome-migrator.sessions.show', $session['session_id']) }}" class="btn btn-primary">
                                <i class="bi bi-eye"></i> Détails
                            </a>
                            <button onclick="deleteSession('{{ $session['session_id'] }}')" class="btn btn-outline-danger">
                                <i class="bi bi-trash"></i> Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-folder display-1 text-muted"></i>
            </div>
            <h3 class="text-muted mb-3">Aucune session disponible</h3>
            <p class="text-muted mb-4">
                Les sessions sont créées automatiquement lors des migrations.<br>
                Exécutez une migration pour voir les sessions apparaître ici.
            </p>
            <div class="bg-dark text-light p-3 rounded d-inline-block">
                <code>php artisan fontawesome:migrate --dry-run --report</code>
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