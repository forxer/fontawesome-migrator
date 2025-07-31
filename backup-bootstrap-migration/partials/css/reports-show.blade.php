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

    /* Variantes colorées pour les stat-cards */
    .stat-card-warning {
        border-left-color: var(--warning-color);
        background: linear-gradient(135deg, rgba(237, 137, 54, 0.05) 0%, white 100%);
    }

    .stat-card-success {
        border-left-color: var(--success-color);
        background: linear-gradient(135deg, rgba(72, 187, 120, 0.05) 0%, white 100%);
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

    /* Timeline styles - Chronologie de migration améliorée */
    .timeline-container {
        position: relative;
        margin: var(--spacing-xl) 0;
        padding: var(--spacing-lg) 0 var(--spacing-lg) 50px;
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-radius: var(--radius-lg);
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .timeline-container::before {
        content: '';
        position: absolute;
        left: 25px;
        top: var(--spacing-lg);
        bottom: var(--spacing-lg);
        width: 3px;
        background: linear-gradient(
            to bottom, 
            var(--primary-color) 0%, 
            var(--secondary-color) 50%, 
            var(--success-color) 100%
        );
        border-radius: 2px;
        box-shadow: 0 0 10px rgba(66, 153, 225, 0.3);
    }

    .timeline-item {
        position: relative;
        margin-bottom: var(--spacing-xl);
        background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
        border-radius: var(--radius-lg);
        padding: var(--spacing-xl);
        box-shadow: 
            0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid transparent;
        background-clip: padding-box;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .timeline-item::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }

    .timeline-item:hover {
        box-shadow: 
            0 20px 25px -5px rgba(0, 0, 0, 0.1),
            0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transform: translateX(8px) translateY(-2px);
        border-color: rgba(66, 153, 225, 0.2);
    }

    .timeline-item:hover::after {
        transform: scaleX(1);
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -38px;
        top: 28px;
        width: 16px;
        height: 16px;
        background: radial-gradient(circle, var(--primary-color) 0%, var(--primary-hover) 100%);
        border: 4px solid white;
        border-radius: 50%;
        box-shadow: 
            0 0 0 3px var(--primary-color),
            0 4px 8px rgba(66, 153, 225, 0.3);
        z-index: 2;
        transition: all 0.3s ease;
    }

    .timeline-item:hover::before {
        transform: scale(1.2);
        box-shadow: 
            0 0 0 3px var(--primary-color),
            0 6px 12px rgba(66, 153, 225, 0.4);
    }

    .timeline-content {
        position: relative;
        z-index: 1;
    }

    .timeline-content h4 {
        margin: 0 0 var(--spacing-md) 0;
        color: var(--gray-800);
        font-size: 1.2rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        letter-spacing: -0.025em;
    }

    .timeline-content h4 i {
        margin-right: var(--spacing-md);
        padding: 10px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: var(--radius-md);
        font-size: 1rem;
        box-shadow: 0 2px 4px rgba(66, 153, 225, 0.3);
        transition: transform 0.2s ease;
        min-width: 40px;
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .timeline-item:hover .timeline-content h4 i {
        transform: rotate(5deg) scale(1.05);
    }

    .timeline-content p {
        margin: var(--spacing-md) 0;
        color: var(--gray-600);
        line-height: 1.7;
        font-size: 1rem;
        font-weight: 400;
    }

    .timeline-content small {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        background: linear-gradient(135deg, var(--gray-100), var(--gray-50));
        color: var(--gray-600);
        font-size: 0.8rem;
        font-weight: 500;
        border-radius: var(--radius-full);
        border: 1px solid var(--gray-200);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .timeline-content small::before {
        content: '⏱';
        margin-right: 6px;
        font-size: 0.9rem;
    }

    /* États spéciaux pour différents types d'étapes */
    .timeline-item:first-child::before {
        background: radial-gradient(circle, var(--primary-color) 0%, var(--primary-hover) 100%);
        animation: pulse-primary 2s infinite;
    }

    .timeline-item:nth-child(2)::before {
        background: radial-gradient(circle, var(--warning-color) 0%, #f59e0b 100%);
        box-shadow: 
            0 0 0 3px var(--warning-color),
            0 4px 8px rgba(245, 158, 11, 0.3);
    }

    .timeline-item:last-child {
        margin-bottom: 0;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-color: rgba(72, 187, 120, 0.2);
    }

    .timeline-item:last-child::before {
        background: radial-gradient(circle, var(--success-color) 0%, #059669 100%);
        box-shadow: 
            0 0 0 3px var(--success-color),
            0 4px 8px rgba(72, 187, 120, 0.3);
        animation: pulse-success 2s infinite;
    }

    .timeline-item:last-child::after {
        background: linear-gradient(90deg, var(--success-color), #10b981);
    }

    .timeline-item:last-child .timeline-content h4 i {
        background: linear-gradient(135deg, var(--success-color), #059669);
    }

    /* Animations */
    @keyframes pulse-primary {
        0%, 100% { 
            transform: scale(1);
            opacity: 1;
        }
        50% { 
            transform: scale(1.1);
            opacity: 0.8;
        }
    }

    @keyframes pulse-success {
        0%, 100% { 
            transform: scale(1);
            box-shadow: 
                0 0 0 3px var(--success-color),
                0 4px 8px rgba(72, 187, 120, 0.3);
        }
        50% { 
            transform: scale(1.05);
            box-shadow: 
                0 0 0 6px rgba(72, 187, 120, 0.4),
                0 6px 12px rgba(72, 187, 120, 0.4);
        }
    }

    /* Responsive pour timeline améliorée */
    @media (max-width: 768px) {
        .timeline-container {
            padding: var(--spacing-md) 0 var(--spacing-md) 40px;
            margin: var(--spacing-lg) 0;
        }

        .timeline-container::before {
            left: 18px;
            width: 2px;
        }

        .timeline-item {
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }

        .timeline-item::before {
            left: -28px;
            width: 14px;
            height: 14px;
            top: 24px;
        }

        .timeline-item:hover {
            transform: translateY(-2px);
            box-shadow: 
                0 10px 15px -3px rgba(0, 0, 0, 0.1),
                0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .timeline-item:hover::before {
            transform: scale(1.1);
        }

        .timeline-content h4 {
            font-size: 1.1rem;
            flex-direction: column;
            align-items: flex-start;
        }

        .timeline-content h4 i {
            margin-right: 0;
            margin-bottom: var(--spacing-sm);
            padding: 8px;
            font-size: 0.9rem;
            min-width: 36px;
            min-height: 36px;
        }

        .timeline-item:hover .timeline-content h4 i {
            transform: scale(1.05);
        }

        .timeline-content p {
            font-size: 0.95rem;
        }

        .timeline-content small {
            font-size: 0.75rem;
            padding: 3px 10px;
        }
    }

    @media (max-width: 480px) {
        .timeline-container {
            padding-left: 35px;
        }

        .timeline-container::before {
            left: 15px;
        }

        .timeline-item::before {
            left: -25px;
            width: 12px;
            height: 12px;
        }

        .timeline-content h4 {
            font-size: 1rem;
        }

        .timeline-content p {
            font-size: 0.9rem;
        }
    }
</style>