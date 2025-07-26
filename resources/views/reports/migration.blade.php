@extends('fontawesome-migrator::layout')

@section('title', 'Rapport de Migration Font Awesome 5 → 6')

@section('body-background', '#f5f5f5')

@section('head-extra')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .enhanced-section {
            position: relative;
            overflow: hidden;
        }
        
        .search-box {
            padding: 12px;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            width: 100%;
            font-size: 16px;
            transition: border-color 0.2s;
        }
        
        .search-box:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        
        .metric-improvement {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 15px;
            background: linear-gradient(135deg, var(--success-color), #10b981);
            color: white;
            border-radius: 12px;
            margin: 10px 0;
        }
        
        .timeline-item {
            position: relative;
            padding-left: 30px;
            margin-bottom: 20px;
            border-left: 3px solid var(--primary-color);
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -7px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-color);
        }
        
        .export-buttons {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .progress-ring {
            width: 120px;
            height: 120px;
            transform: rotate(-90deg);
        }
        
        .progress-ring-circle {
            stroke: var(--primary-color);
            stroke-width: 8;
            stroke-linecap: round;
            fill: transparent;
            r: 52;
            cx: 60;
            cy: 60;
            stroke-dasharray: 326.726;
            transition: stroke-dashoffset 1s ease;
        }
        
        .collapsible-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .collapsible-content.active {
            max-height: 1000px;
        }
        
        .toggle-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .highlight-match {
            background: yellow;
            font-weight: bold;
        }
    </style>
@endsection

@section('content')
    <div class="header">
        <h1>📊 Rapport de Migration Font Awesome 5 → 6</h1>
        <p>Généré le {{ $timestamp }}</p>
    </div>

    <!-- Statistiques générales améliorées -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_files'] }}</div>
            <div class="stat-label">Fichiers analysés</div>
            @if($stats['total_files'] > 0)
                <div style="margin-top: 10px;">
                    <svg class="progress-ring">
                        <circle class="progress-ring-circle" style="stroke-dashoffset: {{ 326.726 * (1 - ($stats['modified_files'] / $stats['total_files'])) }}"></circle>
                    </svg>
                    <div style="text-align: center; margin-top: -80px; color: var(--primary-color); font-weight: bold;">
                        {{ $stats['total_files'] > 0 ? round(($stats['modified_files'] / $stats['total_files']) * 100, 1) : 0 }}%
                    </div>
                </div>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-number">{{ $stats['modified_files'] }}</div>
            <div class="stat-label">Fichiers modifiés</div>
            @if($stats['modified_files'] > 0)
                <div class="metric-improvement">
                    <span>🎯</span>
                    <span>{{ $stats['modified_files'] }} fichier(s) optimisé(s)</span>
                </div>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_changes'] }}</div>
            <div class="stat-label">Total des changements</div>
            @if($stats['total_changes'] > 0)
                <div style="margin-top: 10px; font-size: 0.9em; color: var(--success-color);">
                    ⚡ Prêt pour Font Awesome 6
                </div>
            @endif
        </div>

        <div class="stat-card">
            <div class="stat-number">{{ $stats['icons_migrated'] ?? 0 }}</div>
            <div class="stat-label">Icônes migrées</div>
            @if(($stats['icons_migrated'] ?? 0) > 0)
                <div style="margin-top: 10px; color: var(--primary-color); font-size: 0.9em;">
                    🎨 FA5 → FA6
                </div>
            @endif
        </div>

        @if(($stats['assets_migrated'] ?? 0) > 0)
        <div class="stat-card">
            <div class="stat-number">{{ $stats['assets_migrated'] }}</div>
            <div class="stat-label">Assets migrés</div>
            <div style="margin-top: 10px; color: var(--secondary-color); font-size: 0.9em;">
                📦 CDN + NPM
            </div>
        </div>
        @endif
        
        @if(!empty($stats['warnings']) && $stats['warnings'] > 0)
        <div class="stat-card" style="border-left: 4px solid var(--warning-color);">
            <div class="stat-number" style="color: var(--warning-color);">{{ $stats['warnings'] }}</div>
            <div class="stat-label">Avertissements</div>
            <div style="margin-top: 10px; color: var(--warning-color); font-size: 0.9em;">
                ⚠️ À vérifier
            </div>
        </div>
        @endif
    </div>
    
    @if($stats['total_changes'] > 0)
    <!-- Graphique des types de changements -->
    <div class="section enhanced-section">
        <h2>📊 Répartition des changements</h2>
        <div class="chart-container">
            <canvas id="changesChart"></canvas>
        </div>
    </div>
    @endif

    <!-- Configuration et options -->
    <div class="section">
        <h2>⚙️ Configuration de migration</h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <h3 style="margin: 0 0 10px 0; color: var(--gray-700);">Options utilisées</h3>
                <table style="margin-top: 0;">
                    <tr><td><strong>Mode</strong></td><td>{{ $isDryRun ? 'Dry-run (prévisualisation)' : 'Migration complète' }}</td></tr>
                    @if(!empty($migrationOptions['custom_path']))
                        <tr><td><strong>Chemin personnalisé</strong></td><td><code>{{ $migrationOptions['custom_path'] }}</code></td></tr>
                    @endif
                    @if($migrationOptions['icons_only'] ?? false)
                        <tr><td><strong>Migration</strong></td><td>Icônes uniquement</td></tr>
                    @elseif($migrationOptions['assets_only'] ?? false)
                        <tr><td><strong>Migration</strong></td><td>Assets uniquement</td></tr>
                    @else
                        <tr><td><strong>Migration</strong></td><td>Complète (icônes + assets)</td></tr>
                    @endif
                    <tr><td><strong>Sauvegarde</strong></td><td>
                        @if($migrationOptions['no_backup'] ?? false)
                            Désactivée
                        @elseif($migrationOptions['backup'] ?? false)
                            Forcée
                        @else
                            {{ ($configuration['backup_enabled'] ?? true) ? 'Activée' : 'Désactivée' }}
                        @endif
                    </td></tr>
                </table>
            </div>

            <div>
                <h3 style="margin: 0 0 10px 0; color: var(--gray-700);">Configuration</h3>
                <table style="margin-top: 0;">
                    <tr><td><strong>Type de licence</strong></td><td>{{ ucfirst($configuration['license_type'] ?? 'free') }}</td></tr>
                    <tr><td><strong>Chemins scannés</strong></td><td>
                        @if(!empty($configuration['scan_paths']))
                            @foreach($configuration['scan_paths'] as $path)
                                <code>{{ $path }}</code>@if(!$loop->last), @endif
                            @endforeach
                        @else
                            Non définis
                        @endif
                    </td></tr>
                    <tr><td><strong>Extensions</strong></td><td>
                        @if(!empty($configuration['file_extensions']))
                            @foreach($configuration['file_extensions'] as $ext)
                                <code>{{ $ext }}</code>@if(!$loop->last), @endif
                            @endforeach
                        @else
                            Toutes
                        @endif
                    </td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Informations de fin -->
    <div class="section">
        <h2>ℹ️ Informations supplémentaires</h2>
        <p><strong>Rapport généré :</strong> {{ $timestamp }}</p>
        <p><strong>Package :</strong> FontAwesome Migrator version {{ $packageVersion }}</p>

        @if($stats['total_changes'] > 0 && !$isDryRun)
            <div class="alert alert-info">
                💡 <strong>Conseil :</strong> Testez votre application pour vous assurer que tous les changements fonctionnent correctement.
            </div>
        @endif

        @if($isDryRun && $stats['total_changes'] > 0)
            <div class="alert alert-warning">
                🚀 <strong>Prêt pour la migration :</strong> Exécutez <code>php artisan fontawesome:migrate</code> pour appliquer ces changements.
            </div>
        @endif
    </div>

    @if($stats['total_changes'] > 0)
        <!-- Résumé de migration -->
        <div class="section">
            <h2>📋 Résumé de la migration</h2>

            @if($stats['migration_success'])
                <div class="alert alert-success">
                    ✅ Migration terminée avec succès ! {{ $stats['total_changes'] }} changement(s) appliqué(s) sur {{ $stats['modified_files'] }} fichier(s).
                </div>
            @else
                <div class="alert alert-warning">
                    ⚠️ Migration partielle. Certains éléments n'ont pas pu être migrés automatiquement.
                </div>
            @endif

            @if(!empty($stats['changes_by_type']))
                <table>
                    <tr><th>Type de changement</th><th>Nombre</th><th>Pourcentage</th></tr>
                    @foreach($stats['changes_by_type'] as $type => $count)
                        @php
                            $percentage = $stats['total_changes'] > 0 ? round(($count / $stats['total_changes']) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td><span class="badge">{{ ucfirst($type) }}</span></td>
                            <td>{{ $count }}</td>
                            <td>{{ $percentage }}%</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>

        <!-- Section des assets si présents -->
        @if(!empty($stats['asset_types']))
            <div class="section">
                <h2>🎨 Assets détectés</h2>
                <table>
                    <tr><th>Type d'asset</th><th>Nombre</th><th>Description</th></tr>
                    @foreach($stats['asset_types'] as $assetType => $count)
                        <tr>
                            <td><strong>{{ $assetType }}</strong></td>
                            <td>{{ $count }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $assetType)) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        <!-- Détail des fichiers modifiés avec recherche -->
        <div class="section enhanced-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>📄 Détail des modifications</h2>
                
                <div class="export-buttons">
                    <button class="btn btn-primary btn-sm" onclick="copyToClipboard()">
                        📋 Copier le rapport
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="toggleAllDetails()">
                        🔄 Développer/Réduire
                    </button>
                </div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <input type="text" 
                       class="search-box" 
                       id="searchBox" 
                       placeholder="🔍 Rechercher dans les fichiers, changements ou extensions..."
                       onkeyup="filterChanges()">
            </div>

            <div id="modificationsContainer">
                @foreach($results as $index => $result)
                    @if(!empty($result['changes']))
                        <div class="file-item" data-file="{{ $result['file'] }}" data-index="{{ $index }}">
                            <div class="file-path" style="display: flex; justify-content: space-between; align-items: center;">
                                <span>📁 {{ $result['file'] }}</span>
                                <button class="toggle-btn" onclick="toggleFileDetails({{ $index }})">
                                    <span id="toggle-icon-{{ $index }}">▼</span>
                                    {{ count($result['changes']) }} changement(s)
                                </button>
                            </div>

                            <div class="collapsible-content active" id="details-{{ $index }}">
                                @foreach($result['changes'] as $changeIndex => $change)
                                    <div class="change-item" data-change-from="{{ $change['from'] }}" data-change-to="{{ $change['to'] }}">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div style="flex: 1;">
                                                <div class="change-from">- {{ $change['from'] }}</div>
                                                <div class="change-to">+ {{ $change['to'] }}</div>
                                            </div>
                                            <div style="text-align: right; color: var(--gray-500); font-size: 0.8em;">
                                                @if(isset($change['line']))
                                                    📍 Ligne {{ $change['line'] }}<br>
                                                @endif
                                                @if(isset($change['type']))
                                                    <span class="badge badge-{{ $change['type'] }}">{{ ucfirst($change['type']) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if(!empty($result['warnings']))
                                    @foreach($result['warnings'] as $warning)
                                        <div class="alert alert-warning">⚠️ {{ $warning }}</div>
                                    @endforeach
                                @endif
                                
                                @if(!empty($result['assets']))
                                    <div class="timeline-item">
                                        <strong>Assets détectés :</strong>
                                        @foreach($result['assets'] as $asset)
                                            <div style="margin: 5px 0; font-family: monospace; font-size: 0.9em;">
                                                🎨 {{ $asset['type'] ?? 'unknown' }}: <code>{{ $asset['original'] ?? '' }}</code>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
            
            <div id="noResults" style="display: none; text-align: center; padding: 40px; color: var(--gray-500);">
                <div style="font-size: 3em;">🔍</div>
                <p>Aucun résultat trouvé pour votre recherche</p>
            </div>
        </div>
    @else
        <!-- Aucun changement -->
        <div class="section">
            <div class="alert alert-info">
                ℹ️ Aucun changement nécessaire. Votre code semble déjà compatible avec Font Awesome 6.
            </div>
        </div>
    @endif

    <!-- JavaScript pour l'interactivité -->
    <script>
        // Données pour les graphiques
        const chartData = {
            labels: [
                @if(!empty($stats['changes_by_type']))
                    @foreach($stats['changes_by_type'] as $type => $count)
                        '{{ ucfirst(str_replace("_", " ", $type)) }}',
                    @endforeach
                @endif
            ],
            data: [
                @if(!empty($stats['changes_by_type']))
                    @foreach($stats['changes_by_type'] as $type => $count)
                        {{ $count }},
                    @endforeach
                @endif
            ],
            colors: [
                '#4299e1', '#667eea', '#059669', '#f59e0b', '#f56565', '#8b5cf6', '#06b6d4'
            ]
        };

        // Initialisation du graphique Chart.js
        document.addEventListener('DOMContentLoaded', function() {
            @if($stats['total_changes'] > 0 && !empty($stats['changes_by_type']))
            const ctx = document.getElementById('changesChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            data: chartData.data,
                            backgroundColor: chartData.colors,
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    generateLabels: function(chart) {
                                        const data = chart.data;
                                        const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        return data.labels.map((label, i) => {
                                            const value = data.datasets[0].data[i];
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return {
                                                text: `${label}: ${value} (${percentage}%)`,
                                                fillStyle: data.datasets[0].backgroundColor[i],
                                                strokeStyle: data.datasets[0].backgroundColor[i],
                                                pointStyle: 'circle'
                                            };
                                        });
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                                        return `${context.label}: ${context.parsed} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            }
            @endif
            
            // Animation des cartes statistiques
            animateStatCards();
        });

        function animateStatCards() {
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(el => {
                const finalValue = parseInt(el.textContent);
                if (finalValue > 0) {
                    let currentValue = 0;
                    const increment = Math.ceil(finalValue / 30);
                    const timer = setInterval(() => {
                        currentValue += increment;
                        if (currentValue >= finalValue) {
                            el.textContent = finalValue;
                            clearInterval(timer);
                        } else {
                            el.textContent = currentValue;
                        }
                    }, 50);
                }
            });
        }

        // Fonction de recherche et filtrage
        function filterChanges() {
            const searchTerm = document.getElementById('searchBox').value.toLowerCase();
            const container = document.getElementById('modificationsContainer');
            const fileItems = container.querySelectorAll('.file-item');
            const noResults = document.getElementById('noResults');
            let visibleCount = 0;

            fileItems.forEach(item => {
                const fileName = item.dataset.file.toLowerCase();
                const changeItems = item.querySelectorAll('.change-item');
                let hasVisibleChanges = false;

                // Recherche dans le nom du fichier
                const fileMatches = fileName.includes(searchTerm);
                
                // Recherche dans les changements
                changeItems.forEach(changeItem => {
                    const changeFrom = changeItem.dataset.changeFrom.toLowerCase();
                    const changeTo = changeItem.dataset.changeTo.toLowerCase();
                    const matches = changeFrom.includes(searchTerm) || changeTo.includes(searchTerm);
                    
                    if (matches || fileMatches || searchTerm === '') {
                        changeItem.style.display = 'block';
                        hasVisibleChanges = true;
                        
                        // Surligner les correspondances
                        if (searchTerm !== '') {
                            highlightMatches(changeItem, searchTerm);
                        } else {
                            removeHighlights(changeItem);
                        }
                    } else {
                        changeItem.style.display = 'none';
                    }
                });

                if (hasVisibleChanges || fileMatches || searchTerm === '') {
                    item.style.display = 'block';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                }
            });

            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        function highlightMatches(element, searchTerm) {
            const fromEl = element.querySelector('.change-from');
            const toEl = element.querySelector('.change-to');
            
            [fromEl, toEl].forEach(el => {
                if (el) {
                    const originalText = el.textContent;
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    el.innerHTML = originalText.replace(regex, '<span class="highlight-match">$1</span>');
                }
            });
        }

        function removeHighlights(element) {
            const highlighted = element.querySelectorAll('.highlight-match');
            highlighted.forEach(el => {
                el.outerHTML = el.innerHTML;
            });
        }

        // Toggle pour afficher/masquer les détails d'un fichier
        function toggleFileDetails(index) {
            const details = document.getElementById(`details-${index}`);
            const icon = document.getElementById(`toggle-icon-${index}`);
            
            if (details.classList.contains('active')) {
                details.classList.remove('active');
                icon.textContent = '▶';
            } else {
                details.classList.add('active');
                icon.textContent = '▼';
            }
        }

        // Toggle pour développer/réduire tous les détails
        let allExpanded = true;
        function toggleAllDetails() {
            const allDetails = document.querySelectorAll('.collapsible-content');
            const allIcons = document.querySelectorAll('[id^="toggle-icon-"]');
            
            allExpanded = !allExpanded;
            
            allDetails.forEach(detail => {
                if (allExpanded) {
                    detail.classList.add('active');
                } else {
                    detail.classList.remove('active');
                }
            });
            
            allIcons.forEach(icon => {
                icon.textContent = allExpanded ? '▼' : '▶';
            });
        }

        // Copier le rapport dans le presse-papier
        function copyToClipboard() {
            const reportData = {
                timestamp: '{{ $timestamp }}',
                packageVersion: '{{ $packageVersion }}',
                stats: @json($stats),
                isDryRun: {{ $isDryRun ? 'true' : 'false' }},
                files: @json($results)
            };
            
            const textReport = generateTextReport(reportData);
            
            navigator.clipboard.writeText(textReport).then(() => {
                showNotification('📋 Rapport copié dans le presse-papier !', 'success');
            }).catch(() => {
                showNotification('❌ Erreur lors de la copie', 'error');
            });
        }

        function generateTextReport(data) {
            let report = `📊 RAPPORT DE MIGRATION FONT AWESOME 5 → 6\n`;
            report += `${'='.repeat(50)}\n\n`;
            report += `📅 Généré le: ${data.timestamp}\n`;
            report += `📦 Version: FontAwesome Migrator ${data.packageVersion}\n`;
            report += `🔄 Mode: ${data.isDryRun ? 'Dry-run (prévisualisation)' : 'Migration complète'}\n\n`;
            
            report += `📈 STATISTIQUES:\n`;
            report += `- Fichiers analysés: ${data.stats.total_files}\n`;
            report += `- Fichiers modifiés: ${data.stats.modified_files}\n`;
            report += `- Total changements: ${data.stats.total_changes}\n`;
            report += `- Icônes migrées: ${data.stats.icons_migrated || 0}\n`;
            report += `- Assets migrés: ${data.stats.assets_migrated || 0}\n\n`;
            
            if (data.files.length > 0) {
                report += `📄 DÉTAIL DES MODIFICATIONS:\n`;
                data.files.forEach(file => {
                    if (file.changes && file.changes.length > 0) {
                        report += `\n📁 ${file.file}\n`;
                        file.changes.forEach(change => {
                            report += `  - ${change.from}\n`;
                            report += `  + ${change.to}\n`;
                        });
                    }
                });
            }
            
            return report;
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                animation: slideIn 0.3s ease;
            `;
            notification.innerHTML = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Ajout des animations CSS
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>

@endsection