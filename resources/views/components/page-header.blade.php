{{-- Component Blade pour l'en-tête standardisé des pages --}}
@include('fontawesome-migrator::partials.css.page-header')
<div class="page-header mb-4 shadow-sm border-0 rounded position-relative">

    <!-- Effet de bulles léger -->
    <div class="page-header-bubbles position-absolute top-0 start-0 w-100 h-100 opacity-25"></div>

    <div class="page-header-content p-4 position-relative">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-2">
                    <div class="page-header-icon bg-white bg-opacity-90 rounded-circle me-3 shadow-sm d-flex align-items-center justify-content-center">
                        <i class="bi bi-{{ $icon }} text-primary fs-1"></i>
                    </div>
                    <div>
                        <h1 class="page-header-title mb-1 fs-2 fw-bold">{{ $title }}</h1>
                        <p class="page-header-subtitle mb-0">{{ $subtitle }}</p>
                    </div>
                </div>
            </div>

            @if($hasActions || $hasCounter)
            <div class="col-md-4">
                <div class="d-flex flex-column align-items-end gap-2">
                    @if($hasCounter)
                    <div class="page-header-counter">
                        <i class="bi bi-{{ $counterIcon }} me-1 opacity-75"></i> {{ $counterText }}
                    </div>
                    @endif

                    @if($hasActions)
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear"></i> {{ $actionsLabel }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            {{ $slot }}
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>