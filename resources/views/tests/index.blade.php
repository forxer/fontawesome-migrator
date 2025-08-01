@extends('fontawesome-migrator::layout')

@section('title', 'Tests - FontAwesome Migrator')

@section('head-extra')
    @include('fontawesome-migrator::partials.css.bootstrap-common')
@endsection

@section('content')
    <x-fontawesome-migrator::page-header
        icon="flask"
        title="Tests"
        subtitle="Utilitaires de tests et diagnostics"
        :counterText="$backupStats['total_sessions'] . ' session(s) de test'"
        counterIcon="folder"
        :hasActions="true"
        actionsLabel="Actions globales"
    >
        <x-slot name="actions">
            <li><a class="dropdown-item" href="#" onclick="refreshPage(); return false;">
                <span id="refresh-icon"><i class="bi bi-arrow-repeat"></i></span> Actualiser
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#" onclick="cleanupSessions(7); return false;">
                <i class="bi bi-trash"></i> Nettoyer (7j+)
            </a></li>
        </x-slot>
    </x-fontawesome-migrator::page-header>

    @if ($backupStats['total_sessions'] > 0)
        <!-- Statistiques globales -->
        <div class="mb-4">
            <h2 class="section-title">
                <i class="bi bi-bar-chart text-primary"></i> Statistiques des tests
            </h2>
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-folder fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ $backupStats['total_sessions'] }}</div>
                            <div class="text-muted small">Sessions</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-files fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ $backupStats['total_backups'] }}</div>
                            <div class="text-muted small">Fichiers</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-hdd fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ human_readable_bytes_size($backupStats['total_size'], 2) }}</div>
                            <div class="text-muted small">Taille totale</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-clock fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">
                                @if($backupStats['last_session'])
                                    {{ $backupStats['last_session']['created_at']->format('d/m') }}
                                @else
                                    -
                                @endif
                            </div>
                            <div class="text-muted small">Dernière session</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Tests de Migration -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="section-title">
                <i class="bi bi-rocket text-primary"></i> Tests de Migration
            </h2>
            <div class="d-flex flex-wrap gap-2 justify-content-center mb-3">
                <button onclick="runTest('dry-run')" class="btn btn-primary test-btn" data-type="dry-run">
                    <i class="bi bi-eye"></i> Test Dry-Run
                </button>
                <button onclick="runTest('icons-only')" class="btn btn-outline-primary test-btn" data-type="icons-only">
                    <i class="bi bi-palette"></i> Test Icônes
                </button>
                <button onclick="runTest('assets-only')" class="btn btn-outline-primary test-btn" data-type="assets-only">
                    <i class="bi bi-file-code"></i> Test Assets
                </button>
                <button onclick="runTest('real')" class="btn btn-danger test-btn" data-type="real">
                    <i class="bi bi-lightning-fill"></i> Test Réel
                </button>
            </div>
            <div id="test-output" class="test-output mt-4" style="display: none;">
                <h3 class="section-title">Résultat du test :</h3>
                <pre id="test-result" class="bg-dark text-light p-3 rounded"></pre>
            </div>
        </div>
    </div>

    <!-- Navigation vers les sessions -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="section-title">
                <i class="bi bi-folder-open text-primary"></i> Voir les résultats
            </h2>
            <p class="text-muted mb-3">
                Après avoir lancé un test, consultez les sessions et rapports générés pour analyser les résultats.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('fontawesome-migrator.sessions.index') }}" class="btn btn-primary">
                    <i class="bi bi-folder"></i> Voir les sessions
                </a>
                <a href="{{ route('fontawesome-migrator.reports.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-file-text"></i> Voir les rapports
                </a>
            </div>
        </div>
    </div>

    <!-- Actions de nettoyage -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="section-title">
                <i class="bi bi-trash text-primary"></i> Nettoyage
            </h2>
            <div class="d-flex flex-wrap gap-2">
                <button onclick="cleanupSessions(7)" class="btn btn-outline-secondary">
                    <i class="bi bi-trash"></i> Nettoyer > 7 jours
                </button>
                <button onclick="cleanupSessions(1)" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i> Nettoyer > 1 jour
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<style>
    /* Loading spinner pour les boutons */
    .test-btn.loading {
        position: relative;
        pointer-events: none;
    }
    
    .test-btn.loading::after {
        content: '';
        position: absolute;
        top: 50%;
        right: 15px;
        width: 16px;
        height: 16px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        transform: translateY(-50%);
    }
    
    @keyframes spin {
        to {
            transform: translateY(-50%) rotate(360deg);
        }
    }
    
</style>

<script>
// Configuration CSRF pour les requêtes AJAX
window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Format des tailles de fichiers (équivalent PHP human_readable_bytes_size)
function formatFileSize(bytes, decimals = 2) {
    if (bytes === 0) return '0 B';
    
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function showAlert(message, type = 'success') {
    const existing = document.querySelector('.temp-alert');
    if (existing) existing.remove();

    const alert = document.createElement('div');
    alert.className = `alert alert-${type} temp-alert`;
    alert.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.textContent = message;

    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 4000);
}

function refreshPage() {
    const icon = document.getElementById('refresh-icon');
    icon.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

async function runTest(type) {
    const button = document.querySelector(`[data-type="${type}"]`);
    const output = document.getElementById('test-output');
    const result = document.getElementById('test-result');
    
    // Désactiver le bouton et afficher le loading
    button.disabled = true;
    button.classList.add('loading');
    output.style.display = 'block';
    result.innerHTML = `<i class="bi bi-rocket"></i> Lancement du test ${type}...\n`;
    
    try {
        const response = await fetch('/fontawesome-migrator/tests/migration', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({ type: type })
        });
        
        const data = await response.json();
        
        if (data.success) {
            result.innerHTML = `<i class="bi bi-check-square text-success"></i> Test ${type} terminé avec succès!

<strong><i class="bi bi-rocket"></i> Commande :</strong>
${data.command}

<strong><i class="bi bi-terminal"></i> Résultat :</strong>
${data.output}

<i class="bi bi-clock"></i> Terminé à ${data.timestamp}`;
            
            // Ajouter un bouton pour recharger manuellement
            const reloadBtn = document.createElement('button');
            reloadBtn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Recharger la page';
            reloadBtn.className = 'btn btn-primary mt-3';
            reloadBtn.onclick = () => location.reload();
            output.appendChild(reloadBtn);
            
            showAlert('Test terminé avec succès');
        } else {
            result.innerHTML = `<i class="bi bi-x-square text-danger"></i> Erreur lors du test ${type}

<strong><i class="bi bi-rocket"></i> Commande :</strong>
${data.command || 'Non disponible'}

<strong><i class="bi bi-exclamation-triangle"></i> Erreur :</strong>
${data.error || data.output}

<i class="bi bi-clock"></i> Terminé à ${data.timestamp}`;
            
            showAlert('Erreur lors du test', 'error');
        }
    } catch (error) {
        result.innerHTML = `<i class="bi bi-wifi-off text-danger"></i> Erreur de connexion\n\n${error.message}`;
        showAlert('Erreur de connexion', 'error');
    } finally {
        button.disabled = false;
        button.classList.remove('loading');
    }
}


async function cleanupSessions(days) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer toutes les sessions de plus de ${days} jour(s) ?`)) {
        return;
    }
    
    try {
        const response = await fetch('/fontawesome-migrator/tests/cleanup-sessions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({ days: days })
        });
        
        const data = await response.json();
        showAlert(`${data.message} - Sessions supprimées: ${data.deleted}`);
        
        if (data.deleted > 0) {
            setTimeout(() => location.reload(), 1500);
        }
    } catch (error) {
        showAlert('Erreur lors du nettoyage: ' + error.message, 'error');
    }
}
</script>
@endsection