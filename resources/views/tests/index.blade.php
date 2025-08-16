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
        :counterText="$backupStats['total_migrations'] . ' migration(s) de test'"
        counterIcon="folder"
        :hasActions="true"
        actionsLabel="Actions globales"
    >
        <x-slot name="actions">
            <li><a class="dropdown-item" href="#" onclick="refreshPage(); return false;">
                <span id="refresh-icon"><i class="bi bi-arrow-repeat"></i></span> Actualiser
            </a></li>
        </x-slot>
    </x-fontawesome-migrator::page-header>

    @if ($backupStats['total_migrations'] > 0)
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
                            <div class="fs-3 fw-bold text-primary">{{ $backupStats['total_migrations'] }}</div>
                            <div class="text-body-secondary small">Migrations</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-files fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ $backupStats['total_backups'] }}</div>
                            <div class="text-body-secondary small">Fichiers</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-hdd fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ human_readable_bytes_size($backupStats['total_size'], 2) }}</div>
                            <div class="text-body-secondary small">Taille totale</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-clock fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">
                                @if ($backupStats['last_migration'])
                                    {{ $backupStats['last_migration']->isoFormat('DD/MM') }}
                                @else
                                    -
                                @endif
                            </div>
                            <div class="text-body-secondary small">Dernière migration</div>
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
                <i class="bi bi-arrow-clockwise"></i> Migration multi-versions
            </h2>
            <form id="migrationForm" class="row g-3">
                <div class="col-md-4">
                    <label for="fromVersion" class="form-label">
                        <i class="bi bi-arrow-up-right"></i> Version Source
                    </label>
                    <select class="form-select" id="fromVersion" name="from">
                        <option value="">Détection automatique</option>
                        <option value="4">FontAwesome 4</option>
                        <option value="5">FontAwesome 5</option>
                        <option value="6">FontAwesome 6</option>
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

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="dryRun" name="dry_run" checked>
                        <label class="form-check-label" for="dryRun">
                            <i class="bi bi-eye"></i> Mode Dry-Run (simulation)
                        </label>
                        <div class="form-text">Recommandé pour tester avant migration réelle. Les résultats sont automatiquement enregistrés.</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div id="migrationInfo" class="text-body-secondary">
                            <i class="bi bi-info-circle"></i> Sélectionnez les versions pour voir les détails de migration
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" id="generateCommand"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="Générer la commande CLI correspondante aux options sélectionnées">
                                <i class="bi bi-terminal"></i> Générer Commande
                            </button>
                            <button type="submit" class="btn btn-primary" id="startMigration" disabled
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="Lancer la migration avec les paramètres configurés">
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
                            <button type="button" class="btn btn-sm btn-outline-light"
                                    onclick="copyCommand()"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="Copier la commande dans le presse-papier">
                                <i class="bi bi-clipboard"></i> Copier
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="test-output" class="mt-4" style="display: none;">
                <h3 class="section-title">Résultat de la migration :</h3>
                <pre id="test-result" class="bg-dark text-light p-3 rounded"></pre>
                <div id="migration-report-btn" class="mt-3" style="display: none;">
                    <a id="view-report-link" href="#" class="btn btn-success" target="_blank"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="Ouvrir le rapport détaillé de la migration dans un nouvel onglet">
                        <i class="bi bi-file-text"></i> Voir le rapport de migration
                    </a>
                </div>
            </div>
        </div>
    </div>



@endsection

@section('scripts')
<script>
// Configuration CSRF pour les requêtes AJAX
window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Migrations supportées (passées depuis le contrôleur)
const supportedMigrations = @json($supportedMigrations);

function showAlert(message, type = 'success') {
    showToast(message, type);
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

    let command = 'php artisan fontawesome:migrate';

    // Ajouter les options de version
    if (fromVersion) command += ` --from=${fromVersion}`;
    if (toVersion) command += ` --to=${toVersion}`;

    // Ajouter les options de mode
    if (mode === 'icons-only') command += ' --icons-only';
    if (mode === 'assets-only') command += ' --assets-only';

    // Ajouter les autres options
    if (dryRun) command += ' --dry-run';
    command += ' --no-interactive';

    return command;
}

// Copier la commande dans le presse-papier
function copyCommand() {
    const commandText = document.getElementById('commandText');
    const text = commandText.textContent;
    copyToClipboard(text, 'Commande copiée dans le presse-papier');
}

// Exécuter la migration multi-versions
async function runMultiVersionMigration() {
    const fromVersion = document.getElementById('fromVersion').value;
    const toVersion = document.getElementById('toVersion').value;
    const mode = document.getElementById('migrationMode').value;
    const dryRun = document.getElementById('dryRun').checked;

    const startBtn = document.getElementById('startMigration');
    const output = document.getElementById('test-output');
    const result = document.getElementById('test-result');

    // Désactiver le bouton et afficher le loading
    startBtn.disabled = true;
    startBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Migration en cours...';
    output.style.display = 'block';
    result.innerHTML = `<i class="bi bi-rocket"></i> Lancement de la migration ${fromVersion || 'auto'}→${toVersion || 'auto'}...\n`;

    // Masquer le bouton de rapport précédent
    const reportBtn = document.getElementById('migration-report-btn');
    reportBtn.style.display = 'none';

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
            })
        });

        const data = await response.json();

        if (data.success) {
            result.innerHTML = `<i class="bi bi-check-square text-success"></i> Migration ${data.from_version || 'auto'}→${data.to_version || 'auto'} terminée avec succès!

<strong><i class="bi bi-rocket"></i> Commande :</strong>
${data.command}

<strong><i class="bi bi-terminal"></i> Résultat :</strong>
<div style="padding: 10px; font-size: 0.9em; white-space: pre-wrap; max-height: 300px; overflow-y: auto; font-family: monospace;"></div>

<i class="bi bi-clock"></i> Terminé à ${data.timestamp}`;

            // Injecter le contenu de manière sécurisée
            const outputDiv = result.querySelector('div[style*="font-family: monospace"]');
            if (outputDiv) {
                outputDiv.textContent = data.output;
            }

            // Afficher le bouton du rapport si on a un migration_id
            const reportBtn = document.getElementById('migration-report-btn');
            const reportLink = document.getElementById('view-report-link');

            if (data.migration_id) {
                reportLink.href = `/fontawesome-migrator/migrations/${data.migration_id}`;
                reportBtn.style.display = 'block';
            }

            showAlert('Migration terminée avec succès');

            // Réinitialiser le formulaire après succès
            setTimeout(() => {
                resetMigrationForm();
            }, 2000); // Attendre 2 secondes pour que l'utilisateur voit les résultats
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

// Fonction pour réinitialiser le formulaire de migration
function resetMigrationForm() {
    const fromVersionSelect = document.getElementById('fromVersion');
    const toVersionSelect = document.getElementById('toVersion');
    const migrationMode = document.getElementById('migrationMode');
    const dryRun = document.getElementById('dryRun');
    const migrationInfo = document.getElementById('migrationInfo');
    const commandOutput = document.getElementById('commandOutput');

    // Réinitialiser les sélecteurs
    fromVersionSelect.value = '';
    toVersionSelect.innerHTML = '<option value="">Sélectionnez d\'abord la version source</option>';
    toVersionSelect.disabled = true;
    migrationMode.value = 'complete';
    dryRun.checked = true;

    // Réinitialiser les informations
    migrationInfo.innerHTML = '<i class="bi bi-info-circle"></i> Sélectionnez les versions pour voir les détails de migration';

    // Masquer la sortie de commande
    commandOutput.style.display = 'none';

    // Remettre à jour l'état des boutons
    updateButtonStates();
}


</script>
@endsection