@props(['source'])

@if (($source ?? 'cli') === 'web_interface')
    <span class="badge bg-info">
        <i class="bi bi-globe me-1"></i>Web
    </span>
@else
    <span class="badge bg-primary">
        <i class="bi bi-terminal me-1"></i>CLI
    </span>
@endif