<?php

namespace FontAwesome\Migrator\Tests\Unit\Services;

use FontAwesome\Migrator\Services\FileScanner;
use FontAwesome\Migrator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class FileScannerTest extends TestCase
{
    private FileScanner $fileScanner;

    private string $testPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fileScanner = new FileScanner();
        $this->testPath = base_path('test-files');

        // Create test directory
        File::makeDirectory($this->testPath, 0755, true, true);
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (File::exists($this->testPath)) {
            File::deleteDirectory($this->testPath);
        }

        parent::tearDown();
    }

    public function test_can_scan_paths_and_find_files(): void
    {
        // Create test files
        File::put($this->testPath.'/test.blade.php', '<i class="fas fa-home"></i>');
        File::put($this->testPath.'/test.vue', '<font-awesome-icon icon="fas fa-user" />');
        File::put($this->testPath.'/test.js', 'icon: "fas fa-star"');

        $files = $this->fileScanner->scanPaths(['test-files']);

        $this->assertCount(3, $files);
    }

    public function test_respects_file_extension_filter(): void
    {
        // Create test files with different extensions
        File::put($this->testPath.'/test.blade.php', '<i class="fas fa-home"></i>');
        File::put($this->testPath.'/test.txt', 'fas fa-user');
        File::put($this->testPath.'/test.vue', '<font-awesome-icon icon="fas fa-star" />');

        config()->set('fontawesome-migrator.file_extensions', ['blade.php', 'vue']);

        $fileScanner = new FileScanner();
        $files = $fileScanner->scanPaths(['test-files']);

        $this->assertCount(2, $files);

        $filenames = array_map(fn ($file) => basename($file), $files);
        $this->assertContains('test.blade.php', $filenames);
        $this->assertContains('test.vue', $filenames);
        $this->assertNotContains('test.txt', $filenames);
    }

    public function test_excludes_patterns_correctly(): void
    {
        // Create test files including excluded ones
        File::makeDirectory($this->testPath.'/node_modules', 0755, true);
        File::put($this->testPath.'/test.blade.php', '<i class="fas fa-home"></i>');
        File::put($this->testPath.'/node_modules/test.js', 'fas fa-user');
        File::put($this->testPath.'/test.min.js', 'fas fa-star');

        $files = $this->fileScanner->scanPaths(['test-files']);

        $this->assertCount(1, $files);
        $this->assertStringContains('test.blade.php', $files[0]);
    }

    public function test_handles_nonexistent_paths_gracefully(): void
    {
        $files = $this->fileScanner->scanPaths(['nonexistent-path']);

        $this->assertEmpty($files);
    }

    public function test_calls_progress_callback_when_provided(): void
    {
        File::put($this->testPath.'/test1.blade.php', '<i class="fas fa-home"></i>');
        File::put($this->testPath.'/test2.blade.php', '<i class="fas fa-user"></i>');

        $callbackCalled = false;
        $progressData = [];

        $this->fileScanner->scanPaths(['test-files'], function ($current, $total) use (&$callbackCalled, &$progressData) {
            $callbackCalled = true;
            $progressData[] = ['current' => $current, 'total' => $total];
        });

        $this->assertTrue($callbackCalled);
        $this->assertNotEmpty($progressData);
        $this->assertEquals(2, end($progressData)['total']);
    }
}
