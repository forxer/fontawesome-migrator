<style>
    /* Styles pour le component page-header */
    .page-header {
        background: linear-gradient(135deg, rgba(66, 153, 225, 0.85) 0%, rgba(102, 126, 234, 0.9) 100%);
        color: white;
        border: 1px solid rgba(66, 153, 225, 0.4);
    }

    .page-header-bubbles {
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 90 90"><circle cx="20" cy="20" r="3" fill="white" opacity="0.4"/><circle cx="60" cy="60" r="2.5" fill="white" opacity="0.35"/><circle cx="35" cy="10" r="2.2" fill="white" opacity="0.32"/><circle cx="10" cy="50" r="3.2" fill="white" opacity="0.42"/><circle cx="70" cy="25" r="2" fill="white" opacity="0.3"/><circle cx="25" cy="70" r="2.8" fill="white" opacity="0.38"/></svg>');
        pointer-events: none;
        opacity: 0.6;
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