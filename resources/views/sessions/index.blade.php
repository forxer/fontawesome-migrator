@extends('fontawesome-migrator::layout')

@section('title', 'Sessions de Migration')

@section('head-extra')
    @include('fontawesome-migrator::partials.css.bootstrap-common')
@endsection

@section('content')
    <x-fontawesome-migrator::page-header
        icon="folder"
        title="Sessions"
        subtitle="Gestion des sessions et métadonnées"
        :counterText="count($sessions) . ' session(s) disponible(s)'"
        counterIcon="folder"
        :hasActions="true"
        actionsLabel="Actions globales"
    >
        <x-slot name="actions">
            <li><a class="dropdown-item" href="#" onclick="refreshSessions(); return false;">
                <span id="refresh-icon"><i class="bi bi-arrow-repeat"></i></span> Actualiser
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#" onclick="cleanupSessions(); return false;">
                <i class="bi bi-trash"></i> Nettoyer (30j+)
            </a></li>
        </x-slot>
    </x-fontawesome-migrator::page-header>

    @if (count($sessions) > 0)
        <!-- Statistiques globales -->
        <div class="mb-4">
            <h2 class="section-title">
                <i class="bi bi-bar-chart text-primary"></i> Statistiques des sessions
            </h2>
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-folder fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ $stats['total_sessions'] }}</div>
                            <div class="text-muted small">Sessions</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-files fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ $stats['total_backups'] }}</div>
                            <div class="text-muted small">Fichiers</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-hdd fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ human_readable_bytes_size($stats['total_size'], 2) }}</div>
                            <div class="text-muted small">Taille totale</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-calendar fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">
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

    @if (count($sessions) > 0)
        <div class="row g-4">
            @foreach ($sessions as $session)
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm" data-session="{{ $session['session_id'] }}">
                    <div class="card-header d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <h5 class="card-title mb-1 text-truncate">
                                <i class="bi bi-folder text-primary fs-4"></i>
                                Session <span data-bs-toggle="tooltip" title="ID complet : {{ $session['session_id'] }}">{{ $session['short_id'] }}</span>
                            </h5>
                            <div class="text-muted small">
                                <i class="bi bi-clock"></i> {{ $session['created_at']->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                        @if($session['dry_run'])
                            <span class="badge bg-warning text-dark">DRY-RUN</span>
                        @else
                            <span class="badge bg-success">RÉEL</span>
                        @endif
                    </div>
                    <div class="card-body py-4">
                        <div class="row g-3 text-center">
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <div class="fw-semibold">{{ $session['backup_count'] }}</div>
                                    <div class="text-muted small"><i class="bi bi-files"></i> Fichiers</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <div class="fw-semibold">
                                        @if($session['dry_run'])
                                            <i class="bi bi-eye text-warning"></i> Dry-run
                                        @else
                                            <i class="bi bi-check-square text-success"></i> Réel
                                        @endif
                                    </div>
                                    <div class="text-muted small"><i class="bi bi-gear"></i> Mode</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <div class="fw-semibold">{{ $session['package_version'] ?? 'v?' }}</div>
                                    <div class="text-muted small"><i class="bi bi-tag"></i> Version</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <div class="fw-semibold">{{ $session['created_at']->diffForHumans(['short' => true]) }}</div>
                                    <div class="text-muted small"><i class="bi bi-calendar"></i> Âge</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light">
                        <div class="btn-group btn-group-sm d-flex flex-wrap" role="group" aria-label="Actions de la session">
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
                <code>php artisan fontawesome:migrate --dry-run</code>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
<script>
    // Configuration CSRF pour les requêtes AJAX
    window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function showAlert(message, type = 'success') {
        const existing = document.querySelector('.temp-alert');
        if (existing) existing.remove();

        const alert = document.createElement('div');
        alert.className = `alert alert-${type} temp-alert`;
        alert.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.textContent = message;

        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 4000);
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