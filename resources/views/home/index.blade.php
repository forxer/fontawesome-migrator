@extends('fontawesome-migrator::layout')

@section('title', 'FontAwesome Migrator - Accueil')

@section('head-extra')
    @include('fontawesome-migrator::partials.css.home')
@endsection

@section('content')
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <div class="hero-icon"><i class="fa-solid fa-arrows-rotate"></i></div>
            <h1 class="hero-title">FontAwesome Migrator</h1>
            <p class="hero-subtitle">Migrez facilement de FontAwesome 5 vers FontAwesome 6</p>
            <div class="hero-version">Version {{ $stats['package_version'] }}</div>
        </div>
    </div>

    <!-- Statistics Dashboard -->
    <div class="dashboard-stats">
        <div class="stat-card {{ $stats['total_sessions'] > 0 ? 'has-data' : '' }}">
            <div class="stat-icon"><i class="fa-regular fa-folder"></i></div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['total_sessions'] }}</div>
                <div class="stat-label">Sessions de migration</div>
            </div>
        </div>

        <div class="stat-card {{ $stats['total_reports'] > 0 ? 'has-data' : '' }}">
            <div class="stat-icon"><i class="fa-regular fa-chart-bar"></i></div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['total_reports'] }}</div>
                <div class="stat-label">Rapports générés</div>
            </div>
        </div>

        <div class="stat-card {{ $stats['successful_migrations'] > 0 ? 'has-data' : '' }}">
            <div class="stat-icon"><i class="fa-regular fa-square-check"></i></div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['successful_migrations'] }}</div>
                <div class="stat-label">Migrations réussies</div>
            </div>
        </div>

        <div class="stat-card {{ $stats['total_size'] > 0 ? 'has-data' : '' }}">
            <div class="stat-icon"><i class="fa-regular fa-folder"></i></div>
            <div class="stat-content">
                <div class="stat-number">{{ human_readable_bytes_size($stats['total_size'], 2) }}</div>
                <div class="stat-label">Données générées</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2 class="section-title"><i class="fa-solid fa-bolt"></i> Actions Rapides</h2>

        <div class="actions-grid">
            <div class="action-card">
                <div class="action-icon"><i class="fa-regular fa-chart-bar"></i></div>
                <h3 class="section-title">Voir les Rapports</h3>
                <p>Consultez tous les rapports de migration générés</p>
                <a href="{{ route('fontawesome-migrator.reports.index') }}" class="btn btn-primary">
                    Accéder aux rapports
                </a>
            </div>

            <div class="action-card">
                <div class="action-icon"><i class="fa-regular fa-folder"></i></div>
                <h3 class="section-title">Gérer les Sessions</h3>
                <p>Explorez les sessions de migration et leurs métadonnées</p>
                <a href="{{ route('fontawesome-migrator.sessions.index') }}" class="btn btn-primary">
                    Voir les sessions
                </a>
            </div>

            <div class="action-card">
                <div class="action-icon"><i class="fa-solid fa-flask"></i></div>
                <h3 class="section-title">Tests</h3>
                <p>Testez la migration et débugguez les problèmes</p>
                <a href="{{ route('fontawesome-migrator.tests.index') }}" class="btn btn-primary">
                    Accéder aux tests
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    @if(count($recentReports) > 0)
        <div class="recent-activity">
            <h2 class="section-title"><i class="fa-regular fa-chart-bar"></i> Activité Récente</h2>

            <div class="activity-list">
                @foreach($recentReports as $report)
                    <div class="activity-item">
                        <div class="activity-icon"><i class="fa-regular fa-chart-bar"></i></div>
                        <div class="activity-content">
                            <div class="activity-title">
                                <a href="{{ route('fontawesome-migrator.reports.show', $report['filename']) }}">
                                    {{ $report['name'] }}
                                </a>
                            </div>
                            <div class="activity-meta">
                                Session <span data-tooltip="ID complet : {{ $report['session_id'] }}">{{ $report['short_id'] }}</span>
                                • {{ $report['created_at']->format('d/m/Y à H:i') }}
                                • {{ human_readable_bytes_size($report['size'], 2) }}
                            </div>
                        </div>
                        <div class="activity-badge">
                            {{ $report['created_at']->diffForHumans(['short' => true]) }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="activity-footer">
                <a href="{{ route('fontawesome-migrator.reports.index') }}" class="btn btn-secondary">
                    Voir tous les rapports <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    @endif

    <!-- Getting Started -->
    @if($stats['total_sessions'] == 0)
        <div class="getting-started">
            <h2 class="section-title"><i class="fa-solid fa-flask"></i> Premiers Pas</h2>

            <div class="steps-container">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3 class="section-title">Installation</h3>
                        <p>Configurez le package dans votre projet Laravel</p>
                        <code class="step-code">php artisan fontawesome:install</code>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3 class="section-title">Test de Migration</h3>
                        <p>Testez la migration en mode dry-run pour voir les changements</p>
                        <code class="step-code">php artisan fontawesome:migrate --dry-run --report</code>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3 class="section-title">Migration Réelle</h3>
                        <p>Appliquez les changements définitivement</p>
                        <code class="step-code">php artisan fontawesome:migrate --report</code>
                    </div>
                </div>
            </div>

            <div class="getting-started-footer">
                <a href="{{ route('fontawesome-migrator.tests.index') }}" class="btn btn-primary">
                    <i class="fa-solid fa-flask"></i> Commencer un test de migration
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