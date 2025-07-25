<?php

namespace FontAwesome\Migrator\Tests\Unit\Services;

use FontAwesome\Migrator\Services\MigrationReporter;
use FontAwesome\Migrator\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigrationReporterTest extends TestCase
{
    protected MigrationReporter $reporter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reporter = new MigrationReporter();
    }

    /** @test */
    public function it_includes_asset_information_in_reports()
    {
        $results = [
            [
                'file' => '/test/file.css',
                'changes' => [
                    ['type' => 'asset', 'from' => 'fa5-cdn', 'to' => 'fa6-cdn', 'line' => 1],
                ],
                'warnings' => [],
                'assets' => [
                    ['type' => 'cdn_url', 'original' => 'https://cdnjs.../font-awesome/5.15.4/...', 'is_pro' => false],
                ],
            ],
            [
                'file' => '/test/file.js',
                'changes' => [
                    ['type' => 'style_update', 'from' => 'fas fa-home', 'to' => 'fa-solid fa-house', 'line' => 5],
                ],
                'warnings' => [],
                'assets' => [
                    ['type' => 'import', 'original' => 'from "@fortawesome/fontawesome-free-solid"', 'is_pro' => false],
                ],
            ],
        ];

        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('makeDirectory')->andReturn(true);
        File::shouldReceive('put')->twice()->andReturn(true);
        Storage::shouldReceive('url')->twice()->andReturn('/storage/fontawesome-migrator/reports/test.html', '/storage/fontawesome-migrator/reports/test.json');

        $reportInfo = $this->reporter->generateReport($results);

        $this->assertArrayHasKey('html_path', $reportInfo);
        $this->assertArrayHasKey('json_path', $reportInfo);
        $this->assertArrayHasKey('html_url', $reportInfo);
        $this->assertArrayHasKey('json_url', $reportInfo);
        $this->assertArrayHasKey('filename', $reportInfo);
        $this->assertArrayHasKey('timestamp', $reportInfo);
        $this->assertStringContainsString('fontawesome-migration-report', $reportInfo['html_path']);
        $this->assertStringContainsString('/storage/', $reportInfo['html_url']);
    }

    /** @test */
    public function it_calculates_asset_statistics_correctly()
    {
        $results = [
            [
                'file' => '/test/styles.css',
                'changes' => [
                    ['type' => 'asset', 'from' => 'v5.15.4', 'to' => 'v6.15.4', 'line' => 1],
                    ['type' => 'asset', 'from' => 'another-asset', 'to' => 'migrated-asset', 'line' => 2],
                ],
                'warnings' => [],
                'assets' => [
                    ['type' => 'cdn_url', 'original' => 'fontawesome-cdn', 'is_pro' => false],
                    ['type' => 'import', 'original' => 'scss-import', 'is_pro' => false],
                ],
            ],
            [
                'file' => '/test/icons.blade.php',
                'changes' => [
                    ['type' => 'style_update', 'from' => 'fas fa-home', 'to' => 'fa-solid fa-house', 'line' => 3],
                ],
                'warnings' => [],
                'assets' => [],
            ],
        ];

        $reflection = new \ReflectionMethod($this->reporter, 'calculateStats');
        $reflection->setAccessible(true);
        $stats = $reflection->invoke($this->reporter, $results);

        $this->assertEquals(2, $stats['total_files']);
        $this->assertEquals(2, $stats['modified_files']);
        $this->assertEquals(3, $stats['total_changes']);
        $this->assertEquals(2, $stats['assets_migrated']);
        $this->assertEquals(1, $stats['icons_migrated']);
        $this->assertArrayHasKey('cdn_url', $stats['asset_types']);
        $this->assertArrayHasKey('import', $stats['asset_types']);
        $this->assertEquals(1, $stats['asset_types']['cdn_url']);
        $this->assertEquals(1, $stats['asset_types']['import']);
    }

    /** @test */
    public function it_handles_pro_and_free_assets_in_statistics()
    {
        $results = [
            [
                'file' => '/test/pro.js',
                'changes' => [
                    ['type' => 'asset', 'from' => 'pro-package-v5', 'to' => 'pro-package-v6', 'line' => 1],
                ],
                'warnings' => [],
                'assets' => [
                    ['type' => 'pro_package', 'original' => '@fortawesome/fontawesome-pro', 'is_pro' => true],
                    ['type' => 'free_package', 'original' => '@fortawesome/fontawesome-free', 'is_pro' => false],
                ],
            ],
        ];

        $reflection = new \ReflectionMethod($this->reporter, 'calculateStats');
        $reflection->setAccessible(true);
        $stats = $reflection->invoke($this->reporter, $results);

        $this->assertArrayHasKey('pro_package', $stats['asset_types']);
        $this->assertArrayHasKey('free_package', $stats['asset_types']);
        $this->assertEquals(1, $stats['asset_types']['pro_package']);
        $this->assertEquals(1, $stats['asset_types']['free_package']);
    }

    /** @test */
    public function it_provides_correct_asset_type_descriptions()
    {
        $reflection = new \ReflectionMethod($this->reporter, 'getAssetTypeDescription');
        $reflection->setAccessible(true);

        $this->assertEquals('URLs CDN FontAwesome', $reflection->invoke($this->reporter, 'cdn_url'));
        $this->assertEquals('Imports ES6/CommonJS', $reflection->invoke($this->reporter, 'import'));
        $this->assertEquals('Packages NPM', $reflection->invoke($this->reporter, 'npm_package'));
        $this->assertEquals('Packages Pro', $reflection->invoke($this->reporter, 'pro_package'));
        $this->assertEquals('Type d\'asset détecté', $reflection->invoke($this->reporter, 'unknown_type'));
    }

    /** @test */
    public function it_labels_asset_changes_correctly()
    {
        $reflection = new \ReflectionMethod($this->reporter, 'getChangeTypeLabel');
        $reflection->setAccessible(true);

        $this->assertEquals('Asset migré', $reflection->invoke($this->reporter, 'asset'));
        $this->assertEquals('Mise à jour de style', $reflection->invoke($this->reporter, 'style_update'));
        $this->assertEquals('Icône renommée', $reflection->invoke($this->reporter, 'renamed_icon'));
    }

    /** @test */
    public function it_generates_json_report_with_assets()
    {
        $results = [
            [
                'file' => '/test/app.js',
                'changes' => [['type' => 'asset', 'from' => 'old', 'to' => 'new', 'line' => 1]],
                'warnings' => ['Asset migration warning'],
                'assets' => [['type' => 'import', 'original' => 'test-import', 'is_pro' => false]],
            ],
        ];

        $reflection = new \ReflectionMethod($this->reporter, 'generateJsonReport');
        $reflection->setAccessible(true);
        $jsonReport = $reflection->invoke($this->reporter, $results);

        $this->assertArrayHasKey('meta', $jsonReport);
        $this->assertArrayHasKey('summary', $jsonReport);
        $this->assertArrayHasKey('files', $jsonReport);

        $file = $jsonReport['files'][0];
        $this->assertArrayHasKey('assets_count', $file);
        $this->assertArrayHasKey('assets', $file);
        $this->assertEquals(1, $file['assets_count']);
        $this->assertCount(1, $file['assets']);
    }
}
