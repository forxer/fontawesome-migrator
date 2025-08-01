<style>
    /* Styles spécifiques à la page de détail d'un rapport de migration - nettoyé pour Bootstrap */

    /* Styles spécifiques pour la timeline qui ne sont pas couverts par Bootstrap */
    .timeline-container {
        position: relative;
        margin: var(--spacing-xl) 0;
        padding: var(--spacing-lg) 0 var(--spacing-lg) 50px;
        background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
        border-radius: var(--radius-lg);
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .timeline-container::before {
        content: '';
        position: absolute;
        left: 25px;
        top: var(--spacing-lg);
        bottom: var(--spacing-lg);
        width: 3px;
        background: linear-gradient(
            to bottom, 
            var(--primary-color) 0%, 
            var(--secondary-color) 50%, 
            var(--success-color) 100%
        );
        border-radius: 2px;
        box-shadow: 0 0 10px rgba(66, 153, 225, 0.3);
    }

    .timeline-item {
        position: relative;
        margin-bottom: var(--spacing-xl);
        background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
        border-radius: var(--radius-lg);
        padding: var(--spacing-xl);
        box-shadow: 
            0 4px 6px -1px rgba(0, 0, 0, 0.1),
            0 2px 4px -1px rgba(0, 0, 0, 0.06);
        border: 1px solid transparent;
        background-clip: padding-box;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .timeline-item::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
    }

    .timeline-item:hover {
        box-shadow: 
            0 20px 25px -5px rgba(0, 0, 0, 0.1),
            0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transform: translateX(8px) translateY(-2px);
        border-color: rgba(66, 153, 225, 0.2);
    }

    .timeline-item:hover::after {
        transform: scaleX(1);
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -38px;
        top: 28px;
        width: 16px;
        height: 16px;
        background: radial-gradient(circle, var(--primary-color) 0%, var(--primary-hover) 100%);
        border: 4px solid white;
        border-radius: 50%;
        box-shadow: 
            0 0 0 3px var(--primary-color),
            0 4px 8px rgba(66, 153, 225, 0.3);
        z-index: 2;
        transition: all 0.3s ease;
    }

    .timeline-item:hover::before {
        transform: scale(1.2);
        box-shadow: 
            0 0 0 3px var(--primary-color),
            0 6px 12px rgba(66, 153, 225, 0.4);
    }

    .timeline-content {
        position: relative;
        z-index: 1;
    }

    .timeline-content h4 {
        margin: 0 0 var(--spacing-md) 0;
        color: var(--gray-800);
        font-size: 1.2rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        letter-spacing: -0.025em;
    }

    .timeline-content h4 i {
        margin-right: var(--spacing-md);
        padding: 10px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: var(--radius-md);
        font-size: 1rem;
        box-shadow: 0 2px 4px rgba(66, 153, 225, 0.3);
        transition: transform 0.2s ease;
        min-width: 40px;
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .timeline-item:hover .timeline-content h4 i {
        transform: rotate(5deg) scale(1.05);
    }

    .timeline-content p {
        margin: var(--spacing-md) 0;
        color: var(--gray-600);
        line-height: 1.7;
        font-size: 1rem;
        font-weight: 400;
    }

    /* Responsive pour timeline */
    @media (max-width: 768px) {
        .timeline-container {
            padding: var(--spacing-md) 0 var(--spacing-md) 40px;
            margin: var(--spacing-lg) 0;
        }

        .timeline-container::before {
            left: 18px;
            width: 2px;
        }

        .timeline-item {
            padding: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }

        .timeline-item::before {
            left: -28px;
            width: 14px;
            height: 14px;
            top: 24px;
        }

        .timeline-item:hover {
            transform: translateY(-2px);
            box-shadow: 
                0 10px 15px -3px rgba(0, 0, 0, 0.1),
                0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .timeline-content h4 {
            font-size: 1.1rem;
            flex-direction: column;
            align-items: flex-start;
        }

        .timeline-content h4 i {
            margin-right: 0;
            margin-bottom: var(--spacing-sm);
            padding: 8px;
            font-size: 0.9rem;
            min-width: 36px;
            min-height: 36px;
        }
    }
</style>