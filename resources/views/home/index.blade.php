@extends('fontawesome-migrator::layout')

@section('title', 'FontAwesome Migrator - Accueil')

@section('head-extra')
    @include('fontawesome-migrator::partials.css.home')
@endsection

@section('content')
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="hero-content">
            <div class="hero-icon">🔄</div>
            <h1 class="hero-title">FontAwesome Migrator</h1>
            <p class="hero-subtitle">Migrez facilement de FontAwesome 5 vers FontAwesome 6</p>
            <div class="hero-version">Version {{ $stats['package_version'] }}</div>
        </div>
    </div>

    <!-- Statistics Dashboard -->
    <div class="dashboard-stats">
        <div class="stat-card {{ $stats['total_sessions'] > 0 ? 'has-data' : '' }}">
            <div class="stat-icon">📁</div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['total_sessions'] }}</div>
                <div class="stat-label">Sessions de migration</div>
            </div>
        </div>

        <div class="stat-card {{ $stats['total_reports'] > 0 ? 'has-data' : '' }}">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['total_reports'] }}</div>
                <div class="stat-label">Rapports générés</div>
            </div>
        </div>

        <div class="stat-card {{ $stats['successful_migrations'] > 0 ? 'has-data' : '' }}">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['successful_migrations'] }}</div>
                <div class="stat-label">Migrations réussies</div>
            </div>
        </div>

        <div class="stat-card {{ $stats['total_size'] > 0 ? 'has-data' : '' }}">
            <div class="stat-icon">💾</div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['total_size'] / 1024, 1, ',', ' ') }} KB</div>
                <div class="stat-label">Données générées</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <h2 class="section-title">🚀 Actions Rapides</h2>
        
        <div class="actions-grid">
            <div class="action-card">
                <div class="action-icon">📊</div>
                <h3>Voir les Rapports</h3>
                <p>Consultez tous les rapports de migration générés</p>
                <a href="{{ route('fontawesome-migrator.reports.index') }}" class="btn btn-primary">
                    Accéder aux rapports
                </a>
            </div>

            <div class="action-card">
                <div class="action-icon">🗂️</div>
                <h3>Gérer les Sessions</h3>
                <p>Explorez les sessions de migration et leurs métadonnées</p>
                <a href="{{ route('fontawesome-migrator.sessions.index') }}" class="btn btn-primary">
                    Voir les sessions
                </a>
            </div>

            <div class="action-card">
                <div class="action-icon">🧪</div>
                <h3>Test & Debug</h3>
                <p>Testez la migration et débugguez les problèmes</p>
                <a href="{{ route('fontawesome-migrator.test.panel') }}" class="btn btn-primary">
                    Panneau de test
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    @if(count($recentReports) > 0)
        <div class="recent-activity">
            <h2 class="section-title">📈 Activité Récente</h2>
            
            <div class="activity-list">
                @foreach($recentReports as $report)
                    <div class="activity-item">
                        <div class="activity-icon">📄</div>
                        <div class="activity-content">
                            <div class="activity-title">
                                <a href="{{ route('fontawesome-migrator.reports.show', $report['filename']) }}">
                                    {{ $report['name'] }}
                                </a>
                            </div>
                            <div class="activity-meta">
                                Session <span data-tooltip="ID complet : {{ $report['session_id'] }}">{{ $report['short_id'] }}</span>
                                • {{ date('d/m/Y à H:i', $report['created_at']) }}
                                • {{ number_format($report['size'] / 1024, 1, ',', ' ') }} KB
                            </div>
                        </div>
                        <div class="activity-badge">
                            @php
                                $age = time() - $report['created_at'];
                                if ($age < 3600) echo floor($age / 60) . 'm';
                                elseif ($age < 86400) echo floor($age / 3600) . 'h';
                                else echo floor($age / 86400) . 'j';
                            @endphp
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="activity-footer">
                <a href="{{ route('fontawesome-migrator.reports.index') }}" class="btn btn-secondary">
                    Voir tous les rapports →
                </a>
            </div>
        </div>
    @endif

    <!-- Getting Started -->
    @if($stats['total_sessions'] == 0)
        <div class="getting-started">
            <h2 class="section-title">🎯 Premiers Pas</h2>
            
            <div class="steps-container">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Installation</h3>
                        <p>Configurez le package dans votre projet Laravel</p>
                        <code class="step-code">php artisan fontawesome:install</code>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Test de Migration</h3>
                        <p>Testez la migration en mode dry-run pour voir les changements</p>
                        <code class="step-code">php artisan fontawesome:migrate --dry-run --report</code>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Migration Réelle</h3>
                        <p>Appliquez les changements définitivement</p>
                        <code class="step-code">php artisan fontawesome:migrate --report</code>
                    </div>
                </div>
            </div>

            <div class="getting-started-footer">
                <a href="{{ route('fontawesome-migrator.test.panel') }}" class="btn btn-primary">
                    🧪 Commencer un test de migration
                </a>
            </div>
        </div>
    @endif

    @if($stats['last_activity'])
        <div class="last-activity">
            <p>Dernière activité : {{ date('d/m/Y à H:i', $stats['last_activity']) }}</p>
        </div>
    @endif
@endsection