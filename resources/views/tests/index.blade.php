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

    <!-- Migration Multi-Versions -->
    <div class="card mb-4">
        <div class="card-body">
            <h2 class="section-title">
                <i class="bi bi-arrow-repeat"></i> Migration Multi-Versions
            </h2>
            <form id="migrationForm" class="row g-3">
                <div class="col-md-4">
                    <label for="fromVersion" class="form-label">
                        <i class="bi bi-arrow-up-right"></i> Version Source
                    </label>
                    <select class="form-select" id="fromVersion" name="from">
                        <option value="">Détection automatique</option>
                        <option value="4">FontAwesome 4 (préfixes simples: fa)</option>
                        <option value="5">FontAwesome 5 (préfixes: fas, far, fab)</option>
                        <option value="6">FontAwesome 6 (préfixes: fa-solid, fa-regular)</option>
                    </select>
                    <div class="form-text">La version actuelle de votre projet</div>
                </div>

                <div class="col-md-4">
                    <label for="toVersion" class="form-label">
                        <i class="bi bi-arrow-down-right"></i> Version Cible
                    </label>
                    <select class="form-select" id="toVersion" name="to" disabled>
                        <option value="">Sélectionnez d'abord la version source</option>
                    </select>
                    <div class="form-text">La version vers laquelle migrer</div>
                </div>

                <div class="col-md-4">
                    <label for="migrationMode" class="form-label">
                        <i class="bi bi-sliders"></i> Mode de Migration
                    </label>
                    <select class="form-select" id="migrationMode" name="mode">
                        <option value="complete">Complète (icônes + assets)</option>
                        <option value="icons-only">Icônes seulement</option>
                        <option value="assets-only">Assets seulement</option>
                    </select>
                    <div class="form-text">Type de migration à effectuer</div>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="dryRun" name="dry_run" checked>
                        <label class="form-check-label" for="dryRun">
                            <i class="bi bi-eye"></i> Mode Dry-Run (simulation)
                        </label>
                        <div class="form-text">Recommandé pour tester avant migration réelle</div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="generateReport" name="report" checked>
                        <label class="form-check-label" for="generateReport">
                            <i class="bi bi-file-text"></i> Générer un rapport
                        </label>
                        <div class="form-text">Créer un rapport détaillé des changements</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div id="migrationInfo" class="text-muted">
                            <i class="bi bi-info-circle"></i> Sélectionnez les versions pour voir les détails de migration
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" id="generateCommand">
                                <i class="bi bi-terminal"></i> Générer Commande
                            </button>
                            <button type="submit" class="btn btn-primary" id="startMigration" disabled>
                                <i class="bi bi-play-circle"></i> Démarrer Migration
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Command Output -->
            <div id="commandOutput" class="mt-3" style="display: none;">
                <div class="card bg-dark text-light">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-terminal"></i> Commande Artisan Générée
                        </h6>
                    </div>
                    <div class="card-body">
                        <pre id="commandText" class="mb-0"></pre>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="copyCommand()">
                                <i class="bi bi-clipboard"></i> Copier
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="test-output" class="test-output mt-4" style="display: none;">
                <h3 class="section-title">Résultat de la migration :</h3>
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
    /* Styles pour l'interface multi-versions */
</style>

<script>
// Configuration CSRF pour les requêtes AJAX
window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Migrations supportées (passées depuis le contrôleur)
const supportedMigrations = @json($supportedMigrations);

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

// === FONCTIONS POUR LE SÉLECTEUR MULTI-VERSIONS ===

// Initialiser les événements du formulaire multi-versions
document.addEventListener('DOMContentLoaded', function() {
    const fromVersionSelect = document.getElementById('fromVersion');
    const toVersionSelect = document.getElementById('toVersion');
    const startMigrationBtn = document.getElementById('startMigration');
    const generateCommandBtn = document.getElementById('generateCommand');
    const migrationForm = document.getElementById('migrationForm');

    // Gérer le changement de version source
    fromVersionSelect.addEventListener('change', function() {
        updateTargetVersions(this.value);
        updateMigrationInfo();
        updateButtonStates();
    });

    // Gérer le changement de version cible
    toVersionSelect.addEventListener('change', function() {
        updateMigrationInfo();
        updateButtonStates();
    });

    // Gérer la génération de commande
    generateCommandBtn.addEventListener('click', function() {
        generateAndShowCommand();
    });

    // Gérer la soumission du formulaire
    migrationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        runMultiVersionMigration();
    });
});

// Mettre à jour les versions cibles disponibles
function updateTargetVersions(fromVersion) {
    const toVersionSelect = document.getElementById('toVersion');
    toVersionSelect.innerHTML = '';

    if (!fromVersion) {
        toVersionSelect.disabled = true;
        toVersionSelect.innerHTML = '<option value="">Sélectionnez d\'abord la version source</option>';
        return;
    }

    const availableTargets = supportedMigrations
        .filter(migration => migration.from === fromVersion)
        .map(migration => ({
            value: migration.to,
            label: `FontAwesome ${migration.to} - ${migration.description}`
        }));

    if (availableTargets.length === 0) {
        toVersionSelect.disabled = true;
        toVersionSelect.innerHTML = '<option value="">Aucune migration disponible</option>';
        return;
    }

    toVersionSelect.disabled = false;
    toVersionSelect.innerHTML = '<option value="">Choisissez la version cible</option>';

    availableTargets.forEach(target => {
        const option = document.createElement('option');
        option.value = target.value;
        option.textContent = target.label;
        toVersionSelect.appendChild(option);
    });

    // Sélectionner automatiquement s'il n'y a qu'une option
    if (availableTargets.length === 1) {
        toVersionSelect.value = availableTargets[0].value;
        updateMigrationInfo();
    }
}

// Mettre à jour les informations de migration
function updateMigrationInfo() {
    const fromVersion = document.getElementById('fromVersion').value;
    const toVersion = document.getElementById('toVersion').value;
    const migrationInfo = document.getElementById('migrationInfo');

    if (!fromVersion && !toVersion) {
        migrationInfo.innerHTML = '<i class="bi bi-info-circle"></i> Sélectionnez les versions pour voir les détails de migration';
        return;
    }

    if (fromVersion && !toVersion) {
        migrationInfo.innerHTML = `<i class="bi bi-arrow-right text-primary"></i> Depuis FontAwesome ${fromVersion} - Choisissez la version cible`;
        return;
    }

    if (fromVersion && toVersion) {
        const migration = supportedMigrations.find(m => m.from === fromVersion && m.to === toVersion);
        if (migration) {
            migrationInfo.innerHTML = `<i class="bi bi-check-circle text-success"></i> Migration ${fromVersion}→${toVersion} : ${migration.description}`;
        } else {
            migrationInfo.innerHTML = `<i class="bi bi-x-circle text-danger"></i> Migration ${fromVersion}→${toVersion} non supportée`;
        }
        return;
    }

    if (!fromVersion && toVersion) {
        migrationInfo.innerHTML = `<i class="bi bi-arrow-left text-primary"></i> Vers FontAwesome ${toVersion} - La version source sera détectée automatiquement`;
    }
}

// Mettre à jour l'état des boutons
function updateButtonStates() {
    const fromVersion = document.getElementById('fromVersion').value;
    const toVersion = document.getElementById('toVersion').value;
    const startMigrationBtn = document.getElementById('startMigration');

    // Le bouton est activé si on a au moins une version ou si on a fromVersion
    const canStart = fromVersion || toVersion;
    startMigrationBtn.disabled = !canStart;
}

// Générer et afficher la commande
function generateAndShowCommand() {
    const command = generateCommand();
    const commandOutput = document.getElementById('commandOutput');
    const commandText = document.getElementById('commandText');

    commandText.textContent = command;
    commandOutput.style.display = 'block';
}

// Générer la commande Artisan
function generateCommand() {
    const fromVersion = document.getElementById('fromVersion').value;
    const toVersion = document.getElementById('toVersion').value;
    const mode = document.getElementById('migrationMode').value;
    const dryRun = document.getElementById('dryRun').checked;
    const generateReport = document.getElementById('generateReport').checked;

    let command = 'php artisan fontawesome:migrate';

    // Ajouter les options de version
    if (fromVersion) command += ` --from=${fromVersion}`;
    if (toVersion) command += ` --to=${toVersion}`;

    // Ajouter les options de mode
    if (mode === 'icons-only') command += ' --icons-only';
    if (mode === 'assets-only') command += ' --assets-only';

    // Ajouter les autres options
    if (dryRun) command += ' --dry-run';
    if (generateReport) command += ' --report';
    command += ' --no-interactive';

    return command;
}

// Copier la commande dans le presse-papier
function copyCommand() {
    const commandText = document.getElementById('commandText');
    const text = commandText.textContent;

    navigator.clipboard.writeText(text).then(() => {
        showAlert('Commande copiée dans le presse-papier');
    }).catch(() => {
        // Fallback pour les anciens navigateurs
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showAlert('Commande copiée dans le presse-papier');
    });
}

// Exécuter la migration multi-versions
async function runMultiVersionMigration() {
    const fromVersion = document.getElementById('fromVersion').value;
    const toVersion = document.getElementById('toVersion').value;
    const mode = document.getElementById('migrationMode').value;
    const dryRun = document.getElementById('dryRun').checked;
    const generateReport = document.getElementById('generateReport').checked;

    const startBtn = document.getElementById('startMigration');
    const output = document.getElementById('test-output');
    const result = document.getElementById('test-result');

    // Désactiver le bouton et afficher le loading
    startBtn.disabled = true;
    startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Migration en cours...';
    output.style.display = 'block';
    result.innerHTML = `<i class="bi bi-rocket"></i> Lancement de la migration ${fromVersion || 'auto'}→${toVersion || 'auto'}...\n`;

    try {
        const response = await fetch('/fontawesome-migrator/tests/migration-multi-version', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({
                from: fromVersion || null,
                to: toVersion || null,
                mode: mode,
                dry_run: dryRun,
                report: generateReport
            })
        });

        const data = await response.json();

        if (data.success) {
            result.innerHTML = `<i class="bi bi-check-square text-success"></i> Migration ${data.from_version || 'auto'}→${data.to_version || 'auto'} terminée avec succès!

<strong><i class="bi bi-rocket"></i> Commande :</strong>
${data.command}

<strong><i class="bi bi-terminal"></i> Résultat :</strong>
${data.output}

<i class="bi bi-clock"></i> Terminé à ${data.timestamp}`;

            showAlert('Migration terminée avec succès');
        } else {
            result.innerHTML = `<i class="bi bi-x-square text-danger"></i> Erreur lors de la migration

<strong><i class="bi bi-rocket"></i> Commande :</strong>
${data.command || 'Non disponible'}

<strong><i class="bi bi-exclamation-triangle"></i> Erreur :</strong>
${data.error || data.output}

<i class="bi bi-clock"></i> Terminé à ${data.timestamp}`;

            showAlert('Erreur lors de la migration', 'error');
        }
    } catch (error) {
        result.innerHTML = `<i class="bi bi-wifi-off text-danger"></i> Erreur de connexion\n\n${error.message}`;
        showAlert('Erreur de connexion', 'error');
    } finally {
        startBtn.disabled = false;
        startBtn.innerHTML = '<i class="bi bi-play-circle"></i> Démarrer Migration';
        updateButtonStates(); // Remettre l'état correct
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