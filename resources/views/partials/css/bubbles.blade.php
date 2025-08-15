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
        /* Pas de z-index pour permettre aux bulles enfants d'avoir leurs propres z-index relatifs */
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

    /* Bulles animées - réalistes avec relief */
    .bubble {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.8) 0%, rgba(255, 255, 255, 0.4) 30%, rgba(255, 255, 255, 0.1) 60%, rgba(255, 255, 255, 0.3) 100%);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: inset 2px 2px 4px rgba(255, 255, 255, 0.3), inset -2px -2px 4px rgba(0, 0, 0, 0.1);
        pointer-events: none;
        bottom: -50px;
        animation: bubbleRise linear forwards;
    }

    /* Bulles petites - discrètes avec relief */
    .bubble.small {
        background: radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.6) 25%, rgba(255, 255, 255, 0.2) 55%, rgba(255, 255, 255, 0.4) 100%);
        border: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: inset 1px 1px 2px rgba(255, 255, 255, 0.4), inset -1px -1px 2px rgba(0, 0, 0, 0.08);
    }

    /* Bulles moyennes - subtiles avec relief */
    .bubble.medium {
        background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.6) 0%, rgba(255, 255, 255, 0.3) 30%, rgba(255, 255, 255, 0.08) 60%, rgba(255, 255, 255, 0.2) 100%);
        border: 1px solid rgba(255, 255, 255, 0.12);
        box-shadow: inset 1.5px 1.5px 3px rgba(255, 255, 255, 0.25), inset -1.5px -1.5px 3px rgba(0, 0, 0, 0.06);
    }

    /* Bulles grosses - transparentes avec relief */
    .bubble.large {
        background: radial-gradient(circle at 35% 35%, rgba(255, 255, 255, 0.4) 0%, rgba(255, 255, 255, 0.2) 35%, rgba(255, 255, 255, 0.05) 65%, rgba(255, 255, 255, 0.15) 100%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: inset 2px 2px 4px rgba(255, 255, 255, 0.15), inset -2px -2px 4px rgba(0, 0, 0, 0.04);
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
        z-index: 0; /* Niveau de référence - bulles statiques normales */
    }

    /* Layer arrière pour bulles statiques discrètes */
    .bubbles-pattern-back {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;
        opacity: 0.3;
        z-index: -1; /* Derrière tout */
    }

    /* Layer avant pour bulles statiques visibles */
    .bubbles-pattern-front {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        pointer-events: none;
        opacity: 0.4;
        z-index: 2; /* Devant tout */
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
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><defs><radialGradient id="bubbleGrad1" cx="0.3" cy="0.3"><stop offset="0%" stop-color="white" stop-opacity="0.4"/><stop offset="60%" stop-color="white" stop-opacity="0.2"/><stop offset="100%" stop-color="white" stop-opacity="0.1"/></radialGradient><radialGradient id="bubbleGrad2" cx="0.25" cy="0.25"><stop offset="0%" stop-color="white" stop-opacity="0.35"/><stop offset="70%" stop-color="white" stop-opacity="0.15"/><stop offset="100%" stop-color="white" stop-opacity="0.08"/></radialGradient><radialGradient id="bubbleGrad3" cx="0.35" cy="0.35"><stop offset="0%" stop-color="white" stop-opacity="0.3"/><stop offset="80%" stop-color="white" stop-opacity="0.12"/><stop offset="100%" stop-color="white" stop-opacity="0.05"/></radialGradient><radialGradient id="bubbleGradFaint" cx="0.3" cy="0.3"><stop offset="0%" stop-color="white" stop-opacity="0.2"/><stop offset="70%" stop-color="white" stop-opacity="0.08"/><stop offset="100%" stop-color="white" stop-opacity="0.03"/></radialGradient><radialGradient id="bubbleGradBright" cx="0.28" cy="0.28"><stop offset="0%" stop-color="white" stop-opacity="0.5"/><stop offset="55%" stop-color="white" stop-opacity="0.25"/><stop offset="100%" stop-color="white" stop-opacity="0.12"/></radialGradient><pattern id="bubbles1" width="200" height="200" patternUnits="userSpaceOnUse"><circle cx="30" cy="30" r="4" fill="url(%23bubbleGradBright)"/><circle cx="130" cy="130" r="3.5" fill="url(%23bubbleGrad2)"/><circle cx="80" cy="20" r="3" fill="url(%23bubbleGradFaint)"/><circle cx="20" cy="110" r="5" fill="url(%23bubbleGrad1)"/><circle cx="160" cy="60" r="3.2" fill="url(%23bubbleGradFaint)"/><circle cx="60" cy="160" r="4.2" fill="url(%23bubbleGradBright)"/><circle cx="100" cy="80" r="2.5" fill="url(%23bubbleGrad3)"/><circle cx="170" cy="170" r="3.8" fill="url(%23bubbleGrad2)"/><circle cx="40" cy="185" r="3" fill="url(%23bubbleGradFaint)"/><circle cx="150" cy="190" r="2.5" fill="url(%23bubbleGrad3)"/></pattern></defs><rect width="200" height="200" fill="url(%23bubbles1)"/></svg>');
        animation: floatPattern1 20s ease-in-out infinite;
    }

    /* Motif plus dense pour la hero section */
    .hero-section .bubbles-pattern::before {
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><defs><radialGradient id="bubbleGradHero1" cx="0.3" cy="0.3"><stop offset="0%" stop-color="white" stop-opacity="0.5"/><stop offset="60%" stop-color="white" stop-opacity="0.25"/><stop offset="100%" stop-color="white" stop-opacity="0.12"/></radialGradient><radialGradient id="bubbleGradHero2" cx="0.25" cy="0.25"><stop offset="0%" stop-color="white" stop-opacity="0.45"/><stop offset="70%" stop-color="white" stop-opacity="0.2"/><stop offset="100%" stop-color="white" stop-opacity="0.1"/></radialGradient><radialGradient id="bubbleGradHero3" cx="0.35" cy="0.35"><stop offset="0%" stop-color="white" stop-opacity="0.4"/><stop offset="80%" stop-color="white" stop-opacity="0.15"/><stop offset="100%" stop-color="white" stop-opacity="0.08"/></radialGradient><radialGradient id="bubbleGradHeroFaint" cx="0.3" cy="0.3"><stop offset="0%" stop-color="white" stop-opacity="0.25"/><stop offset="70%" stop-color="white" stop-opacity="0.1"/><stop offset="100%" stop-color="white" stop-opacity="0.04"/></radialGradient><radialGradient id="bubbleGradHeroBright" cx="0.28" cy="0.28"><stop offset="0%" stop-color="white" stop-opacity="0.6"/><stop offset="55%" stop-color="white" stop-opacity="0.3"/><stop offset="100%" stop-color="white" stop-opacity="0.15"/></radialGradient><pattern id="bubbles1-hero" width="200" height="200" patternUnits="userSpaceOnUse"><circle cx="30" cy="30" r="4" fill="url(%23bubbleGradHeroBright)"/><circle cx="130" cy="130" r="3.5" fill="url(%23bubbleGradHero2)"/><circle cx="80" cy="20" r="3" fill="url(%23bubbleGradHeroFaint)"/><circle cx="20" cy="110" r="5" fill="url(%23bubbleGradHero1)"/><circle cx="160" cy="60" r="3.2" fill="url(%23bubbleGradHeroFaint)"/><circle cx="60" cy="160" r="4.2" fill="url(%23bubbleGradHeroBright)"/><circle cx="100" cy="80" r="2.5" fill="url(%23bubbleGradHero3)"/><circle cx="170" cy="170" r="3.8" fill="url(%23bubbleGradHero2)"/><circle cx="40" cy="185" r="3" fill="url(%23bubbleGradHeroFaint)"/><circle cx="150" cy="190" r="2.5" fill="url(%23bubbleGradHero3)"/><circle cx="70" cy="50" r="2.8" fill="url(%23bubbleGradHeroFaint)"/><circle cx="180" cy="40" r="3.5" fill="url(%23bubbleGradHeroBright)"/><circle cx="45" cy="90" r="2.2" fill="url(%23bubbleGradHero3)"/><circle cx="120" cy="25" r="2.8" fill="url(%23bubbleGradHeroFaint)"/><circle cx="90" cy="150" r="3.2" fill="url(%23bubbleGradHero2)"/><circle cx="15" cy="75" r="2.5" fill="url(%23bubbleGradHero3)"/></pattern></defs><rect width="200" height="200" fill="url(%23bubbles1-hero)"/></svg>');
    }

    .bubbles-pattern::after {
        content: '';
        position: absolute;
        top: -50px;
        left: -50px;
        right: -50px;
        bottom: -50px;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 150 150"><defs><radialGradient id="bubbleGradB1" cx="0.3" cy="0.3"><stop offset="0%" stop-color="white" stop-opacity="0.3"/><stop offset="70%" stop-color="white" stop-opacity="0.15"/><stop offset="100%" stop-color="white" stop-opacity="0.08"/></radialGradient><radialGradient id="bubbleGradB2" cx="0.25" cy="0.25"><stop offset="0%" stop-color="white" stop-opacity="0.25"/><stop offset="75%" stop-color="white" stop-opacity="0.12"/><stop offset="100%" stop-color="white" stop-opacity="0.06"/></radialGradient><radialGradient id="bubbleGradB3" cx="0.35" cy="0.35"><stop offset="0%" stop-color="white" stop-opacity="0.35"/><stop offset="80%" stop-color="white" stop-opacity="0.18"/><stop offset="100%" stop-color="white" stop-opacity="0.1"/></radialGradient><radialGradient id="bubbleGradBFaint" cx="0.3" cy="0.3"><stop offset="0%" stop-color="white" stop-opacity="0.15"/><stop offset="75%" stop-color="white" stop-opacity="0.06"/><stop offset="100%" stop-color="white" stop-opacity="0.02"/></radialGradient><radialGradient id="bubbleGradBBright" cx="0.28" cy="0.28"><stop offset="0%" stop-color="white" stop-opacity="0.4"/><stop offset="60%" stop-color="white" stop-opacity="0.2"/><stop offset="100%" stop-color="white" stop-opacity="0.1"/></radialGradient><pattern id="bubbles2" width="150" height="150" patternUnits="userSpaceOnUse"><circle cx="25" cy="50" r="3" fill="url(%23bubbleGradBBright)"/><circle cx="100" cy="100" r="2.5" fill="url(%23bubbleGradB2)"/><circle cx="60" cy="15" r="2.2" fill="url(%23bubbleGradBFaint)"/><circle cx="15" cy="90" r="4" fill="url(%23bubbleGradB3)"/><circle cx="120" cy="45" r="2.8" fill="url(%23bubbleGradBFaint)"/><circle cx="75" cy="125" r="3.5" fill="url(%23bubbleGradBBright)"/><circle cx="30" cy="140" r="2.5" fill="url(%23bubbleGradB2)"/><circle cx="110" cy="135" r="2.8" fill="url(%23bubbleGradB1)"/></pattern></defs><rect width="150" height="150" fill="url(%23bubbles2)"/></svg>');
        animation: floatPattern2 25s ease-in-out infinite;
        animation-delay: -5s;
    }

    /* Deuxième motif plus dense pour la hero section */
    .hero-section .bubbles-pattern::after {
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 150 150"><defs><radialGradient id="bubbleGradBHero1" cx="0.3" cy="0.3"><stop offset="0%" stop-color="white" stop-opacity="0.4"/><stop offset="70%" stop-color="white" stop-opacity="0.2"/><stop offset="100%" stop-color="white" stop-opacity="0.12"/></radialGradient><radialGradient id="bubbleGradBHero2" cx="0.25" cy="0.25"><stop offset="0%" stop-color="white" stop-opacity="0.35"/><stop offset="75%" stop-color="white" stop-opacity="0.18"/><stop offset="100%" stop-color="white" stop-opacity="0.1"/></radialGradient><radialGradient id="bubbleGradBHero3" cx="0.35" cy="0.35"><stop offset="0%" stop-color="white" stop-opacity="0.45"/><stop offset="80%" stop-color="white" stop-opacity="0.22"/><stop offset="100%" stop-color="white" stop-opacity="0.14"/></radialGradient><radialGradient id="bubbleGradBHeroFaint" cx="0.3" cy="0.3"><stop offset="0%" stop-color="white" stop-opacity="0.2"/><stop offset="75%" stop-color="white" stop-opacity="0.08"/><stop offset="100%" stop-color="white" stop-opacity="0.03"/></radialGradient><radialGradient id="bubbleGradBHeroBright" cx="0.28" cy="0.28"><stop offset="0%" stop-color="white" stop-opacity="0.55"/><stop offset="60%" stop-color="white" stop-opacity="0.28"/><stop offset="100%" stop-color="white" stop-opacity="0.16"/></radialGradient><pattern id="bubbles2-hero" width="150" height="150" patternUnits="userSpaceOnUse"><circle cx="25" cy="50" r="3" fill="url(%23bubbleGradBHeroBright)"/><circle cx="100" cy="100" r="2.5" fill="url(%23bubbleGradBHero2)"/><circle cx="60" cy="15" r="2.2" fill="url(%23bubbleGradBHeroFaint)"/><circle cx="15" cy="90" r="4" fill="url(%23bubbleGradBHero3)"/><circle cx="120" cy="45" r="2.8" fill="url(%23bubbleGradBHeroFaint)"/><circle cx="75" cy="125" r="3.5" fill="url(%23bubbleGradBHeroBright)"/><circle cx="30" cy="140" r="2.5" fill="url(%23bubbleGradBHero2)"/><circle cx="110" cy="135" r="2.8" fill="url(%23bubbleGradBHero1)"/><circle cx="50" cy="80" r="2.2" fill="url(%23bubbleGradBHeroFaint)"/><circle cx="135" cy="20" r="2.5" fill="url(%23bubbleGradBHero2)"/><circle cx="80" cy="35" r="2" fill="url(%23bubbleGradBHeroFaint)"/><circle cx="40" cy="25" r="2.8" fill="url(%23bubbleGradBHeroBright)"/><circle cx="140" cy="105" r="2.3" fill="url(%23bubbleGradBHeroFaint)"/><circle cx="20" cy="120" r="2.6" fill="url(%23bubbleGradBHero2)"/></pattern></defs><rect width="150" height="150" fill="url(%23bubbles2-hero)"/></svg>');
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

    /* Styles pour les différentes profondeurs de bulles animées */
    .bubble.behind-static {
        /* Bulles derrière les statiques - floues et discrètes (lointaines) */
        filter: blur(1.2px);
    }

    .bubble.with-static {
        /* Bulles au niveau des statiques - légèrement floues */
        filter: blur(0.3px);
    }

    .bubble.front-static {
        /* Bulles devant les statiques - nettes et lumineuses (proches) */
        filter: brightness(1.15) blur(0px);
    }

    /* Patterns pour layer arrière (z-index -1) */
    .bubbles-pattern-back::before {
        content: '';
        position: absolute;
        top: -50px;
        left: -50px;
        right: -50px;
        bottom: -50px;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 180 180"><defs><radialGradient id="bubbleGradBack1" cx="0.3" cy="0.3"><stop offset="0%" stop-color="white" stop-opacity="0.2"/><stop offset="70%" stop-color="white" stop-opacity="0.08"/><stop offset="100%" stop-color="white" stop-opacity="0.03"/></radialGradient><radialGradient id="bubbleGradBack2" cx="0.25" cy="0.25"><stop offset="0%" stop-color="white" stop-opacity="0.15"/><stop offset="75%" stop-color="white" stop-opacity="0.06"/><stop offset="100%" stop-color="white" stop-opacity="0.02"/></radialGradient><pattern id="bubblesBack" width="180" height="180" patternUnits="userSpaceOnUse"><circle cx="40" cy="40" r="3" fill="url(%23bubbleGradBack1)"/><circle cx="120" cy="120" r="2.5" fill="url(%23bubbleGradBack2)"/><circle cx="70" cy="25" r="2.2" fill="url(%23bubbleGradBack2)"/><circle cx="25" cy="100" r="3.5" fill="url(%23bubbleGradBack1)"/><circle cx="140" cy="50" r="2.8" fill="url(%23bubbleGradBack2)"/><circle cx="90" cy="140" r="3.2" fill="url(%23bubbleGradBack1)"/></pattern></defs><rect width="180" height="180" fill="url(%23bubblesBack)"/></svg>');
        animation: floatPattern1 30s ease-in-out infinite;
    }

    /* Bulles statiques du premier plan - éléments DOM réels pour z-index correct */
    .static-bubble-front {
        position: absolute;
        border-radius: 50%;
        background: radial-gradient(circle at 28% 28%, rgba(255, 255, 255, 0.5) 0%, rgba(255, 255, 255, 0.25) 55%, rgba(255, 255, 255, 0.12) 100%);
        pointer-events: none;
        z-index: 2;
        animation: floatStatic 40s ease-in-out infinite;
    }

    @keyframes floatStatic {
        0%, 100% { transform: translate3d(0, 0, 0); }
        25% { transform: translate3d(-10px, -15px, 0); }
        50% { transform: translate3d(8px, -25px, 0); }
        75% { transform: translate3d(-5px, -10px, 0); }
    }
</style>