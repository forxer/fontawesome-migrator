@extends('fontawesome-migrator::layout')

@section('title', 'Rapports FontAwesome Migrator')

@section('body-background', 'linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%)')

@section('head-extra')
    <style>
        /* Variables CSS unifiées - Design System FontAwesome Migrator */
        :root {
            --primary-color: #4299e1;
            --primary-hover: #3182ce;
            --secondary-color: #667eea;
            --success-color: #48bb78;
            --error-color: #e53e3e;
            --warning-color: #ed8936;
            --danger-color: #e53e3e;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --blue-500: #3b82f6;
            
            /* Spacing system */
            --spacing-xs: 4px;
            --spacing-sm: 8px;
            --spacing-md: 16px;
            --spacing-lg: 24px;
            --spacing-xl: 32px;
            --spacing-2xl: 48px;
            
            /* Border radius */
            --radius-sm: 6px;
            --radius-md: 8px;
            --radius-lg: 12px;
            --radius-xl: 16px;
            
            /* Shadows */
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.15);
            --shadow-colored: 0 8px 32px rgba(66, 153, 225, 0.3);
        }

        /* Header unifié */
        .header {
            text-align: center;
            margin-bottom: var(--spacing-xl);
            padding: var(--spacing-2xl);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: var(--radius-lg);
            color: white;
            box-shadow: var(--shadow-colored);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #ffffff20, #ffffff40, #ffffff20);
        }

        .header h1 {
            margin: 0 0 var(--spacing-sm) 0;
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .header p {
            margin: 0;
            font-size: 1.1rem;
            opacity: 0.9;
            font-weight: 500;
        }

        /* Sections améliorées */
        .section {
            background: white;
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-md);
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }

        .section:hover {
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        /* Actions */
        .actions {
            background: white;
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-md);
            display: flex;
            gap: var(--spacing-md);
            align-items: center;
            flex-wrap: wrap;
            border-left: 4px solid var(--primary-color);
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
        }

        .actions:hover {
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        /* Stats summary */
        .stats-summary {
            background: white;
            padding: var(--spacing-lg);
            border-radius: var(--radius-lg);
            margin-bottom: var(--spacing-xl);
            box-shadow: var(--shadow-md);
            border-left: 4px solid var(--success-color);
            border: 1px solid var(--gray-200);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stats-summary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--success-color), var(--primary-color));
        }

        .stats-summary:hover {
            box-shadow: var(--shadow-lg);
            border-color: var(--success-color);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background: var(--gray-50);
            border-radius: 8px;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--gray-600);
            font-size: 0.9em;
        }

        /* Titres de section */
        .section-title {
            margin: 0 0 var(--spacing-lg) 0;
            color: var(--gray-800);
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            font-size: 1.5rem;
            font-weight: 600;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, var(--primary-color), transparent);
            margin-left: var(--spacing-md);
        }

        /* Reports grid */
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
            gap: 25px;
        }

        .report-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .report-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }

        .report-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .report-icon {
            font-size: 2.5em;
            color: var(--primary-color);
        }

        .report-title {
            flex: 1;
        }

        .report-title h3 {
            margin: 0 0 5px 0;
            color: var(--gray-800);
            font-size: 1.3em;
            font-weight: 600;
        }

        .report-date {
            color: var(--gray-500);
            font-size: 0.95em;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .report-meta {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
            padding: 20px;
            background: linear-gradient(135deg, var(--gray-50) 0%, #ffffff 100%);
            border-radius: 10px;
            border: 1px solid var(--gray-200);
        }

        .meta-item {
            text-align: center;
        }

        .meta-value {
            font-size: 1.4em;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .meta-label {
            color: var(--gray-500);
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* Système de boutons unifié */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm) var(--spacing-md);
            border-radius: var(--radius-md);
            text-decoration: none;
            font-size: 0.9em;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: none;
            white-space: nowrap;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 2px 8px rgba(66, 153, 225, 0.3);
            border: 1px solid var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(66, 153, 225, 0.4);
            border-color: var(--primary-hover);
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
            box-shadow: 0 2px 8px rgba(229, 62, 62, 0.3);
            border: 1px solid var(--danger-color);
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(229, 62, 62, 0.4);
            border-color: #dc2626;
        }

        .btn-sm {
            padding: var(--spacing-xs) var(--spacing-sm);
            font-size: 0.85em;
        }

        /* Empty state */
        .empty-state {
            background: white;
            padding: 80px 40px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            border: 2px dashed var(--gray-300);
        }

        .empty-icon {
            font-size: 5em;
            color: var(--gray-300);
            margin-bottom: 25px;
        }

        .empty-title {
            font-size: 1.6em;
            color: var(--gray-800);
            margin-bottom: 15px;
            font-weight: 600;
        }

        .empty-description {
            color: var(--gray-500);
            margin-bottom: 30px;
            line-height: 1.6;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .empty-code {
            background: var(--gray-50);
            padding: 15px 20px;
            border-radius: 8px;
            color: var(--gray-800);
            font-family: 'Courier New', monospace;
            border: 1px solid var(--gray-200);
            display: inline-block;
        }

        /* Alertes modernisées */
        .alert {
            padding: var(--spacing-md) var(--spacing-lg);
            border-radius: var(--radius-md);
            margin: var(--spacing-md) 0;
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            border: 1px solid;
            font-weight: 500;
            animation: slideInDown 0.3s ease;
        }

        .alert-success {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border-color: #86efac;
            color: #166534;
        }

        .alert-error {
            background: linear-gradient(135deg, #fef2f2 0%, #fef7f7 100%);
            border-color: #fecaca;
            color: #991b1b;
        }

        /* Animations */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .spinner {
            display: inline-block;
            width: 1em;
            height: 1em;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Animation de cartes */
        .report-card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .report-card:nth-child(even) {
            animation-delay: 0.1s;
        }

        .report-card:nth-child(3n) {
            animation-delay: 0.2s;
        }

        /* Bouton retour en haut */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #4299e1; /* Couleur fixe pour éviter les problèmes de variable */
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(66, 153, 225, 0.3);
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1001; /* Z-index plus élevé que les modales */
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: #3182ce;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(66, 153, 225, 0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .reports-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 2rem;
            }

            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .report-meta {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
@endsection

@section('content')
    <div class="header">
        <h1>🚀 FontAwesome Migrator</h1>
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
                    <div class="stat-number">{{ date('d/m', max(array_column($reports, 'created_at'))) }}</div>
                    <div class="stat-label">Dernier</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ count(array_filter($reports, fn($r) => $r['created_at'] > strtotime('-7 days'))) }}</div>
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
                                🕒 {{ date('d/m/Y à H:i', $report['created_at']) }}
                            </div>
                        </div>
                    </div>

                    <div class="report-meta">
                        <div class="meta-item">
                            <div class="meta-value">{{ number_format($report['size'] / 1024, 1, ',', ' ') }}</div>
                            <div class="meta-label">📊 Taille (KB)</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-value">{{ date('H:i', $report['created_at']) }}</div>
                            <div class="meta-label">🕒 Heure</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-value">
                                @php
                                    $age = time() - $report['created_at'];
                                    if ($age < 3600) echo floor($age / 60) . 'm';
                                    elseif ($age < 86400) echo floor($age / 3600) . 'h';
                                    else echo floor($age / 86400) . 'j';
                                @endphp
                            </div>
                            <div class="meta-label">⏰ Âge</div>
                        </div>
                    </div>

                    <div class="report-actions">
                        <a href="{{ route('fontawesome-migrator.reports.show', $report['filename']) }}" class="btn btn-primary btn-sm">
                            📄 Voir Rapport
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

    <!-- Bouton retour en haut -->
    <button class="back-to-top" onclick="scrollToTop()" title="Retour en haut">
        ↑
    </button>
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

    // Fonction retour en haut
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Gestion de la visibilité du bouton retour en haut
    document.addEventListener('DOMContentLoaded', function() {
        const backToTopButton = document.querySelector('.back-to-top');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('visible');
            } else {
                backToTopButton.classList.remove('visible');
            }
        });
    });
</script>
@endsection