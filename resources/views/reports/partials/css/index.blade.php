<style>
    /* Variables spécifiques à l'index (déjà définies dans common.blade.php) */

    /* Header spécifique à l'index avec gradient */
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

    /* Sections spécifiques à l'index avec hover effects */
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
    /* Stats summary spécifique à l'index */
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

    /* Override stats-grid pour l'index */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 20px;
    }

    .stat-item {
        text-align: center;
        padding: 15px;
        background: var(--gray-50);
        border-radius: var(--radius-md);
    }

    /* Titres de section */
    /* Section title avec effet de ligne */
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

    /* Alertes avec gradients spécifiques à l'index */
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

    /* Animations spécifiques à l'index */
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


    /* Animation échelonnée des cartes */
    .report-card {
        animation: fadeInUp 0.6s ease forwards;
    }

    .report-card:nth-child(even) {
        animation-delay: 0.1s;
    }

    .report-card:nth-child(3n) {
        animation-delay: 0.2s;
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
