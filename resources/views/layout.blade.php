<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FontAwesome Migrator')</title>
    @yield('head-extra')
    @include('fontawesome-migrator::reports.partials.css.common')
</head>
<body>
    <!-- Menu de navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <span class="navbar-logo">🔄</span>
                <span class="navbar-title">FontAwesome Migrator</span>
            </div>
            <ul class="navbar-menu">
                <li class="navbar-item">
                    <a href="{{ route('fontawesome-migrator.reports.index') }}" class="navbar-link {{ request()->routeIs('fontawesome-migrator.reports.*') ? 'active' : '' }}">
                        📊 Rapports
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="{{ route('fontawesome-migrator.sessions.index') }}" class="navbar-link {{ request()->routeIs('fontawesome-migrator.sessions.*') ? 'active' : '' }}">
                        🗂️ Sessions
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="{{ route('fontawesome-migrator.test.panel') }}" class="navbar-link {{ request()->routeIs('fontawesome-migrator.test.*') ? 'active' : '' }}">
                        🧪 Test & Debug
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Fil d'ariane -->
    @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
        <nav class="breadcrumb">
            <div class="breadcrumb-container">
                <ul class="breadcrumb-list">
                    <li class="breadcrumb-item">
                        <a href="{{ route('fontawesome-migrator.reports.index') }}" class="breadcrumb-link">
                            🏠 Accueil
                        </a>
                    </li>
                    @foreach($breadcrumbs as $breadcrumb)
                        <li class="breadcrumb-separator">›</li>
                        <li class="breadcrumb-item">
                            @if(isset($breadcrumb['url']))
                                <a href="{{ $breadcrumb['url'] }}" class="breadcrumb-link">
                                    {{ $breadcrumb['label'] }}
                                </a>
                            @else
                                <span class="breadcrumb-current">{{ $breadcrumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </nav>
    @endif

    <div class="container {{ isset($breadcrumbs) ? 'with-breadcrumb' : '' }}">
        @yield('content')
    </div>

    @yield('scripts')
</body>
</html>