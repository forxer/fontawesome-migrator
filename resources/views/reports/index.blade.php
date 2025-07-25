@extends('fontawesome-migrator::layout')

@section('title', 'Rapports FontAwesome Migrator')

@section('additional-styles')
.actions {
    background: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 20px;
}

.report-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.report-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
}

.report-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 15px;
}

.report-icon {
    font-size: 2em;
}

.report-title {
    flex: 1;
}

.report-title h3 {
    margin: 0 0 5px 0;
    color: var(--gray-800);
    font-size: 1.2em;
}

.report-date {
    color: var(--gray-500);
    font-size: 0.9em;
}

.report-meta {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
    padding: 15px;
    background: var(--gray-50);
    border-radius: 8px;
}

.meta-item {
    text-align: center;
}

.meta-value {
    font-size: 1.3em;
    font-weight: bold;
    color: var(--primary-color);
}

.meta-label {
    color: var(--gray-500);
    font-size: 0.9em;
    margin-top: 5px;
}

.report-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.empty-state {
    background: white;
    padding: 60px 30px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

.empty-icon {
    font-size: 4em;
    color: var(--gray-300);
    margin-bottom: 20px;
}

.empty-title {
    font-size: 1.5em;
    color: var(--gray-800);
    margin-bottom: 10px;
}

.empty-description {
    color: var(--gray-500);
    margin-bottom: 30px;
}
@endsection

@section('content')
    <div class="header">
        <h1>🚀 FontAwesome Migrator</h1>
        <p>Gestion des rapports de migration</p>
    </div>

    <div class="actions">
        <button onclick="refreshReports()" class="btn btn-primary">
            <span id="refresh-icon">🔄</span> Actualiser
        </button>
        
        <button onclick="cleanupReports()" class="btn btn-danger">
            🗑️ Nettoyer (30j+)
        </button>
        
        <div style="margin-left: auto; color: var(--gray-500);">
            {{ count($reports) }} rapport(s) disponible(s)
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
                                {{ date('d/m/Y à H:i', $report['created_at']) }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-meta">
                        <div class="meta-item">
                            <div class="meta-value">{{ number_format($report['size'] / 1024, 1) }}</div>
                            <div class="meta-label">KB</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-value">{{ date('H:i', $report['created_at']) }}</div>
                            <div class="meta-label">Heure</div>
                        </div>
                    </div>
                    
                    <div class="report-actions">
                        <a href="{{ $report['html_url'] }}" target="_blank" class="btn btn-primary btn-sm">
                            📄 Voir HTML
                        </a>
                        
                        @if ($report['json_url'])
                            <a href="{{ $report['json_url'] }}" target="_blank" class="btn btn-primary btn-sm">
                                📋 Voir JSON
                            </a>
                        @endif
                        
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
                Les rapports de migration apparaîtront ici après avoir exécuté la commande :
            </div>
            <code style="background: var(--gray-50); padding: 10px 15px; border-radius: 6px; color: var(--gray-800);">
                php artisan fontawesome:migrate --report
            </code>
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