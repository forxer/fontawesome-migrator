<style>
    /* Styles spécifiques au panneau de test - nettoyé pour Bootstrap */
    
    /* Loading spinner pour les boutons */
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
    
    @keyframes spin {
        to {
            transform: translateY(-50%) rotate(360deg);
        }
    }
    
    /* Styles pour output des tests */
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
</style>