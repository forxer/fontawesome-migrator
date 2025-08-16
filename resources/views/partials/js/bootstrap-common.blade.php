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
    
    // Fonction utilitaire pour afficher des alertes temporaires (position fixe)
    window.showTempAlert = function(message, type = 'success') {
        const existing = document.querySelector('.temp-alert');
        if (existing) existing.remove();

        const alert = document.createElement('div');
        alert.className = `alert alert-${type} temp-alert`;
        alert.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.textContent = message;

        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 4000);
    };
    
    // Fonction utilitaire pour copier du texte dans le presse-papier
    window.copyToClipboard = function(text, successMessage = null) {
        navigator.clipboard.writeText(text).then(() => {
            const message = successMessage || `Commande copiée : ${text}`;
            window.showTempAlert(message, 'success');
        }).catch(() => {
            // Fallback pour les anciens navigateurs
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            const message = successMessage || `Commande copiée : ${text}`;
            window.showTempAlert(message, 'success');
        });
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