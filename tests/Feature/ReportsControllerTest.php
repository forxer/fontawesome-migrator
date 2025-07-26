<?php

namespace FontAwesome\Migrator\Tests\Feature;

use FontAwesome\Migrator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class ReportsControllerTest extends TestCase
{
    protected string $reportPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportPath = storage_path('app/public/fontawesome-migrator/reports');

        // Create test reports directory
        File::makeDirectory($this->reportPath, 0755, true, true);
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (File::exists($this->reportPath)) {
            File::deleteDirectory(\dirname($this->reportPath));
        }

        parent::tearDown();
    }

    public function test_can_access_reports_index(): void
    {
        $response = $this->get('/fontawesome-migrator/reports');

        $response->assertStatus(200);
        $response->assertViewIs('fontawesome-migrator::reports.index');
        $response->assertSee('FontAwesome Migrator');
        $response->assertSee('Gestion des rapports de migration');
    }

    public function test_displays_empty_state_when_no_reports(): void
    {
        $response = $this->get('/fontawesome-migrator/reports');

        $response->assertStatus(200);
        $response->assertSee('Aucun rapport disponible');
        $response->assertSee('php artisan fontawesome:migrate --report');
    }

    public function test_displays_reports_when_available(): void
    {
        // Create test report files
        $htmlContent = '<html><body><h1>Test Report</h1></body></html>';
        $jsonContent = json_encode(['test' => 'data']);

        File::put($this->reportPath.'/test-report-2024-01-15_14-30-25.html', $htmlContent);
        File::put($this->reportPath.'/test-report-2024-01-15_14-30-25.json', $jsonContent);

        $response = $this->get('/fontawesome-migrator/reports');

        $response->assertStatus(200);
        $response->assertSee('test-report-2024-01-15_14-30-25');
        $response->assertSee('1 rapport(s) disponible(s)');
        $response->assertSee('Voir HTML');
        $response->assertSee('Voir JSON');
    }

    public function test_can_view_html_report(): void
    {
        $htmlContent = '<html><body><h1>Test Report</h1></body></html>';
        File::put($this->reportPath.'/test-report.html', $htmlContent);

        // Créer le fichier JSON correspondant pour que le contrôleur utilise les vues Blade
        $jsonData = [
            'meta' => [
                'generated_at' => '2025-07-26T10:30:00+00:00',
                'package_version' => '1.3.0',
                'dry_run' => false,
                'migration_options' => [],
                'configuration' => [],
            ],
            'summary' => [
                'total_files' => 1,
                'modified_files' => 1,
                'total_changes' => 1,
                'migration_success' => true,
            ],
            'files' => [
                [
                    'file' => '/test/report.html',
                    'changes_count' => 1,
                    'warnings_count' => 0,
                    'assets_count' => 0,
                ],
            ],
        ];
        File::put($this->reportPath.'/test-report.json', json_encode($jsonData));

        $response = $this->get('/fontawesome-migrator/reports/test-report.html');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/html; charset=UTF-8');
        // Le contenu contient maintenant la vue Blade complète, on teste juste la présence du titre
        $response->assertSee('Rapport de Migration Font Awesome 5 → 6');
    }

    public function test_can_view_json_report(): void
    {
        $jsonData = ['test' => 'data', 'count' => 42];
        File::put($this->reportPath.'/test-report.json', json_encode($jsonData));

        $response = $this->get('/fontawesome-migrator/reports/test-report.json');

        $response->assertStatus(200);
        $response->assertJson($jsonData);
    }

    public function test_returns_404_for_missing_report(): void
    {
        $response = $this->get('/fontawesome-migrator/reports/nonexistent-report.html');

        $response->assertStatus(404);
    }

    public function test_can_delete_report(): void
    {
        // Create test files
        File::put($this->reportPath.'/test-report.html', '<html></html>');
        File::put($this->reportPath.'/test-report.json', '{}');

        $response = $this->delete('/fontawesome-migrator/reports/test-report.html');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Rapport supprimé avec succès']);

        // Verify files are deleted
        $this->assertFalse(File::exists($this->reportPath.'/test-report.html'));
        $this->assertFalse(File::exists($this->reportPath.'/test-report.json'));
    }

    public function test_delete_returns_404_for_missing_report(): void
    {
        $response = $this->delete('/fontawesome-migrator/reports/nonexistent.html');

        $response->assertStatus(404);
        $response->assertJson(['error' => 'Rapport non trouvé']);
    }

    public function test_can_cleanup_old_reports(): void
    {
        // Create test files with different timestamps
        $oldFile = $this->reportPath.'/old-report.html';
        $newFile = $this->reportPath.'/new-report.html';

        File::put($oldFile, '<html>Old</html>');
        File::put($newFile, '<html>New</html>');

        // Make the old file appear old
        touch($oldFile, time() - (40 * 24 * 60 * 60)); // 40 days ago

        $response = $this->post('/fontawesome-migrator/reports/cleanup', [
            'days' => 30,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'deleted', 'days']);

        // Old file should be deleted, new file should remain
        $this->assertFalse(File::exists($oldFile));
        $this->assertTrue(File::exists($newFile));
    }

    public function test_cleanup_with_no_old_files(): void
    {
        $response = $this->post('/fontawesome-migrator/reports/cleanup', [
            'days' => 30,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['deleted' => 0, 'days' => 30]);
    }
}
