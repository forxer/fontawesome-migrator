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

            // Taille aléatoire dans la catégorie
            const size = Math.random() * (config.sizes[sizeCategory].max - config.sizes[sizeCategory].min) + config.sizes[sizeCategory].min;
            bubble.style.width = size + 'px';
            bubble.style.height = size + 'px';

            // Position horizontale aléatoire
            bubble.style.left = Math.random() * 85 + 5 + '%';

            // Vitesse selon la taille
            const speed = Math.random() * (config.speeds[sizeCategory].max - config.speeds[sizeCategory].min) + config.speeds[sizeCategory].min;
            bubble.style.animationDuration = speed + 's';

            bubblesContainer.appendChild(bubble);

            // Supprimer la bulle après l'animation
            setTimeout(() => {
                if (bubble.parentNode) {
                    bubble.remove();
                }
            }, speed * 1000);
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

    // Auto-initialisation
    document.addEventListener('DOMContentLoaded', function() {
        // Hero section
        const heroElement = document.querySelector('.hero-section.with-bubbles');
        if (heroElement) {
            initBubbles('.hero-section.with-bubbles');
        }

        // Page headers
        const pageHeaderElement = document.querySelector('.page-header.with-bubbles');
        if (pageHeaderElement) {
            initBubbles('.page-header.with-bubbles');
        }
    });
</script>