@extends('fontawesome-migrator::layout')

@section('title', 'Session ' . $shortId)

@section('content')
    <x-fontawesome-migrator::page-header
        icon="folder-open"
        title="Session {{ $shortId }}"
        subtitle="Détails de la session de migration"
        :counterText="count($backupFiles) . ' fichier(s) de sauvegarde'"
        counterIcon="files"
        :hasActions="true"
        actionsLabel="Actions de session"
    >
        <x-slot name="actions">
            <li><a class="dropdown-item" href="{{ route('fontawesome-migrator.sessions.index') }}">
                <i class="bi bi-arrow-left"></i> Retour aux sessions
            </a></li>
            <li><a class="dropdown-item" href="#" onclick="copySessionInfo(); return false;">
                <i class="bi bi-clipboard"></i> Copier les infos
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#" onclick="deleteCurrentSession(); return false;">
                <i class="bi bi-trash"></i> Supprimer cette session
            </a></li>
        </x-slot>
    </x-fontawesome-migrator::page-header>

    @if($metadata)
        <!-- Statistiques de la session -->
        <div class="mb-4">
            <h2 class="section-title">
                <i class="bi bi-bar-chart text-primary"></i> Métadonnées de la session
            </h2>
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-calendar fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">
                                @php
                                    $createdAt = $metadata['session']['started_at'] ?? $metadata['meta']['generated_at'] ?? null;
                                @endphp
                                @if($createdAt)
                                    {{ \Carbon\Carbon::parse($createdAt)->format('d/m') }}
                                @else
                                    N/A
                                @endif
                            </div>
                            <div class="text-muted small">Créée le</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-tag fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ $metadata['meta']['package_version'] ?? $metadata['session']['package_version'] ?? 'N/A' }}</div>
                            <div class="text-muted small">Version</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            @php
                                $isDryRun = $metadata['runtime']['dry_run'] ?? $metadata['meta']['dry_run'] ?? false;
                            @endphp
                            @if($isDryRun)
                                <i class="bi bi-eye fs-1 text-warning mb-2"></i>
                                <div class="fs-3 fw-bold text-warning">Dry-run</div>
                            @else
                                <i class="bi bi-play-fill fs-1 text-success mb-2"></i>
                                <div class="fs-3 fw-bold text-success">Réel</div>
                            @endif
                            <div class="text-muted small">Mode</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-files fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ count($backupFiles) }}</div>
                            <div class="text-muted small">Sauvegardes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration -->
        @if(isset($metadata['meta']['configuration']))
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="section-title">
                        <i class="bi bi-gear text-primary"></i> Configuration
                    </h2>
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <div class="border rounded p-3">
                                <strong class="d-block mb-2">Type de licence:</strong>
                                <span class="badge bg-primary">{{ $metadata['meta']['configuration']['license_type'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="border rounded p-3">
                                <strong class="d-block mb-2">Chemins scannés:</strong>
                                @if(isset($metadata['meta']['configuration']['scan_paths']))
                                    @foreach($metadata['meta']['configuration']['scan_paths'] as $path)
                                        <span class="badge bg-secondary me-1">{{ $path }}</span>
                                    @endforeach
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="border rounded p-3">
                                <strong class="d-block mb-2">Extensions:</strong>
                                @if(isset($metadata['meta']['configuration']['file_extensions']))
                                    @foreach($metadata['meta']['configuration']['file_extensions'] as $ext)
                                        <span class="badge bg-info me-1">{{ $ext }}</span>
                                    @endforeach
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Options de migration -->
        @if(isset($metadata['meta']['migration_options']))
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="section-title">
                        <i class="bi bi-sliders text-primary"></i> Options de migration
                    </h2>
                    <div class="row g-2">
                        @foreach($metadata['meta']['migration_options'] as $option => $value)
                            <div class="col-md-6 col-lg-4">
                                <div class="d-flex justify-content-between align-items-center p-2 border rounded">
                                    <span class="small">{{ ucfirst(str_replace('_', ' ', $option)) }}:</span>
                                    <span class="badge {{ $value ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $value ? 'Oui' : 'Non' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Fichiers de sauvegarde -->
    @if(count($backupFiles) > 0)
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="section-title">
                    <i class="bi bi-files text-primary"></i> Fichiers de sauvegarde
                </h2>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th><i class="bi bi-file-code"></i> Nom du fichier</th>
                                <th class="text-end"><i class="bi bi-hdd"></i> Taille</th>
                                <th class="text-center"><i class="bi bi-clock"></i> Modifié</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backupFiles as $file)
                                <tr>
                                    <td class="font-monospace">{{ $file['name'] }}</td>
                                    <td class="text-end">{{ human_readable_bytes_size($file['size'], 2) }}</td>
                                    <td class="text-center">{{ $file['modified']->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-folder-open display-1 text-muted"></i>
            </div>
            <h3 class="text-muted mb-3">Aucun fichier de sauvegarde</h3>
            <p class="text-muted">
                Cette session ne contient aucun fichier de sauvegarde.
            </p>
        </div>
    @endif

    <!-- Informations système -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="section-title">
                <i class="bi bi-info-circle text-primary"></i> Informations système
            </h2>
            <div class="bg-light p-3 rounded font-monospace">
                <div class="mb-2"><strong>Répertoire:</strong> {{ $sessionDir }}</div>
                <div><strong>Session ID:</strong> {{ $sessionId }}</div>
            </div>
        </div>
    </div>

@endsection

@section('head-extra')
    @include('fontawesome-migrator::partials.css.bootstrap-common')
@endsection

@section('scripts')
<script>
    // Configuration CSRF pour les requêtes AJAX
    window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function showAlert(message, type = 'success') {
        const alertsContainer = document.getElementById('alerts') || document.body;
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        alert.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            padding: 15px 20px; border-radius: 5px; color: white;
            background: ${type === 'error' ? '#e53e3e' : '#48bb78'};
        `;

        document.body.appendChild(alert);

        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    async function deleteCurrentSession() {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette session ?')) {
            return;
        }

        try {
            const response = await fetch(`/fontawesome-migrator/sessions/{{ $sessionId }}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (response.ok) {
                showAlert('Session supprimée avec succès');
                setTimeout(() => {
                    window.location.href = '{{ route("fontawesome-migrator.sessions.index") }}';
                }, 1500);
            } else {
                showAlert(data.error || 'Erreur lors de la suppression', 'error');
            }
        } catch (error) {
            showAlert('Erreur de connexion', 'error');
        }
    }

    function copySessionInfo() {
        const sessionInfo = `Session FontAwesome Migrator
ID: {{ $sessionId }}
Répertoire: {{ $sessionDir }}
@if($metadata)
Créée: {{ $metadata['meta']['generated_at'] ?? 'N/A' }}
Version: {{ $metadata['meta']['package_version'] ?? 'N/A' }}
Mode: {{ isset($metadata['meta']['dry_run']) && $metadata['meta']['dry_run'] ? 'Dry-run' : 'Réel' }}
@endif
Fichiers de sauvegarde: {{ count($backupFiles) }}`;

        navigator.clipboard.writeText(sessionInfo).then(() => {
            showAlert('Informations copiées dans le presse-papier');
        }).catch(() => {
            showAlert('Erreur lors de la copie', 'error');
        });
    }
</script>
@endsection