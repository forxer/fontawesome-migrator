@props(['dryRun'])

@if ($dryRun ?? false)
    <span class="badge bg-warning text-dark">
        <i class="bi bi-eye me-1"></i>DRY-RUN
    </span>
@else
    <span class="badge bg-success">
        <i class="bi bi-check-circle me-1"></i>LIVE
    </span>
@endif