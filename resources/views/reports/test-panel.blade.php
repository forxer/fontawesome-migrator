@extends('fontawesome-migrator::reports.layout')

@section('title', 'Panneau de Tests - FontAwesome Migrator')

@section('head-extra')
<style>
/* Styles spécifiques au panneau de test */
.test-section {
    background: var(--surface-color);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    margin: var(--spacing-lg) 0;
}

.test-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-md);
    margin: var(--spacing-md) 0;
}

.test-btn {
    padding: var(--spacing-md) var(--spacing-lg);
    font-size: 1.1rem;
    transition: all 0.3s ease;
    position: relative;
}

.test-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.test-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
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

.test-output {
    background: var(--code-bg, #f8f9fa);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: var(--spacing-md);
    margin-top: var(--spacing-md);
    max-height: 400px;
    overflow-y: auto;
}

.test-output pre {
    margin: 0;
    white-space: pre-wrap;
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
}

.sessions-section {
    margin: var(--spacing-xl) 0;
}

.sessions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-md);
    margin-top: var(--spacing-md);
}

.session-card {
    background: var(--surface-color);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius);
    padding: var(--spacing-md);
    transition: all 0.3s ease;
}

.session-card:hover {
    border-color: var(--primary-color);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.session-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-sm);
}

.session-header h3 {
    margin: 0;
    font-size: 1.1rem;
}

.session-badges {
    display: flex;
    gap: var(--spacing-xs);
}

.session-details {
    margin: var(--spacing-sm) 0;
}

.session-stat {
    margin: var(--spacing-xs) 0;
    font-size: 0.9rem;
}

.session-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: var(--spacing-md);
    padding-top: var(--spacing-sm);
    border-top: 1px solid var(--border-color);
}

.cleanup-section {
    background: var(--surface-color);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    margin: var(--spacing-lg) 0;
}

.cleanup-buttons {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-md);
}

.btn-small {
    padding: var(--spacing-xs) var(--spacing-sm);
    font-size: 0.9rem;
}

@keyframes spin {
    0% { transform: translateY(-50%) rotate(0deg); }
    100% { transform: translateY(-50%) rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .test-buttons {
        grid-template-columns: 1fr;
    }
    
    .sessions-grid {
        grid-template-columns: 1fr;
    }
    
    .cleanup-buttons {
        flex-direction: column;
    }
}
</style>
@endsection

@section('content')
<div class="container">
    <div class="header">
        <h1>🧪 Panneau de Tests</h1>
        <div class="actions">
            <a href="{{ route('fontawesome-migrator.reports.index') }}" class="btn-secondary">
                ← Retour aux rapports
            </a>
        </div>
    </div>

    <!-- Statistiques des sauvegardes -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📁</div>
            <div class="stat-content">
                <div class="stat-value">{{ $backupStats['total_sessions'] }}</div>
                <div class="stat-label">Sessions</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💾</div>
            <div class="stat-content">
                <div class="stat-value">{{ $backupStats['total_backups'] }}</div>
                <div class="stat-label">Sauvegardes</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <div class="stat-value">{{ number_format($backupStats['total_size'] / 1024, 1, ',', ' ') }} KB</div>
                <div class="stat-label">Taille totale</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏰</div>
            <div class="stat-content">
                <div class="stat-value">
                    @if($backupStats['last_session'])
                        {{ $backupStats['last_session']['created_at'] }}
                    @else
                        Aucune
                    @endif
                </div>
                <div class="stat-label">Dernière session</div>
            </div>
        </div>
    </div>

    <!-- Boutons de test -->
    <div class="test-section">
        <h2>🚀 Tests de Migration</h2>
        <div class="test-buttons">
            <button onclick="runTest('dry-run')" class="btn-primary test-btn" data-type="dry-run">
                🔍 Test Dry-Run
            </button>
            <button onclick="runTest('icons-only')" class="btn-secondary test-btn" data-type="icons-only">
                🎯 Test Icônes Uniquement
            </button>
            <button onclick="runTest('assets-only')" class="btn-secondary test-btn" data-type="assets-only">
                🎨 Test Assets Uniquement
            </button>
            <button onclick="runTest('real')" class="btn-warning test-btn" data-type="real">
                ⚡ Test Réel (Attention!)
            </button>
        </div>
        
        <div id="test-output" class="test-output" style="display: none;">
            <h3>Résultat du test :</h3>
            <pre id="test-result"></pre>
        </div>
    </div>

    <!-- Sessions disponibles -->
    <div class="sessions-section">
        <h2>📋 Sessions Disponibles</h2>
        @if(count($sessions) > 0)
            <div class="sessions-grid">
                @foreach($sessions as $session)
                    <div class="session-card" data-session-id="{{ $session['session_id'] }}">
                        <div class="session-header">
                            <h3>Session {{ substr($session['session_id'], -8) }}</h3>
                            <div class="session-badges">
                                @if($session['dry_run'])
                                    <span class="badge badge-info">DRY-RUN</span>
                                @endif
                                <span class="badge badge-secondary">{{ $session['package_version'] ?? 'unknown' }}</span>
                            </div>
                        </div>
                        <div class="session-details">
                            <div class="session-stat">
                                <strong>📅 Créée :</strong> {{ $session['created_at'] }}
                            </div>
                            <div class="session-stat">
                                <strong>💾 Sauvegardes :</strong> {{ $session['backup_count'] }}
                            </div>
                            @if($session['duration'])
                                <div class="session-stat">
                                    <strong>⏱️ Durée :</strong> {{ $session['duration'] }}s
                                </div>
                            @endif
                        </div>
                        <div class="session-actions">
                            <button onclick="inspectSession('{{ $session['session_id'] }}')" class="btn-small btn-primary">
                                🔍 Inspecter
                            </button>
                            @if ($session['has_metadata'])
                                <span class="badge badge-success">✓ Métadonnées</span>
                            @else
                                <span class="badge badge-error">✗ Métadonnées</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>Aucune session trouvée</h3>
                <p>Lancez un test de migration pour créer votre première session.</p>
            </div>
        @endif
    </div>

    <!-- Modal d'inspection des sessions -->
    <div id="session-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🔍 Inspection de Session</h3>
                <button onclick="closeModal('session-modal')" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="session-details">
                    <!-- Contenu chargé via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <!-- Actions de nettoyage -->
    <div class="cleanup-section">
        <h2>🧹 Nettoyage</h2>
        <div class="cleanup-buttons">
            <button onclick="cleanupSessions(7)" class="btn-warning">
                🗑️ Nettoyer sessions > 7 jours
            </button>
            <button onclick="cleanupSessions(1)" class="btn-danger">
                🗑️ Nettoyer sessions > 1 jour
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Fonctions JavaScript pour les tests
async function runTest(type) {
    const button = document.querySelector(`[data-type="${type}"]`);
    const output = document.getElementById('test-output');
    const result = document.getElementById('test-result');
    
    // Désactiver le bouton et afficher le loading
    button.disabled = true;
    button.classList.add('loading');
    output.style.display = 'block';
    result.textContent = `🚀 Lancement du test ${type}...\n`;
    
    try {
        const response = await fetch('/fontawesome-migrator/test/migration', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ type: type })
        });
        
        const data = await response.json();
        
        if (data.success) {
            result.textContent = `✅ Test ${type} terminé avec succès!\n\nSortie de la commande:\n${data.output}\n\nSessions disponibles: ${data.sessions.length}`;
            
            // Recharger la page pour voir les nouvelles sessions
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            result.textContent = `❌ Erreur lors du test ${type}:\n\n${data.error || data.output}`;
        }
    } catch (error) {
        result.textContent = `❌ Erreur de connexion:\n\n${error.message}`;
    } finally {
        button.disabled = false;
        button.classList.remove('loading');
    }
}

async function inspectSession(sessionId) {
    try {
        const response = await fetch(`/fontawesome-migrator/test/session/${sessionId}`);
        const data = await response.json();
        
        if (data.error) {
            alert('Erreur: ' + data.error);
            return;
        }
        
        const details = document.getElementById('session-details');
        details.innerHTML = `
            <h4>Session: ${data.session_id}</h4>
            <p><strong>Répertoire:</strong> ${data.session_dir}</p>
            <p><strong>Nombre de fichiers:</strong> ${data.files_count}</p>
            
            <h5>📋 Métadonnées:</h5>
            <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;">${JSON.stringify(data.metadata, null, 2)}</pre>
            
            <h5>📁 Fichiers de sauvegarde:</h5>
            <ul>
                ${data.backup_files.map(file => `
                    <li>
                        <strong>${file.name}</strong> 
                        (${(file.size / 1024).toFixed(1)} KB, ${file.modified})
                    </li>
                `).join('')}
            </ul>
        `;
        
        document.getElementById('session-modal').style.display = 'flex';
    } catch (error) {
        alert('Erreur lors de l\'inspection: ' + error.message);
    }
}

async function cleanupSessions(days) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer toutes les sessions de plus de ${days} jour(s) ?`)) {
        return;
    }
    
    try {
        const response = await fetch('/fontawesome-migrator/test/cleanup-sessions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ days: days })
        });
        
        const data = await response.json();
        alert(`${data.message}\nSessions supprimées: ${data.deleted}`);
        
        // Recharger la page
        location.reload();
    } catch (error) {
        alert('Erreur lors du nettoyage: ' + error.message);
    }
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Fermer les modales en cliquant à l'extérieur
window.addEventListener('click', function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>
@endsection