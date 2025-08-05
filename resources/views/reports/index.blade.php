@extends('fontawesome-migrator::layout')

@section('title', 'Migrations FontAwesome')

@section('head-extra')
    @include('fontawesome-migrator::partials.css.bootstrap-common')
@endsection

@section('content')
    <x-fontawesome-migrator::page-header
        icon="file-text"
        title="Migrations"
        subtitle="Historique et résultats des migrations FontAwesome"
        :counterText="count($reports) . ' migration(s) effectuée(s)'"
        counterIcon="folder"
        :hasActions="true"
        actionsLabel="Actions globales"
    >
        <x-slot name="actions">
            <li><a class="dropdown-item" href="#" onclick="refreshReports(); return false;">
                <span id="refresh-icon"><i class="bi bi-arrow-repeat"></i></span> Actualiser
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#" onclick="cleanupSessions(); return false;">
                <i class="bi bi-trash"></i> Nettoyer (30j+)
            </a></li>
        </x-slot>
    </x-fontawesome-migrator::page-header>

    @if (count($reports) > 0)
        <!-- Statistiques globales enrichies -->
        <div class="mb-4">
            <h2 class="section-title">
                <i class="bi bi-bar-chart text-primary"></i> Statistiques des migrations
            </h2>
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-folder fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ count($reports) }}</div>
                            <div class="text-muted small">Migrations</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-files fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ $stats['total_backups'] }}</div>
                            <div class="text-muted small">Fichiers sauvegardés</div>
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
                            <div class="text-muted small">Dernière migration</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div id="alerts"></div>

    @if (count($reports) > 0)
        <div class="row g-4 mb-4">
            @foreach ($reports as $report)
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 shadow-sm" data-session="{{ $report['session_id'] }}">
                        <div class="card-header d-flex justify-content-between align-items-center gap-3">
                                <h5 class="card-title mb-1 text-truncate">
                                    <i class="bi bi-file-text text-primary fs-4"></i>
                                   {{ $report['created_at']->format('d/m à H:i') }}
                                </h5>
                                @if ($report['dry_run'])
                                    <span class="badge bg-warning text-dark">DRY-RUN</span>
                                @else
                                    <span class="badge bg-success">RÉEL</span>
                                @endif
                        </div>
                        <div class="card-body py-4">
                            <div class="row g-3 text-center">
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="fw-semibold">{{ $report['backup_count'] }}</div>
                                        <div class="text-muted small"><i class="bi bi-files"></i> Fichiers</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="fw-semibold">{{ $report['package_version'] ?? 'v?' }}</div>
                                        <div class="text-muted small"><i class="bi bi-tag"></i> Version</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="fw-semibold">
                                            @if($report['migration_origin'] === 'web_interface')
                                                <i class="bi bi-globe text-info"></i> Web
                                            @else
                                                <i class="bi bi-terminal text-secondary"></i> CLI
                                            @endif
                                        </div>
                                        <div class="text-muted small"><i class="bi bi-arrow-right"></i> Origine</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="fw-semibold" data-bs-toggle="tooltip" title="ID complet : {{ $report['session_id'] }}">
                                            {{ $report['short_id'] }}
                                        </div>
                                        <div class="text-muted small"><i class="bi bi-arrow-repeat"></i> Migration</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="fw-semibold">
                                            @if(isset($report['migration_summary']['total_changes']))
                                                {{ $report['migration_summary']['total_changes'] }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                        <div class="text-muted small"><i class="bi bi-arrow-repeat"></i> Changements</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light">
                            <div class="btn-group btn-group-sm d-flex flex-wrap" role="group" aria-label="Actions du rapport">
                                <a href="{{ route('fontawesome-migrator.reports.show', $report['short_id']) }}" class="btn btn-primary">
                                    <i class="bi bi-file-text"></i> Rapport
                                </a>
                                <a href="{{ route('fontawesome-migrator.reports.show', $report['short_id']) }}?format=json" target="_blank" class="btn btn-outline-primary">
                                    <i class="bi bi-database"></i> JSON
                                </a>
                                <button onclick="inspectSession('{{ $report['short_id'] }}')" class="btn btn-outline-secondary">
                                    <i class="bi bi-search"></i> Inspecter
                                </button>
                                <button onclick="deleteReport('{{ $report['short_id'] }}')" class="btn btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card mb-3">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-file-text display-1 text-muted"></i>
                </div>
                <h3 class="mb-3">Aucune migration disponible</h3>
                <p class="text-muted mb-4">
                    Les migrations sont automatiquement enregistrées avec leurs résultats.<br>
                    Exécutez une migration pour voir l'historique des changements FontAwesome.
                </p>
                <div class="mb-4">
                    <code class="bg-light p-3 rounded d-inline-block">
                        php artisan fontawesome:migrate --dry-run
                    </code>
                </div>
                <div class="text-muted">
                    <i class="bi bi-info-circle me-1"></i> Ajouter <code class="bg-light px-2 py-1 rounded">--dry-run</code> permet de prévisualiser sans modifier les fichiers
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
<script>
    function showAlert(message, type = 'success') {
        showBootstrapAlert(message, type, 'alerts');
    }

    function refreshReports() {
        const refreshIcon = document.getElementById('refresh-icon');
        if (refreshIcon) {
            refreshIcon.innerHTML = '<i class="bi bi-arrow-repeat me-2 spinner-border spinner-border-sm"></i>';
        }

        // Recharger la page après un court délai
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }

    async function deleteReport(sessionId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette migration ?')) {
            return;
        }

        try {
            const response = await fetch(`/fontawesome-migrator/reports/${sessionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (response.ok) {
                showAlert('Migration supprimée avec succès');
                // Masquer la carte de la session
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
        if (!confirm('Supprimer toutes les migrations de plus de 30 jours ?')) {
            return;
        }

        try {
            const response = await fetch('/fontawesome-migrator/reports/cleanup', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ days: 30 })
            });

            const data = await response.json();

            if (response.ok) {
                showAlert(`${data.deleted} migration(s) supprimée(s)`);
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

    async function inspectSession(sessionId) {
        try {
            const response = await fetch(`/fontawesome-migrator/tests/session/${sessionId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Afficher les détails de session dans une modale ou une nouvelle fenêtre
                const details = `
Session: ${data.session_id}
Répertoire: ${data.session_dir}
Fichiers de sauvegarde: ${data.files_count}
Métadonnées: ${JSON.stringify(data.metadata, null, 2)}
                `;

                // Pour l'instant, afficher dans une alerte - on pourrait améliorer avec une vraie modale
                alert(details);
            } else {
                showAlert('Erreur lors de l\'inspection de la session', 'error');
            }
        } catch (error) {
            showAlert('Erreur de connexion', 'error');
        }
    }
</script>
@endsection