@extends('fontawesome-migrator::layout')

@section('title', 'Session ' . $shortId)

@section('content')
    <div class="header">
        <h1>🗂️ Session {{ $shortId }}</h1>
        <p>Détails de la session de migration {{ $sessionId }}</p>
    </div>

    @if($metadata)
        <!-- Informations de la session -->
        <div class="stats-summary">
            <h2 class="section-title">📋 Métadonnées de la session</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">
                        @if(isset($metadata['meta']['generated_at']))
                            {{ \Carbon\Carbon::parse($metadata['meta']['generated_at'])->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="stat-label">🕒 Créée le</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ $metadata['meta']['package_version'] ?? 'N/A' }}</div>
                    <div class="stat-label">📦 Version</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">
                        @if(isset($metadata['meta']['dry_run']) && $metadata['meta']['dry_run'])
                            🔍 Dry-run
                        @else
                            ✅ Réel
                        @endif
                    </div>
                    <div class="stat-label">⚙️ Mode</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">{{ count($backupFiles) }}</div>
                    <div class="stat-label">📂 Fichiers</div>
                </div>
            </div>
        </div>

        <!-- Configuration -->
        @if(isset($metadata['meta']['configuration']))
            <div class="section">
                <h3 class="section-title">⚙️ Configuration</h3>
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
                <h3 class="section-title">🔧 Options de migration</h3>
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
            <h3 class="section-title">📂 Fichiers de sauvegarde</h3>
            <div class="files-table" style="margin: 20px 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: var(--gray-100); border-bottom: 2px solid var(--gray-200);">
                            <th style="padding: 12px; text-align: left;">📄 Nom du fichier</th>
                            <th style="padding: 12px; text-align: right;">📊 Taille</th>
                            <th style="padding: 12px; text-align: center;">🕒 Modifié</th>
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
            <div class="empty-icon">📂</div>
            <div class="empty-title">Aucun fichier de sauvegarde</div>
            <div class="empty-description">
                Cette session ne contient aucun fichier de sauvegarde.
            </div>
        </div>
    @endif

    <!-- Répertoire de la session -->
    <div class="section">
        <h3 class="section-title">📁 Informations système</h3>
        <div class="system-info" style="background: var(--gray-100); padding: 15px; border-radius: 8px; font-family: monospace; margin: 20px 0;">
            <strong>Répertoire:</strong> {{ $sessionDir }}<br>
            <strong>Session ID:</strong> {{ $sessionId }}
        </div>
    </div>

    <!-- Actions -->
    <div class="actions" style="margin-top: 30px;">
        <a href="{{ route('fontawesome-migrator.sessions.index') }}" class="btn btn-secondary">
            ← Retour aux sessions
        </a>

        <button onclick="deleteCurrentSession()" class="btn btn-danger">
            🗑️ Supprimer cette session
        </button>

        <div style="margin-left: auto;">
            <button onclick="copySessionInfo()" class="btn btn-primary">
                📋 Copier les infos
            </button>
        </div>
    </div>
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