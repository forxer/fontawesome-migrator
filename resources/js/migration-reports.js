/**
 * FontAwesome Migrator - Scripts pour les rapports de migration
 * Fonctionnalités interactives pour l'interface des rapports HTML
 */

// Formatage français des nombres
function formatNumber(number, decimals = 0) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number);
}

function formatPercentage(number, decimals = 1) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'percent',
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number / 100);
}

// Variables globales
let allExpanded = true;

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    // S'assurer que tous les détails sont visibles par défaut
    const allDetails = document.querySelectorAll('.collapsible-content');
    allDetails.forEach(detail => {
        detail.classList.add('active');
    });
    
    // S'assurer que toutes les icônes sont en mode "ouvert"
    const allIcons = document.querySelectorAll('[id^="toggle-icon-"]');
    allIcons.forEach(icon => {
        icon.textContent = '▼';
    });
    
    // Animation des cartes statistiques
    animateStatCards();
    
    // Initialiser les améliorations si les données sont disponibles
    if (typeof window.migrationData !== 'undefined') {
        addPerformanceMetrics();
        enhanceAnimations();
    }
});

// Initialisation du graphique Chart.js (sera appelée depuis la vue avec les données)
function initializeChart(chartData, hasChanges) {
    if (!hasChanges || !chartData.labels.length) return;
    
    const ctx = document.getElementById('changesChart');
    if (!ctx) return;
    
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
                                const percentage = formatPercentage((value / total) * 100);
                                return {
                                    text: `${label}: ${formatNumber(value)} (${percentage})`,
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
                            const percentage = formatPercentage((context.parsed / total) * 100);
                            return `${context.label}: ${formatNumber(context.parsed)} (${percentage})`;
                        }
                    }
                }
            }
        }
    });
}

// Animation des cartes statistiques
function animateStatCards() {
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(el => {
        const originalText = el.textContent;
        const finalValue = parseInt(originalText.replace(/\s/g, ''));
        if (finalValue > 0) {
            let currentValue = 0;
            const increment = Math.ceil(finalValue / 30);
            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= finalValue) {
                    el.textContent = formatNumber(finalValue);
                    clearInterval(timer);
                } else {
                    el.textContent = formatNumber(currentValue);
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

// Surlignage des correspondances de recherche
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
    if (typeof window.migrationData === 'undefined') {
        showNotification('❌ Données du rapport non disponibles', 'error');
        return;
    }
    
    const textReport = generateTextReport(window.migrationData);
    
    navigator.clipboard.writeText(textReport).then(() => {
        showNotification('📋 Rapport copié dans le presse-papier !', 'success');
    }).catch(() => {
        showNotification('❌ Erreur lors de la copie', 'error');
    });
}

// Génération du rapport texte
function generateTextReport(data) {
    let report = `📊 RAPPORT DE MIGRATION FONT AWESOME 5 → 6\n`;
    report += `${'='.repeat(50)}\n\n`;
    report += `📅 Généré le: ${data.timestamp}\n`;
    report += `📦 Version: FontAwesome Migrator ${data.packageVersion}\n`;
    report += `🔄 Mode: ${data.isDryRun ? 'Dry-run (prévisualisation)' : 'Migration complète'}\n\n`;
    
    report += `📈 STATISTIQUES:\n`;
    report += `- Fichiers analysés: ${formatNumber(data.stats.total_files)}\n`;
    report += `- Fichiers modifiés: ${formatNumber(data.stats.modified_files)}\n`;
    report += `- Total changements: ${formatNumber(data.stats.total_changes)}\n`;
    report += `- Icônes migrées: ${formatNumber(data.stats.icons_migrated || 0)}\n`;
    report += `- Assets migrés: ${formatNumber(data.stats.assets_migrated || 0)}\n\n`;
    
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

// Affichage des notifications
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
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Fonctions pour les recommandations
function copyCommand(command) {
    navigator.clipboard.writeText(command).then(() => {
        showNotification(`📋 Commande copiée: ${command}`, 'success');
    }).catch(() => {
        showNotification('❌ Erreur lors de la copie', 'error');
    });
}

function showTestingTips() {
    showModal('🧪 Conseils de test', `
        <ul class="tips-list">
            <li><strong>🔍 Vérification visuelle :</strong> Naviguez sur votre site et vérifiez que toutes les icônes s'affichent correctement.</li>
            <li><strong>📱 Tests multi-appareils :</strong> Testez sur différentes tailles d'écrans (mobile, tablette, desktop).</li>
            <li><strong>🌐 Compatibilité navigateurs :</strong> Vérifiez Chrome, Firefox, Safari et Edge.</li>
            <li><strong>⚡ Performance :</strong> Utilisez les outils de développement pour vérifier les temps de chargement.</li>
            <li><strong>🎨 Cohérence design :</strong> Assurez-vous que le style et la taille des icônes restent cohérents.</li>
            <li><strong>🔄 Cache navigateur :</strong> Videz le cache ou testez en navigation privée.</li>
        </ul>
    `);
}

function scrollToWarnings() {
    const warnings = document.querySelectorAll('.alert-warning');
    if (warnings.length > 0) {
        warnings[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        warnings[0].style.animation = 'pulse 2s';
    } else {
        showNotification('ℹ️ Aucun avertissement dans cette vue', 'info');
    }
}

// Gestion des modales
function showModal(title, content) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>${title}</h3>
                <span class="close" onclick="closeModal(this)">&times;</span>
            </div>
            <div class="modal-body">
                ${content}
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    modal.style.display = 'block';
    
    // Fermer en cliquant à l'extérieur
    modal.onclick = function(event) {
        if (event.target === modal) {
            closeModal(modal.querySelector('.close'));
        }
    };
}

function closeModal(closeBtn) {
    const modal = closeBtn.closest('.modal');
    modal.style.display = 'none';
    document.body.removeChild(modal);
}

// Ajout d'une section métriques de performance
function addPerformanceMetrics() {
    if (typeof window.migrationData === 'undefined') return;
    
    const stats = window.migrationData.stats;
    
    // Calculer quelques métriques intéressantes
    const migrationRate = stats.total_files > 0 ? (stats.modified_files / stats.total_files) * 100 : 0;
    const changesDensity = stats.modified_files > 0 ? (stats.total_changes / stats.modified_files) : 0;
    const successRate = stats.total_changes > 0 ? ((stats.total_changes - (stats.warnings || 0)) / stats.total_changes) * 100 : 100;
    
    const performanceSection = document.createElement('div');
    performanceSection.className = 'section enhanced-section';
    performanceSection.innerHTML = `
        <h2>📈 Métriques de performance</h2>
        <div class="performance-metrics">
            <div class="metric-card">
                <div class="metric-value">${formatPercentage(migrationRate)}</div>
                <div class="metric-label">Taux de migration</div>
                <div class="metric-trend ${migrationRate > 50 ? 'trend-up' : 'trend-down'}">
                    ${migrationRate > 50 ? '↗ Excellent' : '→ Partiel'}
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-value">${formatNumber(changesDensity, 1)}</div>
                <div class="metric-label">Changements par fichier</div>
                <div class="metric-trend ${changesDensity < 5 ? 'trend-up' : 'trend-down'}">
                    ${changesDensity < 5 ? '↗ Léger' : '↗ Intensif'}
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-value">${formatPercentage(successRate)}</div>
                <div class="metric-label">Taux de succès</div>
                <div class="metric-trend ${successRate > 95 ? 'trend-up' : successRate > 80 ? 'trend-up' : 'trend-down'}">
                    ${successRate > 95 ? '↗ Parfait' : successRate > 80 ? '↗ Bon' : '→ À améliorer'}
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-value">${formatNumber(stats.total_changes || 0)}</div>
                <div class="metric-label">Optimisations totales</div>
                <div class="metric-trend trend-up">
                    ↗ Modernisé
                </div>
            </div>
        </div>
    `;
    
    // Insérer après les statistiques principales
    const statsGrid = document.querySelector('.stats-grid');
    if (statsGrid && stats.total_changes > 0) {
        statsGrid.parentNode.insertBefore(performanceSection, statsGrid.nextSibling);
    }
}

// Améliorer les animations
function enhanceAnimations() {
    // Animation des cartes de recommandations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    // Observer toutes les sections
    document.querySelectorAll('.section, .recommendation-card').forEach(el => {
        observer.observe(el);
    });
}