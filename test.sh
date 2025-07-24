#!/bin/bash

set -e  # Arrêter le script en cas d'erreur

# Charger les alias de l'utilisateur (pour d-packages-exec)
if [[ -f ~/.bashrc ]]; then
    source ~/.bashrc
fi
if [[ -f ~/.bash_aliases ]]; then
    source ~/.bash_aliases
fi

echo "🧪 Test du package fontawesome-migrator"
echo "======================================="

# Vérifier que d-packages-exec php84 est disponible
echo "🔍 Test de la commande d-packages-exec php84 php --version..."
if d-packages-exec php84 php --version > /dev/null 2>&1; then
    echo "✅ d-packages-exec php84 php fonctionne"
    PHP_VERSION=$(d-packages-exec php84 php --version | head -n 1 | cut -d ' ' -f 2)
    echo "🐳 Environnement Docker avec d-packages-exec php84 détecté"
    echo "   Version PHP: $PHP_VERSION"
else
    echo "❌ Erreur: d-packages-exec php84 php n'est pas disponible"
    echo "   Assurez-vous que votre fonction d-packages-exec est définie dans ~/.bashrc"
    echo "   Test: d-packages-exec php84 php --version"
    exit 1
fi

echo ""
echo "📦 Installation des dépendances..."
d-packages-exec php84 php -r "file_exists('composer.phar') or copy('https://getcomposer.org/installer', 'composer-setup.php');" 2>/dev/null || true
d-packages-exec php84 composer install

echo ""
echo "🔍 Lancement des tests unitaires..."
if d-packages-exec php84 composer test; then
    echo "✅ Tests unitaires: RÉUSSIS"
else
    echo "❌ Tests unitaires: ÉCHEC"
    exit 1
fi

echo ""
echo "✨ Vérification du style de code..."
if d-packages-exec php84 composer pint-test; then
    echo "✅ Style de code: CONFORME"
else
    echo "⚠️  Style de code: NON CONFORME"
    echo "   Exécutez 'd-packages-exec php84 composer pint' pour corriger automatiquement"
fi

echo ""
echo "🔧 Vérification Rector (modernisation)..."
if d-packages-exec php84 composer rector-dry; then
    echo "✅ Code moderne: OK"
else
    echo "⚠️  Code moderne: AMÉLIORATIONS POSSIBLES"
    echo "   Exécutez 'd-packages-exec php84 composer rector' pour appliquer les modernisations"
fi

echo ""
echo "🚀 Test d'intégration avec Laravel..."

# Créer un dossier temporaire pour les tests
TEST_DIR="./test-integration"
if [ -d "$TEST_DIR" ]; then
    echo "🧹 Nettoyage du dossier de test existant..."
    rm -rf "$TEST_DIR"
fi

mkdir -p "$TEST_DIR"
cd "$TEST_DIR"

echo "📋 Création d'un projet Laravel de test..."
d-packages-exec php84 composer create-project laravel/laravel . --prefer-dist --quiet

echo "📦 Installation du package en local..."
d-packages-exec php84 composer config repositories.local path ../
d-packages-exec php84 composer require forxer/fontawesome-migrator:@dev --quiet

echo "⚙️  Publication de la configuration..."
d-packages-exec php84 php artisan vendor:publish --tag=fontawesome-migrator-config --quiet

echo "📝 Création de fichiers de test avec icônes FA5..."
mkdir -p resources/views
cat > resources/views/test.blade.php << 'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>Test Font Awesome 5</title>
</head>
<body>
    <div class="container">
        <!-- Icônes de base -->
        <i class="fas fa-home"></i>
        <i class="far fa-user"></i>
        <i class="fab fa-github"></i>
        
        <!-- Icônes renommées -->
        <i class="fas fa-times"></i>
        <i class="fas fa-external-link"></i>
        <i class="fas fa-trash-o"></i>
        
        <!-- Styles Pro -->
        <i class="fal fa-star"></i>
        <i class="fad fa-heart"></i>
    </div>
</body>
</html>
EOF

cat > resources/js/test.js << 'EOF'
// Test JavaScript avec icônes FA5
const icons = {
    home: 'fas fa-home',
    user: 'far fa-user',
    close: 'fas fa-times',
    star: 'fal fa-star'
};
EOF

echo "🔍 Test de la commande en mode dry-run..."
if d-packages-exec php84 php artisan fontawesome:migrate --dry-run; then
    echo "✅ Commande dry-run: SUCCÈS"
else
    echo "❌ Commande dry-run: ÉCHEC"
    cd ..
    rm -rf "$TEST_DIR"
    exit 1
fi

echo "🔍 Test de la commande avec rapport..."
if d-packages-exec php84 php artisan fontawesome:migrate --dry-run --report --verbose; then
    echo "✅ Commande avec rapport: SUCCÈS"
else
    echo "❌ Commande avec rapport: ÉCHEC"
    cd ..
    rm -rf "$TEST_DIR"
    exit 1
fi

echo "🧹 Nettoyage du dossier de test..."
cd ..
rm -rf "$TEST_DIR"

echo ""
echo "🎉 TOUS LES TESTS SONT RÉUSSIS !"
echo "================================="
echo ""
echo "📋 Résumé:"
echo "   ✅ Dépendances installées"
echo "   ✅ Tests unitaires passés"
echo "   ✅ Style de code vérifié"
echo "   ✅ Code moderne vérifié"
echo "   ✅ Intégration Laravel testée"
echo ""
echo "🚀 Le package est prêt à être utilisé !"
echo ""
echo "📚 Commandes utiles:"
echo "   d-packages-exec php84 composer test         # Tests uniquement"
echo "   d-packages-exec php84 composer quality      # Contrôle qualité complet"
echo "   d-packages-exec php84 composer pint         # Corriger le style"
echo "   d-packages-exec php84 composer rector       # Moderniser le code"