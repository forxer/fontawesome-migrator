@extends('fontawesome-migrator::layout')

@section('title', 'Rapports FontAwesome Migrator')

@section('head-extra')
    @include('fontawesome-migrator::partials.css.reports')
@endsection

@section('content')
    <div class="header">
        <h1>📊 Rapports</h1>
        <p>Gestion des rapports de migration</p>
    </div>

    @if (count($reports) > 0)
        <!-- Statistiques globales -->
        <div class="stats-summary">
            <h2 class="section-title">
                📈 Statistiques globales
            </h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">{{ count($reports) }}</div>
                    <div class="stat-label">Rapports</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ number_format(array_sum(array_column($reports, 'size')) / 1024, 1, ',', ' ') }}</div>
                    <div class="stat-label">KB Total</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ collect($reports)->max('created_at')->format('d/m') }}</div>
                    <div class="stat-label">Dernier rapport</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ collect($reports)->filter(fn($r) => $r['created_at']->isAfter(now()->subWeek()))->count() }}</div>
                    <div class="stat-label">Cette semaine</div>
                </div>
            </div>
        </div>
    @endif

    <div class="actions">
        <button onclick="refreshReports()" class="btn btn-primary">
            <span id="refresh-icon">🔄</span> Actualiser les rapports
        </button>

        <button onclick="cleanupReports()" class="btn btn-danger">
            🗑️ Nettoyer (30j+)
        </button>

        <div style="margin-left: auto; color: var(--gray-500); font-weight: 500;">
            📊 {{ count($reports) }} rapport(s) disponible(s)
        </div>
    </div>

    <div id="alerts"></div>

    @if (count($reports) > 0)
        <div class="reports-grid">
            @foreach ($reports as $report)
                <div class="report-card" data-filename="{{ $report['filename'] }}">
                    <div class="report-header">
                        <div class="report-icon">📊</div>
                        <div class="report-title">
                            <h3>{{ $report['name'] }}</h3>
                            <div class="report-date">
                                🕒 {{ $report['created_at']->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                    </div>

                    <div class="report-meta">
                        <div class="meta-item">
                            <div class="meta-value">{{ number_format($report['size'] / 1024, 1, ',', ' ') }}</div>
                            <div class="meta-label">📊 Taille (KB)</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-value">{{ $report['created_at']->format('H:i') }}</div>
                            <div class="meta-label">🕒 Heure</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-value" data-tooltip="ID complet : {{ $report['session_id'] }}">
                                {{ $report['short_id'] }}
                            </div>
                            <div class="meta-label">🗂️ Session</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-value">{{ $report['created_at']->diffForHumans(['short' => true]) }}</div>
                            <div class="meta-label">⏰ Âge</div>
                        </div>
                    </div>

                    <div class="report-actions">
                        <a href="{{ route('fontawesome-migrator.reports.show', $report['filename']) }}" class="btn btn-primary btn-sm">
                            📄 Voir Rapport
                        </a>

                        @if ($report['has_json'])
                            <a href="{{ route('fontawesome-migrator.reports.show', str_replace('.html', '.json', $report['filename'])) }}" target="_blank" class="btn btn-primary btn-sm">
                                📋 Voir JSON
                            </a>
                        @endif

                        <a href="{{ route('fontawesome-migrator.sessions.show', $report['session_id']) }}" class="btn btn-secondary btn-sm">
                            🗂️ Session
                        </a>

                        <button onclick="deleteReport('{{ $report['filename'] }}')" class="btn btn-danger btn-sm">
                            🗑️ Supprimer
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon">📊</div>
            <div class="empty-title">Aucun rapport disponible</div>
            <div class="empty-description">
                Commencez par générer un rapport de migration en exécutant la commande ci-dessous.
                Les rapports vous permettront de visualiser les changements effectués lors de la migration Font Awesome 5 → 6.
            </div>
            <div class="empty-code">
                php artisan fontawesome:migrate --report
            </div>
            <div style="margin-top: 20px; font-size: 0.9em; color: var(--gray-400);">
                💡 Ajoutez <code>--dry-run</code> pour prévisualiser sans modifier les fichiers
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

    function refreshReports() {
        const icon = document.getElementById('refresh-icon');
        icon.innerHTML = '<span class="spinner"></span>';

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