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
            result.textContent = `✅ Test ${type} terminé avec succès!\n\nSortie de la commande:\n${data.output}\n\nSessions disponibles: ${data.sessions.length}\n\n⏱️ Test terminé à ${data.timestamp}`;
            
            // Ne pas recharger automatiquement, laisser l'utilisateur voir le résultat
            // Ajouter un bouton pour recharger manuellement
            const reloadBtn = document.createElement('button');
            reloadBtn.textContent = '🔄 Recharger la page pour voir les nouvelles sessions';
            reloadBtn.className = 'btn-primary';
            reloadBtn.style.marginTop = '10px';
            reloadBtn.onclick = () => location.reload();
            output.appendChild(reloadBtn);
        } else {
            result.textContent = `❌ Erreur lors du test ${type}:\n\n${data.error || data.output}\n\n⏱️ Test terminé à ${data.timestamp}`;
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

// Délégation d'événements pour les boutons d'inspection (fonctionne après rechargement)
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('inspect-session-btn')) {
        const sessionId = event.target.getAttribute('data-session-id');
        inspectSession(sessionId);
    }
});
</script>