<style>
    /* Styles spécifiques à la page de détail d'un rapport de migration - nettoyé pour Bootstrap */

    /* Décalage pour les ancres de navigation - évite que le contenu soit masqué par la navbar */
    [id] {
        scroll-margin-top: 40px;
        scroll-padding-top: 40px;
    }

    /* Alternative avec pseudo-element pour un meilleur contrôle */
    #statistics:before,
    #recommendations-section:before,
    #configuration-section:before,
    #environment-section:before,
    #details-section:before,
    #backups-section:before {
        content: "";
        display: block;
        height: 40px;
        margin-top: -40px;
        visibility: hidden;
        pointer-events: none;
    }

    /* Navigation rapide améliorée */
    .hover-bg-light {
        transition: all 0.2s ease;
    }

    .hover-bg-light:hover {
        background-color: #f8f9fa !important;
        border-color: #dee2e6 !important;
        transform: translateY(-1px);
        transition: all 0.2s ease-in-out;
    }
</style>