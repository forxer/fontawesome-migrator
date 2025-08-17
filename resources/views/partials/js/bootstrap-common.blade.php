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


    // Fonction utilitaire pour afficher des Toasts Bootstrap
    window.showToast = function(message, type = 'success') {
        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            console.warn('Toast container not found');
            return;
        }

        // Icônes par type
        const icons = {
            success: 'bi-check-circle-fill text-success',
            error: 'bi-x-circle-fill text-danger',
            warning: 'bi-exclamation-triangle-fill text-warning',
            info: 'bi-info-circle-fill text-info'
        };

        // Couleurs de bordure par type
        const borderColors = {
            success: 'border-success',
            error: 'border-danger',
            warning: 'border-warning',
            info: 'border-info'
        };

        const icon = icons[type] || icons.info;
        const borderColor = borderColors[type] || borderColors.info;

        // Créer le toast
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div class="toast ${borderColor}" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-body d-flex align-items-center">
                    <i class="bi ${icon} me-2"></i>
                    <span class="flex-grow-1">${message}</span>
                    <button type="button" class="btn-close btn-close-sm ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        // Ajouter au conteneur
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        // Initialiser et afficher le toast
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 4000
        });

        // Supprimer l'élément du DOM après fermeture
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });

        toast.show();
    };

    // Fonction utilitaire pour copier du texte dans le presse-papier
    window.copyToClipboard = function(text, successMessage = null) {
        navigator.clipboard.writeText(text).then(() => {
            const message = successMessage || `Commande copiée : ${text}`;
            window.showToast(message, 'success');
        }).catch(() => {
            // Fallback pour les anciens navigateurs
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            const message = successMessage || `Commande copiée : ${text}`;
            window.showToast(message, 'success');
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