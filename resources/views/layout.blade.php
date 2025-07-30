<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FontAwesome Migrator')</title>
    
    <!-- FontAwesome 7 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    @yield('head-extra')
    @include('fontawesome-migrator::partials.css.common')
</head>
<body>
    <!-- Menu de navigation -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a href="{{ route('fontawesome-migrator.home') }}" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit;">
                    <span class="navbar-logo"><i class="fa-regular fa-house"></i></span>
                    <span class="navbar-title">FontAwesome Migrator</span>
                </a>
            </div>
            <ul class="navbar-menu">
                <li class="navbar-item">
                    <a href="{{ route('fontawesome-migrator.reports.index') }}" class="navbar-link {{ request()->routeIs('fontawesome-migrator.reports.*') ? 'active' : '' }}">
                        <i class="fa-regular fa-file"></i> Rapports
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="{{ route('fontawesome-migrator.sessions.index') }}" class="navbar-link {{ request()->routeIs('fontawesome-migrator.sessions.*') ? 'active' : '' }}">
                        <i class="fa-regular fa-folder"></i> Sessions
                    </a>
                </li>
                <li class="navbar-item">
                    <a href="{{ route('fontawesome-migrator.tests.index') }}" class="navbar-link {{ request()->routeIs('fontawesome-migrator.tests.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-flask"></i> Tests
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Fil d'ariane -->
    <nav class="breadcrumb">
        <div class="breadcrumb-container">
            <ul class="breadcrumb-list">
                <li class="breadcrumb-item">
                    @if(isset($breadcrumbs) && count($breadcrumbs) > 0)
                        <a href="{{ route('fontawesome-migrator.home') }}" class="breadcrumb-link">
                            <i class="fa-regular fa-house"></i> Accueil
                        </a>
                    @else
                        <span class="breadcrumb-current"><i class="fa-regular fa-house"></i> Accueil</span>
                    @endif
                </li>
                @if(isset($breadcrumbs))
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
                @endif
            </ul>
        </div>
    </nav>

    <div class="container with-breadcrumb">
        @yield('content')
    </div>

    <!-- Bouton retour en haut -->
    <button class="back-to-top" onclick="scrollToTop()" title="Retour en haut">
        <i class="fa-solid fa-arrow-up"></i>
    </button>

    @yield('scripts')
    
    <!-- Script commun pour le bouton retour en haut -->
    <script>
        // Fonction retour en haut
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Gestion de la visibilité du bouton retour en haut
        document.addEventListener('DOMContentLoaded', function() {
            const backToTopButton = document.querySelector('.back-to-top');

            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.add('visible');
                } else {
                    backToTopButton.classList.remove('visible');
                }
            });
        });
    </script>
</body>
</html>