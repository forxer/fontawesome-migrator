<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'FontAwesome Migrator')</title>

    <!-- Bootstrap 5.3.7 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <!-- Bootstrap Icons 1.13.1 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    @yield('head-extra')
    @include('fontawesome-migrator::partials.css.common')
    @include('fontawesome-migrator::partials.css.bootstrap-common')
</head>
<body>
    <!-- Menu de navigation Bootstrap -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container-fluid" style="max-width: 1200px;">
            <a class="navbar-brand d-flex align-items-center gap-3 fw-semibold" href="{{ route('fontawesome-migrator.home') }}">
                <span class="fs-3"><i class="bi bi-arrow-repeat text-primary"></i></span>
                <span>FontAwesome Migrator</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('fontawesome-migrator.reports.*') ? 'active' : '' }}" href="{{ route('fontawesome-migrator.reports.index') }}">
                            <i class="bi bi-file-text"></i> Rapports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('fontawesome-migrator.sessions.*') ? 'active' : '' }}" href="{{ route('fontawesome-migrator.sessions.index') }}">
                            <i class="bi bi-folder"></i> Sessions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs('fontawesome-migrator.tests.*') ? 'active' : '' }}" href="{{ route('fontawesome-migrator.tests.index') }}">
                            <i class="bi bi-flask"></i> Tests
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Fil d'ariane -->
    <div class="container">
        {!! Breadcrumbs::render() !!}
    </div>

    <div class="container">
        @yield('content')
    </div>

    <!-- Bouton retour en haut -->
    <button class="back-to-top-btn"
            onclick="scrollToTop()"
            title="Retour en haut"
            id="backToTopBtn">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>

    @include('fontawesome-migrator::partials.js.bootstrap-common')

    @yield('scripts')
</body>
</html>