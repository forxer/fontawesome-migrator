@extends('fontawesome-migrator::layout')

@section('title', 'Session ' . $shortId)

@section('content')
    <div class="header">
        <h1><i class="fa-solid fa-folder-open"></i> Session {{ $shortId }}</h1>
        <p>Détails de la session de migration {{ $sessionId }}</p>
    </div>

    @if($metadata)
        <!-- Informations de la session -->
        <div class="stats-summary">
            <h2 class="section-title"><i class="fa-solid fa-clipboard-list"></i> Métadonnées de la session</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">
                        @php
                            // Debug: chercher la date dans différentes structures possibles
                            $createdAt = $metadata['session']['started_at'] ?? 
                                        $metadata['meta']['generated_at'] ?? 
                                        $metadata['session']['created_at'] ?? 
                                        $metadata['created_at'] ?? null;
                        @endphp
                        @if($createdAt)
                            {{ \Carbon\Carbon::parse($createdAt)->format('d/m/Y H:i') }}
                        @else
                            <!-- Debug: afficher la structure pour comprendre -->
                            @if(isset($metadata['session']))
                                @if(isset($metadata['session']['started_at']))
                                    {{ \Carbon\Carbon::parse($metadata['session']['started_at'])->format('d/m/Y H:i') }}
                                @else
                                    Session: {{ json_encode(array_keys($metadata['session'])) }}
                                @endif
                            @elseif(isset($metadata['meta']))
                                Meta: {{ json_encode(array_keys($metadata['meta'])) }}
                            @else
                                Root: {{ json_encode(array_keys($metadata)) }}
                            @endif
                        @endif
                    </div>
                    <div class="stat-label"><i class="fa-regular fa-clock"></i> Créée le</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $metadata['meta']['package_version'] ?? $metadata['session']['package_version'] ?? 'N/A' }}</div>
                    <div class="stat-label"><i class="fa-solid fa-tag"></i> Version</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">
                        @php
                            $isDryRun = $metadata['runtime']['dry_run'] ?? $metadata['meta']['dry_run'] ?? false;
                        @endphp
                        @if($isDryRun)
                            <i class="fa-regular fa-eye"></i> Dry-run
                        @else
                            <i class="fa-solid fa-play"></i> Réel
                        @endif
                    </div>
                    <div class="stat-label"><i class="fa-solid fa-gear"></i> Mode</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ count($backupFiles) }}</div>
                    <div class="stat-label"><i class="fa-solid fa-file-arrow-down"></i> Sauvegardes</div>
                </div>
            </div>
        </div>

        <!-- Configuration -->
        @if(isset($metadata['meta']['configuration']))
            <div class="section">
                <h3 class="section-title"><i class="fa-solid fa-gear"></i> Configuration</h3>
                <div class="config-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;">
                    <div class="config-item">
                        <strong>Type de licence:</strong><br>
                        <span class="badge">{{ $metadata['meta']['configuration']['license_type'] ?? 'N/A' }}</span>
                    </div>
                    <div class="config-item">
                        <strong>Chemins scannés:</strong><br>
                        @if(isset($metadata['meta']['configuration']['scan_paths']))
                            @foreach($metadata['meta']['configuration']['scan_paths'] as $path)
                                <span class="badge">{{ $path }}</span>
                            @endforeach
                        @else
                            <span class="badge">N/A</span>
                        @endif
                    </div>
                    <div class="config-item">
                        <strong>Extensions:</strong><br>
                        @if(isset($metadata['meta']['configuration']['file_extensions']))
                            @foreach($metadata['meta']['configuration']['file_extensions'] as $ext)
                                <span class="badge">{{ $ext }}</span>
                            @endforeach
                        @else
                            <span class="badge">N/A</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Options de migration -->
        @if(isset($metadata['meta']['migration_options']))
            <div class="section">
                <h3 class="section-title"><i class="fa-solid fa-gear"></i> Options de migration</h3>
                <div class="options-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin: 20px 0;">
                    @foreach($metadata['meta']['migration_options'] as $option => $value)
                        <div class="option-item">
                            <span class="option-label">{{ ucfirst(str_replace('_', ' ', $option)) }}:</span>
                            <span class="badge {{ $value ? 'badge-success' : 'badge-secondary' }}">
                                {{ $value ? 'Oui' : 'Non' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <!-- Fichiers de sauvegarde -->
    @if(count($backupFiles) > 0)
        <div class="section">
            <h3 class="section-title"><i class="fa-solid fa-file-arrow-down"></i> Fichiers de sauvegarde</h3>
            <div class="files-table" style="margin: 20px 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--gray-100); border-bottom: 2px solid var(--gray-200);">
                            <th style="padding: 12px; text-align: left;"><i class="fa-solid fa-file-code"></i> Nom du fichier</th>
                            <th style="padding: 12px; text-align: right;"><i class="fa-solid fa-weight-hanging"></i> Taille</th>
                            <th style="padding: 12px; text-align: center;"><i class="fa-regular fa-clock"></i> Modifié</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backupFiles as $file)
                            <tr style="border-bottom: 1px solid var(--gray-200);">
                                <td style="padding: 12px; font-family: monospace;">{{ $file['name'] }}</td>
                                <td style="padding: 12px; text-align: right;">{{ number_format($file['size'] / 1024, 1, ',', ' ') }} KB</td>
                                <td style="padding: 12px; text-align: center;">{{ $file['modified']->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-icon"><i class="fa-solid fa-folder-open"></i></div>
            <div class="empty-title">Aucun fichier de sauvegarde</div>
            <div class="empty-description">
                Cette session ne contient aucun fichier de sauvegarde.
            </div>
        </div>
    @endif

    <!-- Répertoire de la session -->
    <div class="section">
        <h3 class="section-title"><i class="fa-solid fa-info-circle"></i> Informations système</h3>
        <div class="system-info" style="background: var(--gray-100); padding: 15px; border-radius: 8px; font-family: monospace; margin: 20px 0;">
            <strong>Répertoire:</strong> {{ $sessionDir }}<br>
            <strong>Session ID:</strong> {{ $sessionId }}
        </div>
    </div>

    <!-- Actions -->
    <div class="actions" style="margin-top: 30px;">
        <a href="{{ route('fontawesome-migrator.sessions.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Retour aux sessions
        </a>

        <button onclick="deleteCurrentSession()" class="btn btn-danger">
            <i class="fa-regular fa-trash-can"></i> Supprimer cette session
        </button>

        <div style="margin-left: auto;">
            <button onclick="copySessionInfo()" class="btn btn-primary">
                <i class="fa-regular fa-copy"></i> Copier les infos
            </button>
        </div>
    </div>
@endsection

@section('head-extra')
<style>
    /* Styles spécifiques pour les métadonnées de session */
    .badge-success {
        background-color: var(--success-color);
        color: white;
    }
    
    .badge-warning {
        background-color: var(--warning-color);
        color: white;
    }
    
    .badge-info {
        background-color: var(--primary-color);
        color: white;
    }
    
    .badge-secondary {
        background-color: var(--gray-500);
        color: white;
    }
    
    .config-item, .env-item, .option-item, .backup-stat {
        padding: 15px;
        background: var(--gray-50);
        border-radius: var(--radius-md);
        border-left: 4px solid var(--primary-color);
    }
    
    .config-item strong, .env-item strong, .option-item .option-label {
        color: var(--gray-700);
        display: block;
        margin-bottom: 8px;
    }
    
    .badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: var(--radius-full);
        font-size: 0.85rem;
        font-weight: 500;
        margin: 2px;
    }
    
    .report-item {
        transition: all 0.2s ease;
    }
    
    .report-item:hover {
        background: var(--gray-100) !important;
        transform: translateY(-1px);
    }
    
    .stat-item {
        text-align: center;
        padding: var(--spacing-lg);
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        transition: all 0.2s ease;
        border-left: 4px solid var(--primary-color);
    }
    
    .stat-item:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
    
    .stat-number {
        font-size: 1.8rem;
        font-weight: bold;
        color: var(--gray-800);
        margin-bottom: 8px;
    }
    
    .stat-label {
        color: var(--gray-600);
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .stat-label i {
        margin-right: 6px;
        color: var(--primary-color);
    }
</style>
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