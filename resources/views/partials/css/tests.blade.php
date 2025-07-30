<style>
    /* Styles spécifiques au panneau de test - utilise les classes communes */

    /* Test buttons avec charte graphique commune */
    .test-buttons {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: var(--spacing-md);
        margin: var(--spacing-md) 0;
    }

    .test-btn {
        /* Utilise les styles btn communs + personnalisations */
        position: relative;
        font-size: 1.1rem;
    }

    .test-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .test-btn.loading::after {
        content: '';
        position: absolute;
        top: 50%;
        right: 15px;
        width: 16px;
        height: 16px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        transform: translateY(-50%);
    }

    /* Output avec styles communs */
    .test-output {
        background: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-md);
        padding: var(--spacing-md);
        margin-top: var(--spacing-md);
        max-height: 400px;
        overflow-y: auto;
    }

    .test-output pre {
        margin: 0;
        font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, monospace;
        font-size: 0.9rem;
        line-height: 1.4;
        color: var(--gray-800);
        white-space: pre-wrap;
    }

    .test-output .success {
        color: var(--success-color);
        font-weight: 600;
    }

    .test-output .error {
        color: var(--error-color);
        font-weight: 600;
    }

    .test-output .warning {
        color: var(--warning-color);
        font-weight: 600;
    }

    /* Sections de test */
    .test-section {
        background: white;
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        margin-bottom: var(--spacing-lg);
        box-shadow: var(--shadow-md);
        border-left: 4px solid var(--primary-color);
    }

    .test-section h3 {
        margin: 0 0 var(--spacing-md) 0;
        color: var(--gray-800);
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }

    .test-section .description {
        color: var(--gray-600);
        margin-bottom: var(--spacing-lg);
        line-height: 1.6;
    }

    /* Status indicators */
    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: var(--spacing-xs);
        padding: var(--spacing-xs) var(--spacing-sm);
        border-radius: var(--radius-sm);
        font-size: 0.8rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-indicator.success {
        background: #d1fae5;
        color: #065f46;
    }

    .status-indicator.error {
        background: #fee2e2;
        color: #991b1b;
    }

    .status-indicator.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .status-indicator.info {
        background: var(--blue-50);
        color: #1e40af;
    }

    /* Progress bars pour les tests */
    .test-progress {
        width: 100%;
        height: 8px;
        background: var(--gray-200);
        border-radius: var(--radius-sm);
        overflow: hidden;
        margin: var(--spacing-sm) 0;
    }

    .test-progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        transition: width 0.3s ease;
        border-radius: var(--radius-sm);
    }

    /* Responsive spécifique au panneau de test */
    @media (max-width: 768px) {
        .test-buttons {
            grid-template-columns: 1fr;
        }

        .test-btn {
            font-size: 1rem;
        }

        .test-output {
            max-height: 300px;
        }
    }
</style>