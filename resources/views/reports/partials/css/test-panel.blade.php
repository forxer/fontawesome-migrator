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
    white-space: pre-wrap;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    color: var(--gray-700);
}

/* Sessions utilisant la grille commune */
.sessions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-md);
    margin-top: var(--spacing-md);
}

.session-card {
    /* Utilise les mêmes styles que stat-card */
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: var(--spacing-md);
    transition: all 0.2s;
    box-shadow: var(--shadow-sm);
}

.session-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--primary-color);
}

.session-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-sm);
}

.session-header h3 {
    margin: 0;
    font-size: 1.1rem;
    color: var(--gray-800);
}

.session-badges {
    display: flex;
    gap: var(--spacing-xs);
}

.session-details {
    margin: var(--spacing-sm) 0;
}

.session-stat {
    margin: var(--spacing-xs) 0;
    font-size: 0.9rem;
    color: var(--gray-600);
}

.session-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: var(--spacing-md);
    padding-top: var(--spacing-sm);
    border-top: 1px solid var(--gray-200);
}

/* Boutons de nettoyage */
.cleanup-buttons {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-md);
}

/* Badges avec styles communs */
.badge {
    padding: 4px 8px;
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    font-weight: 500;
}

.badge-info {
    background: var(--blue-100);
    color: var(--blue-600);
}

.badge-secondary {
    background: var(--gray-100);
    color: var(--gray-600);
}

.badge-success {
    background: rgba(72, 187, 120, 0.1);
    color: var(--success-color);
}

.badge-error {
    background: rgba(229, 62, 62, 0.1);
    color: var(--error-color);
}

/* Animation commune */
@keyframes spin {
    0% { transform: translateY(-50%) rotate(0deg); }
    100% { transform: translateY(-50%) rotate(360deg); }
}

/* Responsive avec breakpoints communs */
@media (max-width: 768px) {
    .test-buttons {
        grid-template-columns: 1fr;
    }
    
    .sessions-grid {
        grid-template-columns: 1fr;
    }
    
    .cleanup-buttons {
        flex-direction: column;
    }
}
</style>