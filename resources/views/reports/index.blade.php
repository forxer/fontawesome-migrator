<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rapports FontAwesome Migrator</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .header h1 {
            margin: 0 0 10px 0;
            color: #2d3748;
            font-size: 2.5em;
        }
        
        .header p {
            color: #718096;
            margin: 0;
            font-size: 1.1em;
        }
        
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
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #4299e1;
            color: white;
        }
        
        .btn-primary:hover {
            background: #3182ce;
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background: #f56565;
            color: white;
        }
        
        .btn-danger:hover {
            background: #e53e3e;
            transform: translateY(-1px);
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
            color: #2d3748;
            font-size: 1.2em;
        }
        
        .report-date {
            color: #718096;
            font-size: 0.9em;
        }
        
        .report-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f7fafc;
            border-radius: 8px;
        }
        
        .meta-item {
            text-align: center;
        }
        
        .meta-value {
            font-size: 1.3em;
            font-weight: bold;
            color: #4299e1;
        }
        
        .meta-label {
            color: #718096;
            font-size: 0.9em;
            margin-top: 5px;
        }
        
        .report-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 8px 15px;
            font-size: 0.9em;
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
            color: #cbd5e0;
            margin-bottom: 20px;
        }
        
        .empty-title {
            font-size: 1.5em;
            color: #2d3748;
            margin-bottom: 10px;
        }
        
        .empty-description {
            color: #718096;
            margin-bottom: 30px;
        }
        
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #feb2b2;
        }
    </style>
</head>
<body>
    <div class="container">
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
            
            <div style="margin-left: auto; color: #718096;">
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
                <code style="background: #f7fafc; padding: 10px 15px; border-radius: 6px; color: #2d3748;">
                    php artisan fontawesome:migrate --report
                </code>
            </div>
        @endif
    </div>

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
</body>
</html>