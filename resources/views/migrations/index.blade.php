@extends('fontawesome-migrator::layout')

@section('title', 'Migrations FontAwesome')

@section('head-extra')
    @include('fontawesome-migrator::partials.css.bootstrap-common')
@endsection

@section('content')
    <x-fontawesome-migrator::page-header
        icon="folder"
        title="Migrations"
        subtitle="Historique et résultats des migrations FontAwesome"
        :counterText="count($reports) . ' migration(s) effectuée(s)'"
        counterIcon="folder"
        :hasActions="true"
        actionsLabel="Actions globales"
    >
        <x-slot name="actions">
            <li><a class="dropdown-item" href="#" onclick="refreshReports(); return false;">
                <span id="refresh-icon"><i class="bi bi-arrow-repeat"></i></span> Actualiser
            </a></li>
        </x-slot>
    </x-fontawesome-migrator::page-header>

    @if (count($reports) > 0)
        <!-- Statistiques globales enrichies -->
        <div class="mb-4">
            <h2 class="section-title">
                <i class="bi bi-bar-chart text-primary"></i> Statistiques des migrations
            </h2>
            <div class="row g-3">
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-folder fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ number_formatted(count($reports)) }}</div>
                            <div class="text-body-secondary small">Migrations</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-files fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ number_formatted($stats['total_backups']) }}</div>
                            <div class="text-body-secondary small">Fichiers sauvegardés</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-hdd fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">{{ human_readable_bytes_size($stats['total_size'], 2) }}</div>
                            <div class="text-body-secondary small">Taille totale</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <i class="bi bi-calendar fs-1 text-primary mb-2"></i>
                            <div class="fs-3 fw-bold text-primary">
                                @if ($stats['last_migration'])
                                    {{ $stats['last_migration']->isoFormat('DD/MM') }}
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

    <div id="alerts"></div>

    @if (count($reports) > 0)
        <div class="row g-4">
            @foreach ($reports as $report)
                <div class="col-md-6 col-xl-4 mb-4">
                    <div class="card h-100 shadow-sm" data-migration="{{ $report['migration_id'] }}">
                        <div class="card-header d-flex justify-content-between align-items-center gap-3">
                                <h5 class="card-title mb-1 text-truncate">
                                    <i class="bi bi-file-text text-primary fs-4"></i>
                                   {{ $report['created_at']->isoFormat('DD/MM [à] HH:mm') }}
                                </h5>
                                <x-fontawesome-migrator::migration-mode-badge :dry-run="$report['dry_run']" />
                        </div>
                        <div class="card-body py-4">
                            <div class="row g-3 text-center">
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="fw-semibold">{{ number_formatted($report['statistics']['total_files']) }}</div>
                                        <div class="text-body-secondary small"><i class="bi bi-file-code"></i> Fichier(s) analisé(s)</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="fw-semibold">
                                            @if (isset($report['migration_summary']['total_changes']))
                                                {{ number_formatted($report['migration_summary']['total_changes']) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                        <div class="text-body-secondary small"><i class="bi bi-file-text"></i> Changement(s)</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="fw-semibold">
                                            @if ($report['migration_origin'] === 'web_interface')
                                                <i class="bi bi-globe text-info"></i> Web
                                            @else
                                                <i class="bi bi-terminal text-secondary"></i> CLI
                                            @endif
                                        </div>
                                        <div class="text-body-secondary small"><i class="bi bi-arrow-right"></i> Origine</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="border rounded p-3">
                                        <div class="fw-semibold">
                                            @php
                                                $sourceVersion = $report['migration_options']['source_version'] ?? null;
                                                $targetVersion = $report['migration_options']['target_version'] ?? null;
                                            @endphp
                                            @if ($sourceVersion && $targetVersion)
                                                FA{{ $sourceVersion }} → FA{{ $targetVersion }}
                                            @elseif($sourceVersion)
                                                Depuis FA{{ $sourceVersion }}
                                            @elseif($targetVersion)
                                                Vers FA{{ $targetVersion }}
                                            @else
                                                {{ $report['short_id'] }}
                                            @endif
                                        </div>
                                        <div class="text-body-secondary small">
                                            <i class="bi bi-arrow-repeat"></i>
                                            @if ($sourceVersion || $targetVersion)
                                                Versions
                                            @else
                                                Migration ID
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light">
                            <div class="btn-group btn-group-sm d-flex flex-wrap" role="group" aria-label="Actions du rapport">
                                <a href="{{ route('fontawesome-migrator.migrations.show', $report['short_id']) }}"
                                   class="btn btn-primary"
                                   data-bs-toggle="tooltip"
                                   data-bs-placement="top"
                                   title="Voir le rapport détaillé avec toutes les modifications">
                                    <i class="bi bi-file-text"></i> Rapport
                                </a>
                                <button onclick="viewJSON('{{ $report['short_id'] }}')"
                                        class="btn btn-outline-primary"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Télécharger les métadonnées au format JSON brut">
                                    <i class="bi bi-database"></i> JSON
                                </button>
                                <button onclick="inspectMigration('{{ $report['short_id'] }}')"
                                        class="btn btn-outline-secondary"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Inspecter les fichiers de sauvegarde et métadonnées">
                                    <i class="bi bi-search"></i> Inspecter
                                </button>
                                <button onclick="deleteReport('{{ $report['short_id'] }}')"
                                        class="btn btn-outline-danger"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Supprimer définitivement cette migration et ses sauvegardes">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card mb-3">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-file-text display-1 text-body-secondary"></i>
                </div>
                <h3 class="mb-3">Aucune migration disponible</h3>
                <p class="text-body-secondary mb-4">
                    Les migrations sont automatiquement enregistrées avec leurs résultats.<br>
                    Exécutez une migration pour voir l'historique des changements FontAwesome.
                </p>
                <div class="mb-4">
                    <code class="bg-light p-3 rounded d-inline-block">
                        php artisan fontawesome:migrate --dry-run
                    </code>
                </div>
                <div class="text-body-secondary">
                    <i class="bi bi-info-circle me-1"></i> Ajouter <code class="bg-light px-2 py-1 rounded">--dry-run</code> permet de prévisualiser sans modifier les fichiers
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
<script>
    function showAlert(message, type = 'success') {
        showBootstrapAlert(message, type, 'alerts');
    }

    function refreshReports() {
        const refreshIcon = document.getElementById('refresh-icon');
        if (refreshIcon) {
            refreshIcon.innerHTML = '<i class="bi bi-arrow-repeat me-2 spinner-border spinner-border-sm"></i>';
        }

        // Recharger la page après un court délai
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }

    async function deleteReport(migrationId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette migration ?')) {
            return;
        }

        try {
            const response = await fetch(`/fontawesome-migrator/migrations/${migrationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (response.ok) {
                showAlert('Migration supprimée avec succès');
                // Masquer la carte de la migration
                const card = document.querySelector(`[data-migration="${migrationId}"]`);
                if (card) {
                    card.style.opacity = '0.5';
                    card.style.pointerEvents = 'none';
                    setTimeout(() => card.remove(), 300);
                }
            } else {
                showAlert(data.error || 'Erreur lors de la suppression', 'error');
            }
        } catch (error) {
            showAlert('Erreur de connexion', 'error');
        }
    }


    // Fonction pour voir le JSON
    function viewJSON(migrationId) {
        // Ouvrir dans une nouvelle fenêtre avec les headers appropriés
        fetch(`/fontawesome-migrator/migrations/${migrationId}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            // Créer une nouvelle fenêtre avec le JSON formaté
            const jsonWindow = window.open('', '_blank');
            jsonWindow.document.write('<html><head><title>Migration JSON</title></head><body>');
            jsonWindow.document.write('<pre style="padding: 20px; font-family: monospace;">');
            jsonWindow.document.write(JSON.stringify(data, null, 2));
            jsonWindow.document.write('</pre></body></html>');
            jsonWindow.document.close();
        })
        .catch(error => {
            showAlert('Erreur lors de la récupération du JSON', 'error');
        });
    }

    // Fonction pour inspecter une migration
    async function inspectMigration(migrationId) {
        try {
            const response = await fetch(`/fontawesome-migrator/migrations/${migrationId}/inspect`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Créer une modal Bootstrap pour afficher les détails
                const modalHtml = `
                    <div class="modal fade" id="inspectModal" tabindex="-1" style="z-index: 9999;">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header bg-info bg-opacity-10">
                                    <h5 class="modal-title">
                                        <i class="bi bi-search text-info"></i> Inspection de la migration
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <h6 class="text-body-secondary">Identifiant</h6>
                                        <p class="font-monospace">${data.migration_id}</p>
                                    </div>

                                    <div class="mb-3">
                                        <h6 class="text-body-secondary">Répertoire</h6>
                                        <p class="font-monospace small">${data.migration_dir}</p>
                                    </div>

                                    <div class="mb-3">
                                        <h6 class="text-body-secondary">Fichiers de sauvegarde (${data.files_count})</h6>
                                        ${data.backup_files && data.backup_files.length > 0 ? `
                                            <div class="list-group">
                                                ${data.backup_files.map(file => `
                                                    <div class="list-group-item py-2">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="font-monospace small">${file.name}</span>
                                                            <span class="badge bg-secondary">${(file.size / 1024).toFixed(2)} KB</span>
                                                        </div>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        ` : '<p class="text-body-secondary">Aucun fichier de sauvegarde</p>'}
                                    </div>

                                    <div class="mb-3">
                                        <h6 class="text-body-secondary">Métadonnées</h6>
                                        <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">
${JSON.stringify(data.metadata, null, 2)}
                                        </pre>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Supprimer modal existante si présente
                const existing = document.getElementById('inspectModal');
                if (existing) existing.remove();

                // Supprimer backdrop existant si présent
                const existingBackdrop = document.querySelector('.modal-backdrop');
                if (existingBackdrop) existingBackdrop.remove();

                // Ajouter la nouvelle modal
                document.body.insertAdjacentHTML('beforeend', modalHtml);

                // Créer et afficher la modal
                const modalElement = document.getElementById('inspectModal');
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                });
                modal.show();

                // Nettoyer après fermeture
                modalElement.addEventListener('hidden.bs.modal', function() {
                    this.remove();
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                });

            } else {
                showAlert(data.error || 'Erreur lors de l\'inspection de la migration', 'error');
            }
        } catch (error) {
            showAlert('Erreur de connexion', 'error');
        }
    }
</script>
@endsection