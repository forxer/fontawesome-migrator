@extends('fontawesome-migrator::layout')

@section('title', 'Rapport de Migration Font Awesome 5 → 6')

@section('body-background', '#f5f5f5')

@section('content')
    <div class="header">
        <h1>📊 Rapport de Migration Font Awesome 5 → 6</h1>
        <p>Généré le {{ $timestamp }}</p>
    </div>

    <!-- Statistiques générales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_files'] }}</div>
            <div class="stat-label">Fichiers analysés</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number">{{ $stats['modified_files'] }}</div>
            <div class="stat-label">Fichiers modifiés</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_changes'] }}</div>
            <div class="stat-label">Total des changements</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-number">{{ $stats['icons_migrated'] ?? 0 }}</div>
            <div class="stat-label">Icônes migrées</div>
        </div>
        
        @if(($stats['assets_migrated'] ?? 0) > 0)
        <div class="stat-card">
            <div class="stat-number">{{ $stats['assets_migrated'] }}</div>
            <div class="stat-label">Assets migrés</div>
        </div>
        @endif
    </div>

    @if($stats['total_changes'] > 0)
        <!-- Résumé de migration -->
        <div class="section">
            <h2>📋 Résumé de la migration</h2>
            
            @if($stats['migration_success'])
                <div class="alert alert-success">
                    ✅ Migration terminée avec succès ! {{ $stats['total_changes'] }} changement(s) appliqué(s) sur {{ $stats['modified_files'] }} fichier(s).
                </div>
            @else
                <div class="alert alert-warning">
                    ⚠️ Migration partielle. Certains éléments n'ont pas pu être migrés automatiquement.
                </div>
            @endif

            @if(!empty($stats['changes_by_type']))
                <table>
                    <tr><th>Type de changement</th><th>Nombre</th><th>Pourcentage</th></tr>
                    @foreach($stats['changes_by_type'] as $type => $count)
                        @php
                            $percentage = $stats['total_changes'] > 0 ? round(($count / $stats['total_changes']) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td><span class="badge">{{ ucfirst($type) }}</span></td>
                            <td>{{ $count }}</td>
                            <td>{{ $percentage }}%</td>
                        </tr>
                    @endforeach
                </table>
            @endif
        </div>

        <!-- Section des assets si présents -->
        @if(!empty($stats['asset_types']))
            <div class="section">
                <h2>🎨 Assets détectés</h2>
                <table>
                    <tr><th>Type d'asset</th><th>Nombre</th><th>Description</th></tr>
                    @foreach($stats['asset_types'] as $assetType => $count)
                        <tr>
                            <td><strong>{{ $assetType }}</strong></td>
                            <td>{{ $count }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $assetType)) }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        <!-- Détail des fichiers modifiés -->
        <div class="section">
            <h2>📄 Détail des modifications</h2>
            
            @foreach($results as $result)
                @if(!empty($result['changes']))
                    <div class="file-item">
                        <div class="file-path">📁 {{ $result['file'] }}</div>
                        
                        @foreach($result['changes'] as $change)
                            <div class="change-item">
                                <div class="change-from">- {{ $change['from'] }}</div>
                                <div class="change-to">+ {{ $change['to'] }}</div>
                                @if(isset($change['line']))
                                    <small style="color: var(--gray-500);">Ligne {{ $change['line'] }}</small>
                                @endif
                            </div>
                        @endforeach

                        @if(!empty($result['warnings']))
                            @foreach($result['warnings'] as $warning)
                                <div class="alert alert-warning">⚠️ {{ $warning }}</div>
                            @endforeach
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    @else
        <!-- Aucun changement -->
        <div class="section">
            <div class="alert alert-info">
                ℹ️ Aucun changement nécessaire. Votre code semble déjà compatible avec Font Awesome 6.
            </div>
        </div>
    @endif

    <!-- Configuration et options -->
    <div class="section">
        <h2>⚙️ Configuration de migration</h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <h3 style="margin: 0 0 10px 0; color: var(--gray-700);">Options utilisées</h3>
                <table style="margin-top: 0;">
                    <tr><td><strong>Mode</strong></td><td>{{ $isDryRun ? 'Dry-run (prévisualisation)' : 'Migration complète' }}</td></tr>
                    @if(!empty($migrationOptions['custom_path']))
                        <tr><td><strong>Chemin personnalisé</strong></td><td><code>{{ $migrationOptions['custom_path'] }}</code></td></tr>
                    @endif
                    @if($migrationOptions['icons_only'] ?? false)
                        <tr><td><strong>Migration</strong></td><td>Icônes uniquement</td></tr>
                    @elseif($migrationOptions['assets_only'] ?? false)
                        <tr><td><strong>Migration</strong></td><td>Assets uniquement</td></tr>
                    @else
                        <tr><td><strong>Migration</strong></td><td>Complète (icônes + assets)</td></tr>
                    @endif
                    <tr><td><strong>Sauvegarde</strong></td><td>
                        @if($migrationOptions['no_backup'] ?? false)
                            Désactivée
                        @elseif($migrationOptions['backup'] ?? false)
                            Forcée
                        @else
                            {{ ($configuration['backup_enabled'] ?? true) ? 'Activée' : 'Désactivée' }}
                        @endif
                    </td></tr>
                </table>
            </div>
            
            <div>
                <h3 style="margin: 0 0 10px 0; color: var(--gray-700);">Configuration</h3>
                <table style="margin-top: 0;">
                    <tr><td><strong>Type de licence</strong></td><td>{{ ucfirst($configuration['license_type'] ?? 'free') }}</td></tr>
                    <tr><td><strong>Chemins scannés</strong></td><td>
                        @if(!empty($configuration['scan_paths']))
                            @foreach($configuration['scan_paths'] as $path)
                                <code>{{ $path }}</code>@if(!$loop->last), @endif
                            @endforeach
                        @else
                            Non définis
                        @endif
                    </td></tr>
                    <tr><td><strong>Extensions</strong></td><td>
                        @if(!empty($configuration['file_extensions']))
                            @foreach($configuration['file_extensions'] as $ext)
                                <code>{{ $ext }}</code>@if(!$loop->last), @endif
                            @endforeach
                        @else
                            Toutes
                        @endif
                    </td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Informations de fin -->
    <div class="section">
        <h2>ℹ️ Informations supplémentaires</h2>
        <p><strong>Rapport généré :</strong> {{ $timestamp }}</p>
        <p><strong>Package :</strong> FontAwesome Migrator v{{ $packageVersion ?? '1.1.0' }}</p>
        
        @if($stats['total_changes'] > 0 && !$isDryRun)
            <div class="alert alert-info">
                💡 <strong>Conseil :</strong> Testez votre application pour vous assurer que tous les changements fonctionnent correctement.
            </div>
        @endif
        
        @if($isDryRun && $stats['total_changes'] > 0)
            <div class="alert alert-warning">
                🚀 <strong>Prêt pour la migration :</strong> Exécutez <code>php artisan fontawesome:migrate</code> pour appliquer ces changements.
            </div>
        @endif
    </div>
@endsection