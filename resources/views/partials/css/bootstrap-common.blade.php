<style>
    /* CSS commun Bootstrap pour toutes les pages */

    /* Transitions des cards */
    .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    /* Action cards avec effet hover plus prononcé */
    .action-card-hover {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .action-card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Tooltips Bootstrap */
    [data-bs-toggle="tooltip"] {
        cursor: help;
    }

    /* Amélioration des badges */
    .badge {
        font-size: 0.75em;
    }

    /* Code blocks dans les pages */
    code.command-block {
        background: var(--bs-dark);
        color: var(--bs-light);
        padding: 0.75rem 1rem;
        border-radius: var(--bs-border-radius);
        font-family: 'SF Mono', Monaco, 'Cascadia Code', 'Roboto Mono', Consolas, monospace;
        font-size: 0.9rem;
        display: block;
        overflow-x: auto;
    }

    /* Spinner pour les boutons de chargement */
    .spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinner-animation 0.75s linear infinite;
    }

    @keyframes spinner-animation {
        to {
            transform: rotate(360deg);
        }
    }

    /* Cards avec bordure colorée pour les états */
    .card.border-primary-subtle {
        border-left: 4px solid var(--bs-primary);
    }

    .card.border-success-subtle {
        border-left: 4px solid var(--bs-success);
    }

    .card.border-warning-subtle {
        border-left: 4px solid var(--bs-warning);
    }

    /* Bouton retour en haut */
    .back-to-top-btn {
        /* Styles Bootstrap intégrés */
        display: inline-block;
        padding: 0.375rem 0.75rem;
        margin-bottom: 0;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        text-align: center;
        text-decoration: none;
        vertical-align: middle;
        cursor: pointer;
        user-select: none;
        background-color: var(--bs-primary);
        border: 1px solid var(--bs-primary);
        border-radius: 50%;
        color: var(--bs-white);

        /* Position */
        position: fixed;
        bottom: 0;
        right: 0;
        margin: 1.5rem;

        /* Dimensions */
        width: 50px !important;
        height: 50px !important;

        /* Animation et visibilité */
        opacity: 0;
        transform: translateY(100px);
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .back-to-top-btn:hover {
        background-color: var(--bs-primary);
        border-color: var(--bs-primary);
        color: var(--bs-white);
        transform: translateY(100px) scale(1.05);
    }

    .back-to-top-btn.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .back-to-top-btn.visible:hover {
        transform: translateY(0) scale(1.05);
    }

    /* ========================================
     * COMPOSANTS MÉTIER MUTUALISÉS
     * ========================================*/

    /* Liens de navigation avec icône */
    .nav-link-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    /* Titres de section avec icône */
    .section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .section-title.section-title-lg {
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .section-title.section-title-sm {
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
    }

    /* Cards statistiques uniformisées */
    .stat-card-bootstrap {
        text-align: center;
        height: 100%;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card-bootstrap:hover {
        transform: translateY(-2px);
    }

    .stat-card-bootstrap.active {
        border-left: 4px solid var(--bs-primary);
    }

    .stat-card-bootstrap .stat-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .stat-card-bootstrap .stat-number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.25rem;
    }

    .stat-card-bootstrap .stat-label {
        color: var(--bs-secondary);
        font-size: 0.9rem;
    }

    .stat-card-bootstrap .stat-footer {
        background: var(--bs-primary);
        background: linear-gradient(90deg, var(--bs-primary) 0%, rgba(var(--bs-primary-rgb), 0.1) 100%);
        border: 0;
        height: 4px;
    }

    /* Cards d'actions avec hover prononcé */
    .action-card {
        border: 0;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
    }

    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .action-card .action-icon {
        font-size: 3rem;
        color: var(--bs-primary);
        margin-bottom: 1rem;
    }

    /* Cards de migrations */
    .entity-card {
        height: 100%;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .entity-card:hover {
        transform: translateY(-2px);
    }

    .entity-card .entity-header {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .entity-card .entity-icon {
        font-size: 1.5rem;
        color: var(--bs-primary);
    }

    .entity-card .entity-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        text-align: center;
    }

    .entity-card .entity-meta-item {
        border: 1px solid var(--bs-border-color);
        border-radius: var(--bs-border-radius);
        padding: 0.5rem;
    }

    .entity-card .entity-actions {
        background: var(--bs-light);
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    /* Badges de statut uniformisés */
    .status-badge-dry-run {
        background-color: var(--bs-warning);
        color: var(--bs-dark);
    }

    .status-badge-real {
        background-color: var(--bs-success);
        color: var(--bs-white);
    }

    .status-badge-session {
        background-color: var(--bs-info);
        color: var(--bs-white);
    }

    /* État vide réutilisable */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }

    .empty-state .empty-icon {
        font-size: 4rem;
        color: var(--bs-secondary);
        margin-bottom: 1.5rem;
    }

    .empty-state .empty-title {
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }

    .empty-state .empty-description {
        color: var(--bs-secondary);
        margin-bottom: 1.5rem;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    .empty-state .empty-command {
        background: var(--bs-dark);
        color: var(--bs-light);
        padding: 0.75rem 1rem;
        border-radius: var(--bs-border-radius);
        display: inline-block;
        font-family: var(--bs-font-monospace);
        margin-bottom: 1rem;
    }

    .empty-state .empty-hint {
        color: var(--bs-secondary);
        font-size: 0.875rem;
    }

    /* Liste d'activité récente */
    .activity-list .activity-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        border: 0;
        transition: background-color 0.2s ease;
    }

    .activity-list .activity-item:hover {
        background-color: var(--bs-light);
    }

    .activity-list .activity-icon {
        font-size: 1.25rem;
        color: var(--bs-primary);
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .activity-list .activity-content {
        flex-grow: 1;
        min-width: 0;
    }

    .activity-list .activity-title {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .activity-list .activity-meta {
        color: var(--bs-secondary);
        font-size: 0.875rem;
    }

    .activity-list .activity-badge {
        background: var(--bs-light);
        color: var(--bs-secondary);
        flex-shrink: 0;
    }

    /* Améliorations btn-group responsive */
    .btn-group.flex-wrap {
        flex-wrap: wrap;
    }

    /* Amélioration responsive pour btn-group sur mobile */
    @media (max-width: 576px) {
        .btn-group {
            flex-direction: column;
            width: 100%;
        }

        .btn-group .btn {
            border-radius: var(--bs-border-radius) !important;
            margin-bottom: 0.25rem;
        }

        .btn-group .btn:last-child {
            margin-bottom: 0;
        }

        /* Exception pour les btn-group qui doivent rester horizontaux */
        .btn-group.stay-horizontal {
            flex-direction: row;
            width: auto;
        }

        .btn-group.stay-horizontal .btn {
            margin-bottom: 0;
            border-radius: 0;
        }

        .btn-group.stay-horizontal .btn:first-child {
            border-top-left-radius: var(--bs-border-radius);
            border-bottom-left-radius: var(--bs-border-radius);
        }

        .btn-group.stay-horizontal .btn:last-child {
            border-top-right-radius: var(--bs-border-radius);
            border-bottom-right-radius: var(--bs-border-radius);
        }
    }
</style>