@extends('fontawesome-migrator::layout')

@section('title', 'FontAwesome Migrator - Accueil')

@section('head-extra')
    @include('fontawesome-migrator::partials.css.bootstrap-common')
    @include('fontawesome-migrator::partials.css.home')
@endsection

@section('content')
    <!-- Hero Section -->
    <div class="hero-section mt-4">
        <div class="hero-content">
            <div class="hero-icon"><i class="bi bi-arrow-clockwise"></i></div>
            <h1 class="hero-title">FontAwesome Migrator</h1>
            <p class="hero-subtitle">Migrez facilement entre toutes les versions de FontAwesome</p>
            <div class="hero-version">Version {{ $stats['package_version'] }}</div>
        </div>
    </div>

    <!-- Getting Started -->
    @if($stats['total_migrations'] == 0)
        <div class="card shadow-sm mb-5">
            <div class="card-body p-4">
                <h2 class="section-title">
                    <i class="bi bi-flask"></i> Premiers Pas
                </h2>
                <div class="row g-4">
                    <div class="col-12">
                        <div class="d-flex align-items-start bg-light p-3 rounded">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                  style="width: 40px; height: 40px; font-size: 1.2rem;">1</span>
                            <div class="activity-content flex-grow-1">
                                <h5 class="mb-2">Installation</h5>
                                <p class="text-muted mb-3">Configurez le package dans votre projet Laravel</p>
                                <div class="d-flex align-items-center gap-2">
                                    <code class="flex-grow-1 bg-dark text-light p-2 rounded" id="install-command">php artisan fontawesome:install</code>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="copyCommand('php artisan fontawesome:install')" title="Copier la commande">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex align-items-start bg-light p-3 rounded">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                  style="width: 40px; height: 40px; font-size: 1.2rem;">2</span>
                            <div class="activity-content flex-grow-1">
                                <h5 class="mb-2">Test de Migration</h5>
                                <p class="text-muted mb-3">Testez la migration en mode dry-run pour voir les changements</p>
                                <div class="d-flex align-items-center gap-2">
                                    <code class="flex-grow-1 bg-dark text-light p-2 rounded" id="test-command">php artisan fontawesome:migrate --dry-run</code>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="copyCommand('php artisan fontawesome:migrate --dry-run')" title="Copier la commande">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex align-items-start bg-light p-3 rounded">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center me-3"
                                  style="width: 40px; height: 40px; font-size: 1.2rem;">3</span>
                            <div class="activity-content flex-grow-1">
                                <h5 class="mb-2">Migration Réelle</h5>
                                <p class="text-muted mb-3">Appliquez les changements définitivement</p>
                                <div class="d-flex align-items-center gap-2">
                                    <code class="flex-grow-1 bg-dark text-light p-2 rounded" id="migrate-command">php artisan fontawesome:migrate</code>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="copyCommand('php artisan fontawesome:migrate')" title="Copier la commande">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center bg-light">
                <a href="{{ route('fontawesome-migrator.tests.index') }}" class="btn btn-primary">
                    <i class="bi bi-flask"></i> Commencer un test de migration
                </a>
            </div>
        </div>
    @else

        <!-- Statistics Dashboard -->
        <div class="row g-4 mb-5">
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm {{ $stats['total_migrations'] > 0 ? 'border-primary' : '' }}">
                    <div class="card-body text-center">
                        <i class="bi bi-folder fs-1 {{ $stats['total_migrations'] > 0 ? 'text-primary' : 'text-muted' }} mb-3"></i>
                        <h3 class="card-title {{ $stats['total_migrations'] > 0 ? 'text-primary' : 'text-muted' }}">{{ $stats['total_migrations'] }}</h3>
                        <p class="card-text text-muted">Migrations effectuées</p>
                    </div>
                    @if($stats['total_migrations'] > 0)
                        <div class="card-footer bg-primary bg-opacity-10 border-0"></div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm {{ $stats['successful_migrations'] > 0 ? 'border-primary' : '' }}">
                    <div class="card-body text-center">
                        <i class="bi bi-check-square fs-1 {{ $stats['successful_migrations'] > 0 ? 'text-primary' : 'text-muted' }} mb-3"></i>
                        <h3 class="card-title {{ $stats['successful_migrations'] > 0 ? 'text-primary' : 'text-muted' }}">{{ $stats['successful_migrations'] }}</h3>
                        <p class="card-text text-muted">Migrations réussies</p>
                    </div>
                    @if($stats['successful_migrations'] > 0)
                        <div class="card-footer bg-primary bg-opacity-10 border-0"></div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm {{ $stats['total_size'] > 0 ? 'border-primary' : '' }}">
                    <div class="card-body text-center">
                        <i class="bi bi-hdd fs-1 {{ $stats['total_size'] > 0 ? 'text-primary' : 'text-muted' }} mb-3"></i>
                        <h3 class="card-title {{ $stats['total_size'] > 0 ? 'text-primary' : 'text-muted' }}">{{ human_readable_bytes_size($stats['total_size'], 2) }}</h3>
                        <p class="card-text text-muted">Données générées</p>
                    </div>
                    @if($stats['total_size'] > 0)
                        <div class="card-footer bg-primary bg-opacity-10 border-0"></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm {{ $stats['success_rate'] > 70 ? 'border-success' : ($stats['success_rate'] > 40 ? 'border-warning' : 'border-danger') }}">
                    <div class="card-body text-center">
                        <i class="bi bi-percent fs-1 {{ $stats['success_rate'] > 70 ? 'text-success' : ($stats['success_rate'] > 40 ? 'text-warning' : 'text-danger') }} mb-3"></i>
                        <h3 class="card-title {{ $stats['success_rate'] > 70 ? 'text-success' : ($stats['success_rate'] > 40 ? 'text-warning' : 'text-danger') }}">{{ $stats['success_rate'] }}%</h3>
                        <p class="card-text text-muted">Taux de réussite</p>
                    </div>
                    @if($stats['total_migrations'] > 0)
                        <div class="card-footer {{ $stats['success_rate'] > 70 ? 'bg-success' : ($stats['success_rate'] > 40 ? 'bg-warning' : 'bg-danger') }} bg-opacity-10 border-0"></div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm {{ $stats['avg_changes'] > 0 ? 'border-primary' : '' }}">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-up fs-1 {{ $stats['avg_changes'] > 0 ? 'text-primary' : 'text-muted' }} mb-3"></i>
                        <h3 class="card-title {{ $stats['avg_changes'] > 0 ? 'text-primary' : 'text-muted' }}">{{ $stats['avg_changes'] }}</h3>
                        <p class="card-text text-muted">Changements moyens</p>
                    </div>
                    @if($stats['avg_changes'] > 0)
                        <div class="card-footer bg-primary bg-opacity-10 border-0"></div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card h-100 shadow-sm border-secondary">
                    <div class="card-body text-center">
                        <i class="bi bi-pie-chart-fill fs-1 text-secondary mb-3"></i>
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="me-3">
                                <span class="badge bg-success fs-6">{{ $stats['real_run_count'] }}</span>
                                <div class="text-muted small">Réel</div>
                            </div>
                            <div class="text-muted fs-3">/</div>
                            <div class="ms-3">
                                <span class="badge bg-warning text-dark fs-6">{{ $stats['dry_run_count'] }}</span>
                                <div class="text-muted small">Test</div>
                            </div>
                        </div>
                        <p class="card-text text-muted mt-2">Types de migration</p>
                    </div>
                    <div class="card-footer bg-secondary bg-opacity-10 border-0"></div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-5">
            <h2 class="section-title section-title-lg">
                <i class="bi bi-lightning-fill text-primary"></i> Actions Rapides
            </h2>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card action-card">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-folder action-icon"></i>
                            <h5 class="card-title">Migrations</h5>
                            <p class="card-text text-muted">Consultez l'historique des migrations et leurs rapports détaillés</p>
                            <a href="{{ route('fontawesome-migrator.migrations.index') }}" class="btn btn-primary">
                                Voir les migrations
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card action-card">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-flask action-icon"></i>
                            <h5 class="card-title">Tests</h5>
                            <p class="card-text text-muted">Testez la migration et débugguez les problèmes</p>
                            <a href="{{ route('fontawesome-migrator.tests.index') }}" class="btn btn-primary">
                                Accéder aux tests
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        @if(count($recentMigrations) > 0)
            <div class="mb-5">
                <h2 class="section-title section-title-lg">
                    <i class="bi bi-folder text-primary"></i> Migrations Récentes
                </h2>

                <div class="card shadow-sm activity-list">
                    <div class="list-group list-group-flush">
                        @foreach($recentMigrations as $migration)
                            <div class="list-group-item activity-item">
                                <i class="bi bi-file-text activity-icon"></i>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <a href="{{ route('fontawesome-migrator.migrations.show', $migration['short_id']) }}"
                                        class="text-decoration-none text-dark">
                                            {{ $migration['name'] }}
                                        </a>
                                    </div>
                                    <div class="activity-meta">
                                        Migration <span data-bs-toggle="tooltip" title="ID complet : {{ $migration['session_id'] }}">{{ $migration['short_id'] }}</span>
                                        • {{ $migration['created_at']->format('d/m/Y à H:i') }}
                                        • {{ $migration['files_modified'] }} fichier(s) • {{ $migration['total_changes'] }} changement(s)
                                        @if($migration['dry_run'])
                                            • <span class="badge bg-warning text-dark">DRY-RUN</span>
                                        @else
                                            • <span class="badge bg-success">RÉEL</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="badge activity-badge">
                                    {{ $migration['created_at']->diffForHumans(['short' => true]) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('fontawesome-migrator.migrations.index') }}" class="btn btn-outline-primary">
                        Voir tous les rapports <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        @endif

        @if ($stats['last_activity'])
            <div class="last-activity mb-5">
                <p>Dernière activité : {{ $stats['last_activity']->diffForHumans() }}</p>
            </div>
        @endif
    @endif
@endsection

@section('scripts')
<script>
    // Fonction pour copier les commandes
    function copyCommand(command) {
        navigator.clipboard.writeText(command).then(() => {
            showAlert(`Commande copiée : ${command}`, 'success');
        }).catch(() => {
            // Fallback pour les anciens navigateurs
            const textArea = document.createElement('textarea');
            textArea.value = command;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showAlert(`Commande copiée : ${command}`, 'success');
        });
    }

    // Fonction pour afficher les alertes
    function showAlert(message, type = 'success') {
        const existing = document.querySelector('.temp-alert');
        if (existing) existing.remove();

        const alert = document.createElement('div');
        alert.className = `alert alert-${type} temp-alert`;
        alert.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        alert.textContent = message;

        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 3000);
    }

    // Génération dynamique de bulles
    function createBubble() {
        const heroSection = document.querySelector('.hero-section');
        const bubble = document.createElement('div');
        bubble.classList.add('bubble');

        // Taille aléatoire entre 10 et 40px
        const size = Math.random() * 30 + 10;
        bubble.style.width = size + 'px';
        bubble.style.height = size + 'px';

        // Position horizontale aléatoire
        bubble.style.left = Math.random() * 90 + 5 + '%';

        // Vitesse basée sur la taille (petites bulles = plus rapides)
        const speed = 8 + (40 - size) / 5; // Entre 8 et 14 secondes
        bubble.style.animationDuration = speed + 's';

        // Léger mouvement horizontal pendant la montée
        const sway = (Math.random() - 0.5) * 30;
        bubble.style.setProperty('--sway', sway + 'px');

        heroSection.appendChild(bubble);

        // Supprimer la bulle après l'animation
        setTimeout(() => {
            bubble.remove();
        }, speed * 1000);
    }

    // Créer des bulles périodiquement
    document.addEventListener('DOMContentLoaded', function() {
        // Créer quelques bulles au démarrage
        for (let i = 0; i < 5; i++) {
            setTimeout(createBubble, i * 800);
        }

        // Continuer à créer des bulles à un rythme modéré
        setInterval(createBubble, 2500);
    });
</script>
@endsection