<script>
    // Génération de bulles animées
    function initBubbles(containerSelector = '.page-header') {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        // Détecter le type de conteneur
        const isHeroSection = container.classList.contains('hero-section');
        
        // Créer le conteneur de bulles s'il n'existe pas
        let bubblesContainer = container.querySelector('.bubbles-container');
        if (!bubblesContainer) {
            bubblesContainer = document.createElement('div');
            bubblesContainer.classList.add('bubbles-container');
            container.appendChild(bubblesContainer);
        }

        // Configuration selon le contexte - différence subtile
        const config = isHeroSection ? {
            frequency: 800,          // Nouvelle bulle toutes les 800ms
            initialBubbles: 12,      // 12 bulles initiales
            sizes: {
                small: { min: 8, max: 16, weight: 0.5 },    // 50% petites
                medium: { min: 16, max: 24, weight: 0.3 },  // 30% moyennes  
                large: { min: 24, max: 32, weight: 0.2 }    // 20% grosses
            },
            speeds: {
                small: { min: 18, max: 25 },   // 18-25s
                medium: { min: 28, max: 38 },  // 28-38s
                large: { min: 40, max: 55 }    // 40-55s
            }
        } : {
            frequency: 2000,         // Nouvelle bulle toutes les 2s
            initialBubbles: 5,       // 5 bulles initiales
            sizes: {
                small: { min: 7, max: 15, weight: 0.6 },    // 60% petites
                medium: { min: 15, max: 22, weight: 0.3 },  // 30% moyennes
                large: { min: 22, max: 30, weight: 0.1 }    // 10% grosses
            },
            speeds: {
                small: { min: 22, max: 30 },   // 22-30s
                medium: { min: 32, max: 42 },  // 32-42s
                large: { min: 45, max: 60 }    // 45-60s
            }
        };

        // Fonction pour créer une bulle
        function createBubble() {
            const bubble = document.createElement('div');
            bubble.classList.add('bubble');

            // Déterminer la taille selon les poids
            const rand = Math.random();
            let sizeCategory, sizeClass;
            
            if (rand < config.sizes.small.weight) {
                sizeCategory = 'small';
                sizeClass = 'small';
            } else if (rand < config.sizes.small.weight + config.sizes.medium.weight) {
                sizeCategory = 'medium'; 
                sizeClass = 'medium';
            } else {
                sizeCategory = 'large';
                sizeClass = 'large';
            }

            bubble.classList.add(sizeClass);

            // Profondeur aléatoire pour entrelacement avec bulles statiques
            const depthRand = Math.random();
            let depthMultiplier, speedMultiplier;
            
            if (depthRand < 0.3) {
                // 30% des bulles passent derrière (z-index -1)
                bubble.style.zIndex = '-1';
                bubble.style.opacity = '0.25'; // Beaucoup plus discrètes
                bubble.classList.add('behind-static');
                depthMultiplier = 0.6;  // 40% plus petites (très lointaines)
                speedMultiplier = 1.5;  // 50% plus lentes (effet parallaxe fort)
            } else if (depthRand < 0.7) {
                // 40% des bulles au niveau des statiques (z-index 0)
                bubble.style.zIndex = '0';
                bubble.style.opacity = '0.65';
                bubble.classList.add('with-static');
                depthMultiplier = 0.9;  // Légèrement plus petites
                speedMultiplier = 1.1;  // Légèrement plus lentes
            } else {
                // 30% des bulles devant (z-index 1)
                bubble.style.zIndex = '1';
                bubble.style.opacity = '1.0'; // Très visibles
                bubble.classList.add('front-static');
                depthMultiplier = 1.4;  // 40% plus grandes (très proches)
                speedMultiplier = 0.7;  // 30% plus rapides (mouvement évident)
            }

            // Taille ajustée selon la profondeur
            const baseSize = Math.random() * (config.sizes[sizeCategory].max - config.sizes[sizeCategory].min) + config.sizes[sizeCategory].min;
            const adjustedSize = baseSize * depthMultiplier;
            bubble.style.width = adjustedSize + 'px';
            bubble.style.height = adjustedSize + 'px';

            // Position horizontale aléatoire
            bubble.style.left = Math.random() * 85 + 5 + '%';

            // Vitesse ajustée selon la profondeur
            const baseSpeed = Math.random() * (config.speeds[sizeCategory].max - config.speeds[sizeCategory].min) + config.speeds[sizeCategory].min;
            const adjustedSpeed = baseSpeed * speedMultiplier;
            bubble.style.animationDuration = adjustedSpeed + 's';

            // Trajectoire aléatoire parmi 6 possibilités
            const trajectories = ['bubbleRise', 'bubbleRise2', 'bubbleRise3', 'bubbleRise4', 'bubbleRise5', 'bubbleRise6'];
            const randomTrajectory = trajectories[Math.floor(Math.random() * trajectories.length)];
            bubble.style.animationName = randomTrajectory;

            bubblesContainer.appendChild(bubble);

            // Supprimer la bulle après l'animation
            setTimeout(() => {
                if (bubble.parentNode) {
                    bubble.remove();
                }
            }, adjustedSpeed * 1000);
        }

        // Créer des bulles initiales avec délais échelonnés
        for (let i = 0; i < config.initialBubbles; i++) {
            setTimeout(() => {
                createBubble();
            }, i * 200);
        }

        // Génération continue
        const bubbleInterval = setInterval(createBubble, config.frequency);

        // Retourner fonction de nettoyage
        return () => clearInterval(bubbleInterval);
    }

    // Créer des bulles statiques DOM réelles pour le premier plan
    function createStaticFrontBubbles(containerSelector) {
        const container = document.querySelector(containerSelector);
        if (!container) return;

        // Créer quelques bulles statiques du premier plan
        const staticBubbles = [
            { left: '15%', top: '20%', size: 18 },
            { left: '75%', top: '35%', size: 22 },
            { left: '45%', top: '70%', size: 16 },
            { left: '85%', top: '15%', size: 20 },
            { left: '25%', top: '80%', size: 14 },
            { left: '65%', top: '25%', size: 24 }
        ];

        staticBubbles.forEach((bubbleData, index) => {
            const staticBubble = document.createElement('div');
            staticBubble.classList.add('static-bubble-front');
            staticBubble.style.left = bubbleData.left;
            staticBubble.style.top = bubbleData.top;
            staticBubble.style.width = bubbleData.size + 'px';
            staticBubble.style.height = bubbleData.size + 'px';
            staticBubble.style.animationDelay = (index * 2) + 's';
            
            container.appendChild(staticBubble);
        });
    }

    // Auto-initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Hero section
        const heroElement = document.querySelector('.hero-section.with-bubbles');
        if (heroElement) {
            initBubbles('.hero-section.with-bubbles');
            createStaticFrontBubbles('.hero-section.with-bubbles');
        }

        // Page headers
        const pageHeaderElement = document.querySelector('.page-header.with-bubbles');
        if (pageHeaderElement) {
            initBubbles('.page-header.with-bubbles');
            createStaticFrontBubbles('.page-header.with-bubbles');
        }
    });
</script>