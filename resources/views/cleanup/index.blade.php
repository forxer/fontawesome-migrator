@extends('fontawesome-migrator::layout')

@section('title', 'Nettoyage et Maintenance')

@section('content')
<div class="py-4">
    <x-fontawesome-migrator::page-header
        icon="recycle"
        title="Nettoyage et Maintenance"
        subtitle="Interface centralisée pour nettoyer et gérer vos migrations FontAwesome"
    />

    <div class="row g-4 mb-4">
        <!-- Statistiques -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-bar-chart"></i> Statistiques du stockage</h5>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total des migrations</span>
                        <span class="fw-bold fs-5">{{ $totalMigrations }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Migrations anciennes (30+ jours)</span>
                        <span class="fw-bold fs-5 {{ $old30Days > 0 ? 'text-warning' : 'text-success' }}">
                            {{ $old30Days }}
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Migrations de test (7+ jours)</span>
                        <span class="fw-bold fs-5 {{ $old7Days > 0 ? 'text-warning' : 'text-success' }}">
                            {{ $old7Days }}
                        </span>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Espace disque utilisé</span>
                        <span class="fw-bold fs-5">
                            {{ number_format($totalSize / 1024 / 1024, 1) }} MB
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions recommandées -->
        <div class="col-lg-6">
            <div class="card h-100 border-primary">
                <div class="card-body">
                    <h5 class="card-title text-primary"><i class="bi bi-lightbulb"></i> Actions recommandées</h5>

                    @if($old30Days > 0)
                    <div class="d-flex align-items-center mb-2 text-primary">
                        <i class="bi bi-info-circle me-2"></i>
                        <span>{{ $old30Days }} migrations anciennes peuvent être supprimées</span>
                    </div>
                    @endif

                    @if($old7Days > 0)
                    <div class="d-flex align-items-center mb-2 text-primary">
                        <i class="bi bi-info-circle me-2"></i>
                        <span>{{ $old7Days }} migrations de test peuvent être nettoyées</span>
                    </div>
                    @endif

                    @if($old30Days == 0 && $old7Days == 0)
                    <div class="d-flex align-items-center mb-2 text-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <span>Aucun nettoyage nécessaire pour le moment</span>
                    </div>
                    @endif

                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-lightbulb me-2"></i>
                        <span>Un nettoyage régulier maintient les performances optimales</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions de nettoyage -->
    <div class="row g-4 mb-4">
        <!-- Nettoyage migrations anciennes -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-clock-history fs-2 text-warning me-3"></i>
                        <h6 class="card-title mb-0">Migrations anciennes</h6>
                    </div>

                    <p class="card-text text-muted small mb-3">
                        Supprimer les migrations de plus de 30 jours (recommandé pour libérer de l'espace).
                    </p>

                    <form class="cleanup-form" data-action="cleanup_old_migrations">
                        <div class="mb-3">
                            <label class="form-label small">Âge minimum (jours)</label>
                            <input type="number" name="days" value="30" min="1" max="365" class="form-control form-control-sm">
                        </div>

                        <button type="submit"
                                class="btn btn-warning btn-sm w-100"
                                {{ $old30Days == 0 ? 'disabled' : '' }}>
                            <i class="bi bi-trash me-1"></i>
                            Nettoyer ({{ $old30Days }} migrations)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Nettoyage migrations de test -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-flask fs-2 text-info me-3"></i>
                        <h6 class="card-title mb-0">Migrations de test</h6>
                    </div>

                    <p class="card-text text-muted small mb-3">
                        Supprimer les migrations créées via l'interface web de plus de 7 jours.
                    </p>

                    <form class="cleanup-form" data-action="cleanup_test_migrations">
                        <div class="mb-3">
                            <label class="form-label small">Âge minimum (jours)</label>
                            <input type="number" name="days" value="7" min="1" max="30" class="form-control form-control-sm">
                        </div>

                        <button type="submit"
                                class="btn btn-info btn-sm w-100"
                                {{ $old7Days == 0 ? 'disabled' : '' }}>
                            <i class="bi bi-flask me-1"></i>
                            Nettoyer tests ({{ $old7Days }} migrations)
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Nettoyage complet -->
        <div class="col-md-12 col-lg-4">
            <div class="card h-100 border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-lightning fs-2 text-danger me-3"></i>
                        <h6 class="card-title mb-0">Nettoyage complet</h6>
                    </div>

                    <p class="card-text text-muted small mb-3">
                        Effectuer un nettoyage automatique : anciennes (30j+) + tests (7j+).
                    </p>

                    <form class="cleanup-form" data-action="cleanup_all">
                        <div class="alert alert-warning py-2 mb-3">
                            <div class="d-flex align-items-center small">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <span>Action recommandée pour un nettoyage optimal</span>
                            </div>
                        </div>

                        <button type="submit"
                                class="btn btn-danger btn-sm w-100"
                                {{ $old30Days + $old7Days == 0 ? 'disabled' : '' }}>
                            <i class="bi bi-lightning me-1"></i>
                            Nettoyage complet ({{ $old30Days + $old7Days }} migrations)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Option NUCLÉAIRE - Supprimer TOUT -->
    @if($totalMigrations > 0)
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-danger bg-danger bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-radioactive fs-2 text-danger me-3"></i>
                        <div>
                            <h5 class="card-title text-danger mb-1">⚠️ SUPPRESSION TOTALE</h5>
                            <p class="card-text text-danger small mb-0">Option nucléaire - Supprimer TOUTES les migrations sans exception</p>
                        </div>
                    </div>
                    
                    <div class="alert alert-danger mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <div>
                                <strong>ATTENTION :</strong> Cette action supprimera définitivement TOUTES les {{ $totalMigrations }} migrations, 
                                y compris les récentes et importantes. Cette action est IRRÉVERSIBLE !
                            </div>
                        </div>
                    </div>
                    
                    <form class="cleanup-form" data-action="cleanup_everything">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="confirmNuclear" required>
                                <label class="form-check-label text-danger" for="confirmNuclear">
                                    <strong>Je comprends que cette action supprimera DÉFINITIVEMENT toutes les migrations</strong>
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" 
                                class="btn btn-danger w-100"
                                data-confirm-text="ÊTES-VOUS ABSOLUMENT CERTAIN de vouloir supprimer TOUTES les migrations ? Cette action est IRRÉVERSIBLE !">
                            <i class="bi bi-radioactive me-1"></i>
                            ⚠️ SUPPRIMER TOUT ({{ $totalMigrations }} migrations)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Liste des migrations pour suppression sélective -->
    @if(count($migrations) > 0)
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="bi bi-list-ul"></i> Gestion sélective des migrations</h5>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Migration</th>
                            <th>Type</th>
                            <th>Âge</th>
                            <th>Taille</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($migrations->take(20) as $migration)
                        <tr>
                            <td>
                                <div>
                                    <div class="fw-bold">{{ $migration['short_id'] }}</div>
                                    <div class="text-muted small">{{ $migration['created_at']->format('d/m/Y H:i') }}</div>
                                </div>
                            </td>
                            <td>
                                @if(($migration['migration_source'] ?? 'command_line') === 'web_interface')
                                    <span class="badge bg-primary">
                                        <i class="bi bi-globe me-1"></i>Web
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-terminal me-1"></i>CLI
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted small">
                                {{ $migration['created_at']->diffForHumans() }}
                            </td>
                            <td class="text-muted small">
                                {{ number_format(($migration['directory_size'] ?? 0) / 1024, 1) }} KB
                            </td>
                            <td>
                                <button type="button"
                                        class="btn btn-outline-danger btn-sm delete-migration-btn"
                                        data-migration-id="{{ $migration['short_id'] }}"
                                        data-migration-date="{{ $migration['created_at']->format('d/m/Y H:i') }}">
                                    <i class="bi bi-trash"></i>
                                    Supprimer
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if(count($migrations) > 20)
                <div class="text-center mt-3">
                    <p class="text-muted small">
                        Affichage de 20 migrations sur {{ count($migrations) }} total.
                        <a href="{{ route('fontawesome-migrator.migrations.index') }}" class="link-primary">
                            Voir toutes les migrations
                        </a>
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Messages d'état -->
    <div id="cleanup-messages" class="mt-4" style="display: none;"></div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.cleanup-form');
    const messagesContainer = document.getElementById('cleanup-messages');

    forms.forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const action = form.dataset.action;
            const submitButton = form.querySelector('button[type="submit"]');
            
            // Confirmation spéciale pour l'action nucléaire
            if (action === 'cleanup_everything') {
                const confirmText = submitButton.dataset.confirmText;
                if (!confirm(confirmText)) {
                    return;
                }
                
                // Double confirmation pour l'option nucléaire
                if (!confirm('DERNIÈRE CHANCE : Voulez-vous vraiment EFFACER TOUTES LES MIGRATIONS ?')) {
                    return;
                }
            }
            
            const formData = new FormData(form);
            formData.append('action', action);
            const originalText = submitButton.innerHTML;

            // État de chargement
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Nettoyage...';

            try {
                const response = await fetch('{{ route("fontawesome-migrator.cleanup.execute") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    showMessage(result.message, 'success');
                    // Recharger la page après 2 secondes pour actualiser les stats
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showMessage(result.error || 'Erreur lors du nettoyage', 'danger');
                }
            } catch (error) {
                showMessage('Erreur de communication avec le serveur', 'danger');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }
        });
    });

    // Gestion suppression sélective
    document.querySelectorAll('.delete-migration-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const migrationId = this.dataset.migrationId;
            const migrationDate = this.dataset.migrationDate;

            if (!confirm(`Supprimer définitivement la migration ${migrationId} (${migrationDate}) ?`)) {
                return;
            }

            const originalText = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            try {
                const formData = new FormData();
                formData.append('action', 'delete_specific');
                formData.append('migration_id', migrationId);

                const response = await fetch('{{ route("fontawesome-migrator.cleanup.execute") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    showMessage(result.message, 'success');
                    // Retirer la ligne du tableau
                    this.closest('tr').remove();
                } else {
                    showMessage(result.error || 'Erreur lors de la suppression', 'danger');
                }
            } catch (error) {
                showMessage('Erreur de communication avec le serveur', 'danger');
            } finally {
                this.disabled = false;
                this.innerHTML = originalText;
            }
        });
    });

    function showMessage(text, type) {
        const alertClass = `alert-${type}`;
        const iconClass = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';

        const message = document.createElement('div');
        message.className = `alert ${alertClass} alert-dismissible fade show`;
        message.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi ${iconClass} me-2"></i>
                <span>${text}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;

        messagesContainer.appendChild(message);
        messagesContainer.style.display = 'block';

        // Retirer le message après 5 secondes
        setTimeout(() => {
            message.remove();
            if (messagesContainer.children.length === 0) {
                messagesContainer.style.display = 'none';
            }
        }, 5000);
    }
});
</script>
@endsection