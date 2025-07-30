<style>
    /* Styles spécifiques à la page de détail d'un rapport de migration */

    /* Boîte de recherche spécifique à la migration */
    .search-box {
        padding: 12px;
        border: 2px solid var(--gray-200);
        border-radius: var(--radius-md);
        width: 100%;
        font-size: 16px;
        transition: border-color 0.2s;
    }

    .search-box:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    /* Override stats-grid pour la migration */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: var(--radius-md);
        padding: var(--spacing-lg);
        text-align: center;
        box-shadow: var(--shadow-md);
        border-left: 4px solid var(--primary-color);
    }

    /* Métriques avec indicateurs de performance */
    .metric-improvement {
        margin-top: 10px;
        font-size: 0.9em;
        color: var(--success-color);
    }

    /* Boutons d'export */
    .export-buttons {
        display: flex;
        gap: 10px;
    }

    /* Graphiques et visualisations spécifiques à la migration */
    .chart-container {
        position: relative;
        height: 300px;
        margin: var(--spacing-lg) 0;
        display: flex;
        justify-content: center;
        align-items: center;
        background: white;
        border-radius: var(--radius-md);
        padding: var(--spacing-lg);
    }

    /* Détails des fichiers spécifiques à la migration */
    .file-details {
        margin-top: var(--spacing-md);
    }

    .file-details summary {
        cursor: pointer;
        font-weight: 600;
        padding: var(--spacing-sm);
        background: var(--gray-50);
        border-radius: var(--radius-sm);
        transition: background 0.2s;
    }

    .file-details summary:hover {
        background: var(--gray-100);
    }

    .file-details[open] summary {
        background: var(--primary-color);
        color: white;
    }

    /* Boutons de filtre/toggle */
    .toggle-buttons {
        display: flex;
        gap: var(--spacing-sm);
        margin-bottom: var(--spacing-lg);
        flex-wrap: wrap;
    }

    .toggle-btn {
        padding: var(--spacing-sm) var(--spacing-md);
        background: var(--gray-100);
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.9rem;
    }

    .toggle-btn:hover {
        background: var(--gray-200);
    }

    .toggle-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    /* Résultats de recherche */
    .search-results {
        font-size: 0.9rem;
        color: var(--gray-600);
        margin-bottom: var(--spacing-sm);
    }

    /* Recommandations spécifiques à la migration */
    .recommendations {
        background: linear-gradient(135deg, #fef3c7 0%, #fef5cf 100%);
        border: 1px solid #fbbf24;
        border-radius: var(--radius-md);
        padding: var(--spacing-lg);
        margin: var(--spacing-lg) 0;
    }

    .recommendations h3 {
        color: #92400e;
        margin-bottom: var(--spacing-md);
    }

    .recommendations ul {
        margin: 0;
        padding-left: var(--spacing-lg);
    }

    .recommendations li {
        margin: var(--spacing-sm) 0;
        color: #92400e;
    }

    /* Performance metrics */
    .performance-section {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1px solid #86efac;
        border-radius: var(--radius-md);
        padding: var(--spacing-lg);
        margin: var(--spacing-lg) 0;
    }

    .performance-section h3 {
        color: #166534;
        margin-bottom: var(--spacing-md);
    }

    .metric-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: var(--spacing-sm) 0;
        border-bottom: 1px solid #d1fae5;
    }

    .metric-row:last-child {
        border-bottom: none;
    }

    .metric-label {
        color: #166534;
        font-weight: 500;
    }

    .metric-value {
        font-weight: bold;
        color: #047857;
    }

    /* Responsive spécifique à la migration */
    @media (max-width: 768px) {
        .export-buttons {
            flex-direction: column;
        }

        .toggle-buttons {
            justify-content: center;
        }

        .chart-container {
            height: 250px;
        }
    }
</style>