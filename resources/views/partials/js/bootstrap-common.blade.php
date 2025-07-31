<script>
    // JavaScript commun Bootstrap pour toutes les pages
    
    // Configuration CSRF globale pour les requêtes AJAX
    window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Initialisation automatique des tooltips Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    
    // Fonction utilitaire pour afficher des alertes Bootstrap
    window.showBootstrapAlert = function(message, type = 'success', containerId = 'alerts') {
        const alertsContainer = document.getElementById(containerId);
        if (!alertsContainer) return;
        
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        alertsContainer.appendChild(alert);
        
        // Auto-dismiss après 5 secondes
        setTimeout(() => {
            if (alert.parentNode) {
                const bootstrapAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bootstrapAlert.close();
            }
        }, 5000);
    };
    
    // Fonction utilitaire pour animer un spinner sur un bouton
    window.toggleButtonSpinner = function(buttonElement, loading = true) {
        const icon = buttonElement.querySelector('i, .icon');
        const originalIcon = icon ? icon.outerHTML : '';
        
        if (loading) {
            buttonElement.disabled = true;
            if (icon) {
                icon.outerHTML = '<span class="spinner"></span>';
            }
            buttonElement.dataset.originalIcon = originalIcon;
        } else {
            buttonElement.disabled = false;
            if (buttonElement.dataset.originalIcon) {
                const spinnerElement = buttonElement.querySelector('.spinner');
                if (spinnerElement) {
                    spinnerElement.outerHTML = buttonElement.dataset.originalIcon;
                }
                delete buttonElement.dataset.originalIcon;
            }
        }
    };
    
    // Fonction utilitaire pour les requêtes AJAX avec gestion d'erreurs
    window.makeAjaxRequest = async function(url, options = {}) {
        const defaultOptions = {
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };
        
        const mergedOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers
            }
        };
        
        try {
            const response = await fetch(url, mergedOptions);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || `HTTP error! status: ${response.status}`);
            }
            
            return { success: true, data };
        } catch (error) {
            console.error('AJAX request failed:', error);
            return { success: false, error: error.message };
        }
    };
    
    // ========================================
    // Bouton retour en haut
    // ========================================
    
    // Fonction retour en haut
    window.scrollToTop = function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };
    
    // Gestion de la visibilité du bouton retour en haut
    document.addEventListener('DOMContentLoaded', function() {
        const backToTopButton = document.getElementById('backToTopBtn');
        if (!backToTopButton) return;

        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('visible');
            } else {
                backToTopButton.classList.remove('visible');
            }
        });
    });
</script>