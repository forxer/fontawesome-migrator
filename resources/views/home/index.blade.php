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
            <div class="hero-icon"><i class="bi bi-arrow-repeat"></i></div>
            <h1 class="hero-title">FontAwesome Migrator</h1>
            <p class="hero-subtitle">Migrez facilement de FontAwesome 5 vers FontAwesome 6</p>
            <div class="hero-version">Version {{ $stats['package_version'] }}</div>
        </div>
    </div>

    <!-- Statistics Dashboard Bootstrap -->
    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
            <div class="card h-100 shadow-sm {{ $stats['total_sessions'] > 0 ? 'border-primary' : '' }}">
                <div class="card-body text-center">
                    <i class="bi bi-folder fs-1 {{ $stats['total_sessions'] > 0 ? 'text-primary' : 'text-muted' }} mb-3"></i>
                    <h3 class="card-title {{ $stats['total_sessions'] > 0 ? 'text-primary' : 'text-muted' }}">{{ $stats['total_sessions'] }}</h3>
                    <p class="card-text text-muted">Sessions de migration</p>
                </div>
                @if($stats['total_sessions'] > 0)
                    <div class="card-footer bg-primary bg-opacity-10 border-0"></div>
                @endif
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card h-100 shadow-sm {{ $stats['total_reports'] > 0 ? 'border-primary' : '' }}">
                <div class="card-body text-center">
                    <i class="bi bi-file-text fs-1 {{ $stats['total_reports'] > 0 ? 'text-primary' : 'text-muted' }} mb-3"></i>
                    <h3 class="card-title {{ $stats['total_reports'] > 0 ? 'text-primary' : 'text-muted' }}">{{ $stats['total_reports'] }}</h3>
                    <p class="card-text text-muted">Rapports générés</p>
                </div>
                @if($stats['total_reports'] > 0)
                    <div class="card-footer bg-primary bg-opacity-10 border-0"></div>
                @endif
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
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

        <div class="col-lg-3 col-md-6">
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

    <!-- Quick Actions Bootstrap -->
    <div class="mb-5">
        <h2 class="section-title section-title-lg">
            <i class="bi bi-lightning-fill text-primary"></i> Actions Rapides
        </h2>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="card action-card-bootstrap">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-file-text action-icon"></i>
                        <h5 class="card-title">Voir les Rapports</h5>
                        <p class="card-text text-muted">Consultez tous les rapports de migration générés</p>
                        <a href="{{ route('fontawesome-migrator.reports.index') }}" class="btn btn-primary">
                            Accéder aux rapports
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card action-card-bootstrap">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-folder action-icon"></i>
                        <h5 class="card-title">Gérer les Sessions</h5>
                        <p class="card-text text-muted">Explorez les sessions de migration et leurs métadonnées</p>
                        <a href="{{ route('fontawesome-migrator.sessions.index') }}" class="btn btn-primary">
                            Voir les sessions
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card action-card-bootstrap">
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

    <!-- Recent Activity Bootstrap -->
    @if(count($recentReports) > 0)
        <div class="mb-5">
            <h2 class="section-title section-title-lg">
                <i class="bi bi-file-text text-primary"></i> Activité Récente
            </h2>

            <div class="card shadow-sm activity-list">
                <div class="list-group list-group-flush">
                    @foreach($recentReports as $report)
                        <div class="list-group-item activity-item">
                            <i class="bi bi-file-text activity-icon"></i>
                            <div class="activity-content">
                                <div class="activity-title">
                                    <a href="{{ route('fontawesome-migrator.reports.show', $report['filename']) }}" 
                                       class="text-decoration-none text-dark">
                                        {{ $report['name'] }}
                                    </a>
                                </div>
                                <div class="activity-meta">
                                    Session <span data-bs-toggle="tooltip" title="ID complet : {{ $report['session_id'] }}">{{ $report['short_id'] }}</span>
                                    • {{ $report['created_at']->format('d/m/Y à H:i') }}
                                    • {{ human_readable_bytes_size($report['size'], 2) }}
                                    @if($report['dry_run'])
                                        • <span class="badge bg-warning text-dark">DRY-RUN</span>
                                    @else
                                        • <span class="badge bg-success">RÉEL</span>
                                    @endif
                                </div>
                            </div>
                            <span class="badge activity-badge">
                                {{ $report['created_at']->diffForHumans(['short' => true]) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('fontawesome-migrator.reports.index') }}" class="btn btn-outline-primary">
                    Voir tous les rapports <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    @endif

    <!-- Getting Started Bootstrap -->
    @if($stats['total_sessions'] == 0)
        <div class="card shadow-sm mb-5">
            <div class="card-header bg-primary text-white">
                <h2 class="section-title">
                    <i class="bi bi-flask"></i> Premiers Pas
                </h2>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="d-flex align-items-start bg-light p-3 rounded">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                  style="width: 40px; height: 40px; font-size: 1.2rem;">1</span>
                            <div class="activity-content">
                                <h5 class="mb-2">Installation</h5>
                                <p class="text-muted mb-3">Configurez le package dans votre projet Laravel</p>
                                <code class="d-block bg-dark text-light p-2 rounded">php artisan fontawesome:install</code>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex align-items-start bg-light p-3 rounded">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                  style="width: 40px; height: 40px; font-size: 1.2rem;">2</span>
                            <div class="activity-content">
                                <h5 class="mb-2">Test de Migration</h5>
                                <p class="text-muted mb-3">Testez la migration en mode dry-run pour voir les changements</p>
                                <code class="d-block bg-dark text-light p-2 rounded">php artisan fontawesome:migrate --dry-run --report</code>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="d-flex align-items-start bg-light p-3 rounded">
                            <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                  style="width: 40px; height: 40px; font-size: 1.2rem;">3</span>
                            <div class="activity-content">
                                <h5 class="mb-2">Migration Réelle</h5>
                                <p class="text-muted mb-3">Appliquez les changements définitivement</p>
                                <code class="d-block bg-dark text-light p-2 rounded">php artisan fontawesome:migrate --report</code>
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
    @endif

    @if($stats['last_activity'])
        <div class="last-activity">
            <p>Dernière activité : {{ $stats['last_activity']->diffForHumans() }}</p>
        </div>
    @endif
@endsection

@section('scripts')
<script>
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