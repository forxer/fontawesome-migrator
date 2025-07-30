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

    /* Actions spécifiques à l'index (reste pour spécificités futures) */
    /* Classes communes déplacées vers common.blade.php */

    /* Stats summary spécifiques à l'index (reste pour spécificités futures) */
    /* Classes communes déplacées vers common.blade.php */

    /* Override stats-grid pour l'index */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 20px;
    }

    /* Stat item spécifique à l'index (reste pour spécificités futures) */
    /* Classes communes déplacées vers common.blade.php */

    /* Section title spécifique à l'index (reste pour spécificités futures) */
    /* Classes communes déplacées vers common.blade.php */

    /* Reports grid spécifique à l'index (reste pour spécificités futures) */
    /* Classes communes déplacées vers common.blade.php */


    /* Empty state spécifique à l'index (reste pour spécificités futures) */
    /* Classes communes déplacées vers common.blade.php */

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
