<style>
    /* Conteneur pour les bulles animées */
    .bubbles-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        pointer-events: none;
    }

    /* Animation naturelle de montée avec oscillations douces */
    @keyframes bubbleRise {
        0% {
            transform: translateY(0) translateX(0);
            opacity: 0.8;
        }
        8% {
            transform: translateY(-8vh) translateX(12px);
            opacity: 0.85;
        }
        18% {
            transform: translateY(-18vh) translateX(28px);
            opacity: 0.9;
        }
        25% {
            transform: translateY(-25vh) translateX(22px);
            opacity: 0.85;
        }
        35% {
            transform: translateY(-35vh) translateX(-5px);
            opacity: 0.8;
        }
        45% {
            transform: translateY(-45vh) translateX(-22px);
            opacity: 0.75;
        }
        52% {
            transform: translateY(-52vh) translateX(-18px);
            opacity: 0.7;
        }
        62% {
            transform: translateY(-62vh) translateX(8px);
            opacity: 0.65;
        }
        72% {
            transform: translateY(-72vh) translateX(25px);
            opacity: 0.6;
        }
        78% {
            transform: translateY(-78vh) translateX(20px);
            opacity: 0.5;
        }
        88% {
            transform: translateY(-88vh) translateX(-8px);
            opacity: 0.3;
        }
        95% {
            transform: translateY(-95vh) translateX(-5px);
            opacity: 0.1;
        }
        100% {
            transform: translateY(-105vh) translateX(0);
            opacity: 0;
        }
    }

    /* Trajectoire alternative 1 - oscillations plus amples */
    @keyframes bubbleRise2 {
        0% {
            transform: translateY(0) translateX(0);
            opacity: 0.8;
        }
        10% {
            transform: translateY(-10vh) translateX(-15px);
            opacity: 0.85;
        }
        22% {
            transform: translateY(-22vh) translateX(-35px);
            opacity: 0.9;
        }
        35% {
            transform: translateY(-35vh) translateX(-25px);
            opacity: 0.85;
        }
        48% {
            transform: translateY(-48vh) translateX(20px);
            opacity: 0.8;
        }
        60% {
            transform: translateY(-60vh) translateX(40px);
            opacity: 0.7;
        }
        72% {
            transform: translateY(-72vh) translateX(30px);
            opacity: 0.6;
        }
        82% {
            transform: translateY(-82vh) translateX(-10px);
            opacity: 0.45;
        }
        92% {
            transform: translateY(-92vh) translateX(-20px);
            opacity: 0.25;
        }
        100% {
            transform: translateY(-105vh) translateX(-15px);
            opacity: 0;
        }
    }

    /* Trajectoire alternative 2 - oscillations douces et courtes */
    @keyframes bubbleRise3 {
        0% {
            transform: translateY(0) translateX(0);
            opacity: 0.8;
        }
        12% {
            transform: translateY(-12vh) translateX(8px);
            opacity: 0.85;
        }
        28% {
            transform: translateY(-28vh) translateX(18px);
            opacity: 0.9;
        }
        40% {
            transform: translateY(-40vh) translateX(12px);
            opacity: 0.85;
        }
        55% {
            transform: translateY(-55vh) translateX(-8px);
            opacity: 0.8;
        }
        68% {
            transform: translateY(-68vh) translateX(-15px);
            opacity: 0.7;
        }
        78% {
            transform: translateY(-78vh) translateX(-12px);
            opacity: 0.6;
        }
        88% {
            transform: translateY(-88vh) translateX(5px);
            opacity: 0.4;
        }
        96% {
            transform: translateY(-96vh) translateX(8px);
            opacity: 0.15;
        }
        100% {
            transform: translateY(-105vh) translateX(5px);
            opacity: 0;
        }
    }

    /* Bulles animées - plus subtiles */
    .bubble {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.15);
        pointer-events: none;
        bottom: -50px;
        animation: bubbleRise linear forwards;
    }

    /* Bulles petites - discrètes */
    .bubble.small {
        background: rgba(255, 255, 255, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Bulles moyennes - très subtiles */
    .bubble.medium {
        background: rgba(255, 255, 255, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.12);
    }

    /* Bulles grosses - quasiment transparentes */
    .bubble.large {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    /* Motifs de bulles statiques en arrière-plan - plus visibles */
    .bubbles-pattern {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;
        opacity: 0.6;
    }

    /* Densité augmentée pour la hero section */
    .hero-section .bubbles-pattern {
        opacity: 0.8;
    }

    .bubbles-pattern::before {
        content: '';
        position: absolute;
        top: -50px;
        left: -50px;
        right: -50px;
        bottom: -50px;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><defs><pattern id="bubbles1" width="200" height="200" patternUnits="userSpaceOnUse"><circle cx="30" cy="30" r="4" fill="white" opacity="0.3"/><circle cx="130" cy="130" r="3.5" fill="white" opacity="0.25"/><circle cx="80" cy="20" r="3" fill="white" opacity="0.2"/><circle cx="20" cy="110" r="5" fill="white" opacity="0.35"/><circle cx="160" cy="60" r="3.2" fill="white" opacity="0.22"/><circle cx="60" cy="160" r="4.2" fill="white" opacity="0.28"/><circle cx="100" cy="80" r="2.5" fill="white" opacity="0.18"/><circle cx="170" cy="170" r="3.8" fill="white" opacity="0.26"/><circle cx="40" cy="185" r="3" fill="white" opacity="0.22"/><circle cx="150" cy="190" r="2.5" fill="white" opacity="0.18"/></pattern></defs><rect width="200" height="200" fill="url(%23bubbles1)"/></svg>');
        animation: floatPattern1 20s ease-in-out infinite;
    }

    /* Motif plus dense pour la hero section */
    .hero-section .bubbles-pattern::before {
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><defs><pattern id="bubbles1-hero" width="200" height="200" patternUnits="userSpaceOnUse"><circle cx="30" cy="30" r="4" fill="white" opacity="0.3"/><circle cx="130" cy="130" r="3.5" fill="white" opacity="0.25"/><circle cx="80" cy="20" r="3" fill="white" opacity="0.2"/><circle cx="20" cy="110" r="5" fill="white" opacity="0.35"/><circle cx="160" cy="60" r="3.2" fill="white" opacity="0.22"/><circle cx="60" cy="160" r="4.2" fill="white" opacity="0.28"/><circle cx="100" cy="80" r="2.5" fill="white" opacity="0.18"/><circle cx="170" cy="170" r="3.8" fill="white" opacity="0.26"/><circle cx="40" cy="185" r="3" fill="white" opacity="0.22"/><circle cx="150" cy="190" r="2.5" fill="white" opacity="0.18"/><circle cx="70" cy="50" r="2.8" fill="white" opacity="0.20"/><circle cx="180" cy="40" r="3.5" fill="white" opacity="0.24"/><circle cx="45" cy="90" r="2.2" fill="white" opacity="0.16"/><circle cx="120" cy="25" r="2.8" fill="white" opacity="0.19"/><circle cx="90" cy="150" r="3.2" fill="white" opacity="0.23"/><circle cx="15" cy="75" r="2.5" fill="white" opacity="0.17"/></pattern></defs><rect width="200" height="200" fill="url(%23bubbles1-hero)"/></svg>');
    }

    .bubbles-pattern::after {
        content: '';
        position: absolute;
        top: -50px;
        left: -50px;
        right: -50px;
        bottom: -50px;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 150 150"><defs><pattern id="bubbles2" width="150" height="150" patternUnits="userSpaceOnUse"><circle cx="25" cy="50" r="3" fill="white" opacity="0.2"/><circle cx="100" cy="100" r="2.5" fill="white" opacity="0.16"/><circle cx="60" cy="15" r="2.2" fill="white" opacity="0.12"/><circle cx="15" cy="90" r="4" fill="white" opacity="0.24"/><circle cx="120" cy="45" r="2.8" fill="white" opacity="0.18"/><circle cx="75" cy="125" r="3.5" fill="white" opacity="0.22"/><circle cx="30" cy="140" r="2.5" fill="white" opacity="0.16"/><circle cx="110" cy="135" r="2.8" fill="white" opacity="0.18"/></pattern></defs><rect width="150" height="150" fill="url(%23bubbles2)"/></svg>');
        animation: floatPattern2 25s ease-in-out infinite;
        animation-delay: -5s;
    }

    /* Deuxième motif plus dense pour la hero section */
    .hero-section .bubbles-pattern::after {
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 150 150"><defs><pattern id="bubbles2-hero" width="150" height="150" patternUnits="userSpaceOnUse"><circle cx="25" cy="50" r="3" fill="white" opacity="0.2"/><circle cx="100" cy="100" r="2.5" fill="white" opacity="0.16"/><circle cx="60" cy="15" r="2.2" fill="white" opacity="0.12"/><circle cx="15" cy="90" r="4" fill="white" opacity="0.24"/><circle cx="120" cy="45" r="2.8" fill="white" opacity="0.18"/><circle cx="75" cy="125" r="3.5" fill="white" opacity="0.22"/><circle cx="30" cy="140" r="2.5" fill="white" opacity="0.16"/><circle cx="110" cy="135" r="2.8" fill="white" opacity="0.18"/><circle cx="50" cy="80" r="2.2" fill="white" opacity="0.14"/><circle cx="135" cy="20" r="2.5" fill="white" opacity="0.16"/><circle cx="80" cy="35" r="2" fill="white" opacity="0.12"/><circle cx="40" cy="25" r="2.8" fill="white" opacity="0.18"/><circle cx="140" cy="105" r="2.3" fill="white" opacity="0.15"/><circle cx="20" cy="120" r="2.6" fill="white" opacity="0.17"/></pattern></defs><rect width="150" height="150" fill="url(%23bubbles2-hero)"/></svg>');
    }

    /* Animations de flottement aléatoires pour les motifs de fond */
    @keyframes floatPattern1 {
        0%, 100% {
            transform: translate3d(0, 0, 0);
        }
        15% {
            transform: translate3d(-18px, -22px, 0);
        }
        35% {
            transform: translate3d(12px, -35px, 0);
        }
        55% {
            transform: translate3d(-8px, -28px, 0);
        }
        75% {
            transform: translate3d(20px, -15px, 0);
        }
        90% {
            transform: translate3d(-5px, -18px, 0);
        }
    }

    @keyframes floatPattern2 {
        0%, 100% {
            transform: translate3d(0, 0, 0);
        }
        12% {
            transform: translate3d(15px, -18px, 0);
        }
        28% {
            transform: translate3d(-22px, -32px, 0);
        }
        45% {
            transform: translate3d(8px, -25px, 0);
        }
        68% {
            transform: translate3d(-12px, -40px, 0);
        }
        85% {
            transform: translate3d(25px, -12px, 0);
        }
    }

    /* Trajectoires supplémentaires pour plus de variété */
    
    /* Trajectoire 4 - montée presque droite */
    @keyframes bubbleRise4 {
        0% {
            transform: translateY(0) translateX(0);
            opacity: 0.8;
        }
        15% {
            transform: translateY(-15vh) translateX(3px);
            opacity: 0.85;
        }
        35% {
            transform: translateY(-35vh) translateX(-2px);
            opacity: 0.9;
        }
        55% {
            transform: translateY(-55vh) translateX(4px);
            opacity: 0.8;
        }
        75% {
            transform: translateY(-75vh) translateX(-1px);
            opacity: 0.6;
        }
        90% {
            transform: translateY(-90vh) translateX(2px);
            opacity: 0.3;
        }
        100% {
            transform: translateY(-105vh) translateX(0);
            opacity: 0;
        }
    }

    /* Trajectoire 5 - zigzag prononcé mais adouci */
    @keyframes bubbleRise5 {
        0% {
            transform: translateY(0) translateX(0);
            opacity: 0.8;
        }
        8% {
            transform: translateY(-8vh) translateX(25px);
            opacity: 0.82;
        }
        16% {
            transform: translateY(-16vh) translateX(40px);
            opacity: 0.88;
        }
        22% {
            transform: translateY(-22vh) translateX(35px);
            opacity: 0.9;
        }
        30% {
            transform: translateY(-30vh) translateX(10px);
            opacity: 0.88;
        }
        38% {
            transform: translateY(-38vh) translateX(-20px);
            opacity: 0.85;
        }
        46% {
            transform: translateY(-46vh) translateX(-35px);
            opacity: 0.82;
        }
        54% {
            transform: translateY(-54vh) translateX(-30px);
            opacity: 0.8;
        }
        62% {
            transform: translateY(-62vh) translateX(-10px);
            opacity: 0.75;
        }
        70% {
            transform: translateY(-70vh) translateX(15px);
            opacity: 0.7;
        }
        78% {
            transform: translateY(-78vh) translateX(25px);
            opacity: 0.6;
        }
        86% {
            transform: translateY(-86vh) translateX(15px);
            opacity: 0.4;
        }
        94% {
            transform: translateY(-94vh) translateX(5px);
            opacity: 0.2;
        }
        100% {
            transform: translateY(-105vh) translateX(0);
            opacity: 0;
        }
    }

    /* Trajectoire 6 - spirale douce */
    @keyframes bubbleRise6 {
        0% {
            transform: translateY(0) translateX(0);
            opacity: 0.8;
        }
        14% {
            transform: translateY(-14vh) translateX(20px);
            opacity: 0.85;
        }
        28% {
            transform: translateY(-28vh) translateX(10px);
            opacity: 0.9;
        }
        42% {
            transform: translateY(-42vh) translateX(-15px);
            opacity: 0.85;
        }
        56% {
            transform: translateY(-56vh) translateX(-25px);
            opacity: 0.8;
        }
        70% {
            transform: translateY(-70vh) translateX(-10px);
            opacity: 0.65;
        }
        84% {
            transform: translateY(-84vh) translateX(15px);
            opacity: 0.4;
        }
        100% {
            transform: translateY(-105vh) translateX(10px);
            opacity: 0;
        }
    }
</style>