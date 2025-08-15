<style>
    /* Styles pour le component page-header */
    .page-header {
        background: linear-gradient(135deg, rgba(66, 153, 225, 0.85) 0%, rgba(102, 126, 234, 0.9) 100%);
        color: white;
        border: 1px solid rgba(66, 153, 225, 0.4);
        position: relative;
        overflow: hidden;
    }

    /* S'assurer que les bulles sont derrière le contenu */
    .page-header .bubbles-pattern,
    .page-header .bubbles-container {
        z-index: 0;
    }

    .page-header-content {
        z-index: 1;
    }

    .page-header-icon {
        backdrop-filter: blur(10px);
        width: 80px;
        height: 80px;
    }

    .page-header-title {
        color: white !important;
        text-shadow: 0 1px 3px rgba(0,0,0,0.2);
        font-size: 2.25rem !important;
    }

    .page-header-subtitle {
        color: rgba(255, 255, 255, 0.8) !important;
        font-size: 1.1rem !important;
    }

    .page-header-counter {
        background: none;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
        font-size: 1.1rem;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    /* Couleur pour le bouton dropdown */
    .page-header .dropdown-toggle {
        color: var(--primary-color) !important;
    }
</style>