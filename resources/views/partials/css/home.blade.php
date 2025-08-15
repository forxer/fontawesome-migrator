<style>
    /* Hero Section - simplifié car les bulles sont maintenant dans le fichier partagé */
    .hero-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        padding: 60px 0;
        margin-bottom: 40px;
        border-radius: var(--radius-lg);
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    .hero-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        animation: rotate 5s linear infinite;
    }

    .hero-title {
        font-size: 3rem;
        font-weight: 700;
        margin: 0 0 15px 0;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .hero-subtitle {
        font-size: 1.3rem;
        margin: 0 0 20px 0;
        opacity: 0.9;
        font-weight: 300;
    }

    .hero-version {
        display: inline-block;
        background: rgba(255,255,255,0.2);
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
        backdrop-filter: blur(10px);
    }

    /* Animation rotation pour l'icône hero */
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Responsive Hero */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.2rem;
        }

        .hero-subtitle {
            font-size: 1.1rem;
        }
    }
</style>