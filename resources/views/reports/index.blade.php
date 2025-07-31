@extends('fontawesome-migrator::layout')

@section('title', 'Rapports FontAwesome Migrator')

@section('head-extra')
    @include('fontawesome-migrator::partials.css.reports')
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="section-title section-title-lg">
                <i class="bi bi-file-text text-primary"></i> Rapports
            </h1>
            <p class="text-muted mb-0">Gestion des rapports de migration</p>
        </div>
    </div>

    @if (count($reports) > 0)
        <!-- Statistiques globales Bootstrap -->
        <div class="mb-4">
            <h2 class="section-title">
                <i class="bi bi-file-text text-primary"></i> Statistiques globales
            </h2>
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="fs-3 fw-bold text-primary">{{ count($reports) }}</div>
                            <div class="text-muted small">Rapports</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="fs-3 fw-bold text-primary">{{ human_readable_bytes_size(array_sum(array_column($reports, 'size')), 2) }}</div>
                            <div class="text-muted small">Total</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="fs-3 fw-bold text-primary">{{ collect($reports)->max('created_at')->format('d/m') }}</div>
                            <div class="text-muted small">Dernier rapport</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="fs-3 fw-bold text-primary">{{ collect($reports)->filter(fn($r) => $r['created_at']->isAfter(now()->subWeek()))->count() }}</div>
                            <div class="text-muted small">Cette semaine</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex gap-2">
            <button onclick="refreshReports()" class="btn btn-primary">
                <span id="refresh-icon"><i class="bi bi-arrow-repeat"></i></span> Actualiser les rapports
            </button>

            <button onclick="cleanupReports()" class="btn btn-outline-danger">
                <i class="bi bi-trash"></i> Nettoyer (30j+)
            </button>
        </div>

        <div class="text-muted fw-medium">
            <i class="bi bi-file-text"></i> {{ count($reports) }} rapport(s) disponible(s)
        </div>
    </div>

    <div id="alerts"></div>

    @if (count($reports) > 0)
        <div class="row g-4">
            @foreach ($reports as $report)
                <div class="col-lg-6 col-xl-4">
                    <div class="card h-100 shadow-sm" data-filename="{{ $report['filename'] }}">
                        <div class="card-header d-flex align-items-center gap-3">
                            <i class="bi bi-file-text text-primary fs-4"></i>
                            <div class="flex-grow-1 min-w-0">
                                <h5 class="card-title mb-1 text-truncate">{{ $report['name'] }}</h5>
                                <div class="text-muted small d-flex align-items-center gap-2">
                                    <i class="bi bi-clock"></i> {{ $report['created_at']->format('d/m/Y à H:i') }}
                                    @if($report['dry_run'])
                                        <span class="badge bg-warning text-dark">DRY-RUN</span>
                                    @else
                                        <span class="badge bg-success">RÉEL</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row g-2 text-center">
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="fw-semibold">{{ human_readable_bytes_size($report['size'], 2) }}</div>
                                        <div class="text-muted small"><i class="bi bi-hdd"></i> Taille</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="fw-semibold">{{ $report['created_at']->format('H:i') }}</div>
                                        <div class="text-muted small"><i class="bi bi-clock"></i> Heure</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="fw-semibold" data-bs-toggle="tooltip" title="ID complet : {{ $report['session_id'] }}">
                                            {{ $report['short_id'] }}
                                        </div>
                                        <div class="text-muted small"><i class="bi bi-folder"></i> Session</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-2">
                                        <div class="fw-semibold">{{ $report['created_at']->diffForHumans(['short' => true]) }}</div>
                                        <div class="text-muted small"><i class="bi bi-clock"></i> Âge</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light">
                            <div class="d-flex flex-wrap gap-1">
                                <a href="{{ route('fontawesome-migrator.reports.show', $report['filename']) }}" class="btn btn-primary btn-sm">
                                    <i class="bi bi-file-text"></i> Rapport
                                </a>

                                @if ($report['has_json'])
                                    <a href="{{ route('fontawesome-migrator.reports.show', str_replace('.html', '.json', $report['filename'])) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-filetype-json"></i> JSON
                                    </a>
                                @endif

                                <a href="{{ route('fontawesome-migrator.sessions.show', $report['session_id']) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-folder"></i> Session
                                </a>

                                <button onclick="deleteReport('{{ $report['filename'] }}')" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">
                <i class="bi bi-file-text"></i>
            </div>
            <h3 class="empty-title">Aucun rapport disponible</h3>
            <p class="empty-description">
                Commencez par générer un rapport de migration en exécutant la commande ci-dessous.
                Les rapports vous permettront de visualiser les changements effectués lors de la migration Font Awesome 5 → 6.
            </p>
            <div class="mb-4">
                <code class="empty-command">
                    php artisan fontawesome:migrate --report
                </code>
            </div>
            <div class="empty-hint">
                <i class="bi bi-arrow-repeat"></i> Ajoutez <code class="text-body">--dry-run</code> pour prévisualiser sans modifier les fichiers
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
        const button = document.querySelector('button[onclick="refreshReports()"]');
        toggleButtonSpinner(button, true);

        // Recharger la page après un court délai
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }

    async function deleteReport(filename) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer ce rapport ?')) {
            return;
        }

        try {
            const response = await fetch(`/fontawesome-migrator/reports/${filename}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (response.ok) {
                showAlert('Rapport supprimé avec succès');
                // Masquer la carte du rapport
                const card = document.querySelector(`[data-filename="${filename}"]`);
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

    async function cleanupReports() {
        if (!confirm('Supprimer tous les rapports de plus de 30 jours ?')) {
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
                showAlert(`${data.deleted} rapport(s) supprimé(s)`);
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