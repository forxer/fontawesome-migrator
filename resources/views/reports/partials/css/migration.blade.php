<style>
    /* Variables CSS */
    :root {
        --primary-color: #4299e1;
        --primary-hover: #3182ce;
        --secondary-color: #667eea;
        --success-color: #48bb78;
        --error-color: #e53e3e;
        --warning-color: #ed8936;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-500: #6b7280;
        --gray-700: #374151;
        --blue-500: #3b82f6;
    }

    /* Sections améliorées */
    .enhanced-section {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        margin-bottom: 20px;
        padding: 20px;
    }

    .enhanced-section h2 {
        color: var(--gray-700);
        margin-bottom: 20px;
    }

    /* Boîte de recherche */
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

    /* Statistiques */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-left: 4px solid var(--primary-color);
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: var(--primary-color);
        margin-bottom: 10px;
    }

    .stat-label {
        color: var(--gray-700);
        font-weight: 500;
    }

    .metric-improvement {
        margin-top: 10px;
        font-size: 0.9em;
        color: var(--success-color);
    }

    /* Boutons */
    .btn {
        display: inline-block;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 0.9em;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-primary {
        background: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
    }

    .btn-warning {
        background: var(--warning-color);
        color: white;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8em;
    }

    .export-buttons {
        display: flex;
        gap: 10px;
    }

    /* Graphiques */
    .chart-container {
        position: relative;
        height: 300px;
        margin: 20px 0;
        display: flex;
        justify-content: center;
        align-items: center;
        background: white;
        border-radius: 8px;
        padding: 20px;
    }

    /* Timeline */
    .timeline-container {
        position: relative;
        padding-left: 30px;
    }

    .timeline-container:before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: var(--gray-300);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .timeline-item:before {
        content: '';
        position: absolute;
        left: -37px;
        top: 20px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--primary-color);
    }

    /* Recommandations */
    .recommendations-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }

    .recommendation-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        display: flex;
        gap: 15px;
    }

    .priority-high {
        border-left: 4px solid var(--error-color);
    }

    .priority-medium {
        border-left: 4px solid var(--warning-color);
    }

    .priority-success {
        border-left: 4px solid var(--success-color);
    }

    .priority-info {
        border-left: 4px solid var(--blue-500);
    }

    .rec-icon {
        font-size: 2rem;
    }

    .rec-content h4 {
        margin: 0 0 10px 0;
        color: var(--gray-700);
    }

    /* Métriques de performance */
    .performance-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .metric-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .metric-value {
        font-size: 2rem;
        font-weight: bold;
        color: var(--primary-color);
        margin-bottom: 10px;
    }

    .metric-label {
        color: var(--gray-700);
        font-weight: 500;
        margin-bottom: 10px;
    }

    .metric-trend {
        font-size: 0.9em;
        font-weight: 500;
    }

    .trend-up {
        color: var(--success-color);
    }

    .trend-down {
        color: var(--error-color);
    }

    /* Tables */
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    th, td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid var(--gray-200);
    }

    th {
        background: var(--gray-50);
        font-weight: 600;
        color: var(--gray-700);
    }

    .badge {
        background: var(--primary-color);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8em;
    }

    .badge-style {
        background: var(--secondary-color);
    }

    .badge-icon {
        background: var(--success-color);
    }

    .badge-asset {
        background: var(--warning-color);
    }

    /* Alertes */
    .alert {
        padding: 15px;
        border-radius: 8px;
        margin: 15px 0;
    }

    .alert-success {
        background: #f0fdf4;
        border: 1px solid #86efac;
        color: #166534;
    }

    .alert-warning {
        background: #fefce8;
        border: 1px solid #fde047;
        color: #a16207;
    }

    .alert-info {
        background: #eff6ff;
        border: 1px solid #93c5fd;
        color: #1e40af;
    }

    /* Contenu pliable */
    .collapsible-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }

    .collapsible-content.active {
        max-height: 2000px !important;
        overflow: visible !important;
        transition: max-height 0.3s ease-in;
    }

    /* Fichiers et changements */
    .file-item {
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        margin-bottom: 15px;
        overflow: hidden;
    }

    .file-path {
        background: var(--gray-50);
        padding: 15px;
        border-bottom: 1px solid var(--gray-200);
    }

    .toggle-btn {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.9em;
        transition: background-color 0.2s;
    }

    .toggle-btn:hover {
        background: var(--primary-hover);
    }

    .change-item {
        padding: 15px;
        border-bottom: 1px solid var(--gray-100);
        background: white;
    }

    .change-item:last-child {
        border-bottom: none;
    }

    .change-from {
        color: var(--error-color);
        font-family: monospace;
        background: #fef2f2;
        padding: 8px;
        border-radius: 4px;
        margin-bottom: 5px;
    }

    .change-to {
        color: var(--success-color);
        font-family: monospace;
        background: #f0fdf4;
        padding: 8px;
        border-radius: 4px;
    }

    /* Styles pour les changements avec avertissements */
    .change-with-warning {
        border-left: 4px solid var(--warning-color);
        background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);
        margin: 10px 0;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .change-item {
        margin: 8px 0;
        padding: 12px;
        border-radius: 6px;
        background: #ffffff;
        border: 1px solid var(--gray-200);
        transition: all 0.2s ease;
    }

    .change-item:hover {
        border-color: var(--primary-color);
        box-shadow: 0 2px 8px rgba(66, 153, 225, 0.1);
    }

    /* Surlignage de recherche */
    .highlight-match {
        background: #fef3c7;
        color: #92400e;
        padding: 2px 4px;
        border-radius: 3px;
    }

    /* Animations */
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

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    /* Section générale */
    .section {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .section h2 {
        margin-top: 0;
        color: var(--gray-700);
        border-bottom: 2px solid var(--gray-200);
        padding-bottom: 10px;
    }

    .header {
        text-align: center;
        margin-bottom: 30px;
        padding: 20px;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .header h1 {
        margin: 0;
        color: var(--gray-700);
    }

    /* Tips list */
    .tips-list {
        list-style: none;
        padding: 0;
    }

    .tips-list li {
        margin-bottom: 15px;
        padding: 10px;
        background: var(--gray-50);
        border-radius: 6px;
        border-left: 4px solid var(--primary-color);
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
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: bold;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
        z-index: 1001; /* Z-index plus élevé que les modales */
        opacity: 0;
        transform: translateY(20px);
    }

    .back-to-top:hover {
        background: #3182ce;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    }

    .back-to-top.show {
        display: flex;
        opacity: 1;
        transform: translateY(0);
        animation: fadeInUp 0.3s ease;
    }

    @media (max-width: 768px) {
        .back-to-top {
            bottom: 20px;
            right: 20px;
            width: 45px;
            height: 45px;
            font-size: 18px;
        }
    }

    /* Table des matières */
    .table-of-contents {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        border: 1px solid var(--gray-200);
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 30px;
    }

    .toc-title {
        margin: 0 0 15px 0;
        color: var(--gray-700);
        font-size: 18px;
        font-weight: 600;
    }

    .toc-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 8px;
    }

    .toc-item {
        display: flex;
        align-items: center;
    }

    .toc-link {
        display: flex;
        align-items: center;
        color: var(--gray-600);
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 6px;
        transition: all 0.2s ease;
        font-size: 14px;
        width: 100%;
    }

    .toc-link:hover {
        background: white;
        color: var(--primary-color);
        transform: translateX(4px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .toc-icon {
        margin-right: 8px;
        font-size: 16px;
    }

    @media (max-width: 768px) {
        .toc-list {
            grid-template-columns: 1fr;
        }
    }
</style>
