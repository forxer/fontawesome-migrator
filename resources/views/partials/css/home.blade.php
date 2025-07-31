<style>
    /* Hero Section avec animations des bulles */
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

    .hero-section::before,
    .hero-section::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;
    }

    .hero-section::before {
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><defs><pattern id="bubbles1" width="200" height="200" patternUnits="userSpaceOnUse"><circle cx="30" cy="30" r="3" fill="white" opacity="0.2"/><circle cx="130" cy="130" r="2.5" fill="white" opacity="0.15"/><circle cx="80" cy="20" r="2" fill="white" opacity="0.1"/><circle cx="20" cy="110" r="3.5" fill="white" opacity="0.25"/><circle cx="160" cy="60" r="2.2" fill="white" opacity="0.12"/><circle cx="60" cy="160" r="2.8" fill="white" opacity="0.18"/><circle cx="110" cy="40" r="1.8" fill="white" opacity="0.08"/><circle cx="40" cy="80" r="3.2" fill="white" opacity="0.22"/><circle cx="150" cy="25" r="2" fill="white" opacity="0.1"/><circle cx="25" cy="150" r="2.5" fill="white" opacity="0.15"/><circle cx="100" cy="100" r="2.2" fill="white" opacity="0.12"/><circle cx="180" cy="110" r="3" fill="white" opacity="0.2"/></pattern></defs><rect width="200" height="200" fill="url(%23bubbles1)"/></svg>');
        animation: bubbleFloat 15s ease-in-out infinite;
    }

    .hero-section::after {
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 150 150"><defs><pattern id="bubbles2" width="150" height="150" patternUnits="userSpaceOnUse"><circle cx="25" cy="50" r="2" fill="white" opacity="0.1"/><circle cx="100" cy="100" r="1.5" fill="white" opacity="0.08"/><circle cx="60" cy="15" r="1.2" fill="white" opacity="0.06"/><circle cx="15" cy="90" r="2.5" fill="white" opacity="0.12"/><circle cx="120" cy="45" r="1.8" fill="white" opacity="0.09"/><circle cx="45" cy="120" r="2.2" fill="white" opacity="0.11"/><circle cx="85" cy="30" r="1.5" fill="white" opacity="0.07"/><circle cx="30" cy="65" r="2.8" fill="white" opacity="0.14"/></pattern></defs><rect width="150" height="150" fill="url(%23bubbles2)"/></svg>');
        animation: bubbleFloat 20s ease-in-out infinite reverse;
        animation-delay: -4s;
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    .hero-icon {
        font-size: 4rem;
        margin-bottom: 20px;
        animation: rotate 20s linear infinite;
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

    /* Animations */
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes bubbleFloat {
        0%, 100% { 
            transform: translate3d(0, 0, 0);
        }
        20% { 
            transform: translate3d(-15px, -20px, 0);
        }
        40% { 
            transform: translate3d(10px, -40px, 0);
        }
        60% { 
            transform: translate3d(-8px, -25px, 0);
        }
        80% { 
            transform: translate3d(12px, -10px, 0);
        }
    }

    @keyframes bubbleRise {
        0% {
            transform: translateY(0) translateX(0);
            opacity: 0.2;
        }
        20% {
            opacity: 0.25;
        }
        100% {
            transform: translateY(calc(-100vh - 100px)) translateX(var(--sway, 0px));
            opacity: 0.25;
        }
    }

    /* Bulles individuelles animées */
    .bubble {
        position: absolute;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 50%;
        pointer-events: none;
        bottom: -50px;
        animation: bubbleRise linear forwards;
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