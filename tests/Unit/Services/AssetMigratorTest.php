<?php

namespace FontAwesome\Migrator\Tests\Unit\Services;

use FontAwesome\Migrator\Services\AssetMigrator;
use FontAwesome\Migrator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class AssetMigratorTest extends TestCase
{
    protected AssetMigrator $assetMigrator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assetMigrator = new AssetMigrator();
    }

    /** @test */
    public function it_migrates_css_cdn_urls()
    {
        $css = '@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css");';
        $expected = '@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.15.4/css/all.min.css");';

        $result = $this->assetMigrator->migrateAssets('test.css', $css);

        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_migrates_scss_imports()
    {
        $scss = '@import "~@fortawesome/fontawesome-free/scss/fontawesome";
@import "~@fortawesome/fontawesome-free/scss/solid";';

        $result = $this->assetMigrator->migrateAssets('test.scss', $scss);

        $this->assertStringContainsString('@import "~@fortawesome/fontawesome-free/scss/fontawesome";', $result);
        $this->assertStringContainsString('@import "~@fortawesome/fontawesome-free/scss/solid";', $result);
    }

    /** @test */
    public function it_migrates_javascript_imports()
    {
        $js = 'import { faHome } from "@fortawesome/fontawesome-free-solid";
const icons = require("@fortawesome/fontawesome-free-regular");';

        $expected = 'import { faHome } from "@fortawesome/free-solid-svg-icons";
const icons = require("@fortawesome/free-regular-svg-icons");';

        $result = $this->assetMigrator->migrateAssets('test.js', $js);

        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_migrates_html_cdn_links()
    {
        $html = '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">';
        $expected = '<link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.15.4/css/all.css">';

        $result = $this->assetMigrator->migrateAssets('test.html', $html);

        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_migrates_package_json_dependencies()
    {
        $packageJson = '{
  "dependencies": {
    "@fortawesome/fontawesome-free": "^5.15.4",
    "@fortawesome/fontawesome-svg-core": "^1.2.36"
  }
}';

        $result = $this->assetMigrator->migrateAssets('package.json', $packageJson);

        $this->assertStringContainsString('"@fortawesome/fontawesome-free": "^6.15.4"', $result);
        $this->assertStringContainsString('"@fortawesome/fontawesome-svg-core": "^6.2.36"', $result);
    }

    /** @test */
    public function it_handles_pro_assets_when_enabled()
    {
        // Mock la configuration Pro
        config(['fontawesome-migrator.license_type' => 'pro']);

        $js = 'import { faHome } from "@fortawesome/fontawesome-pro-solid";
const lightIcons = require("@fortawesome/fontawesome-pro-light");';

        $expected = 'import { faHome } from "@fortawesome/pro-solid-svg-icons";
const lightIcons = require("@fortawesome/pro-light-svg-icons");';

        $result = $this->assetMigrator->migrateAssets('test.js', $js);

        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_migrates_pro_package_json_dependencies()
    {
        config(['fontawesome-migrator.license_type' => 'pro']);

        $packageJson = '{
  "dependencies": {
    "@fortawesome/fontawesome-pro": "^5.15.4",
    "@fortawesome/pro-solid-svg-icons": "^5.15.4",
    "@fortawesome/pro-light-svg-icons": "^5.15.4"
  }
}';

        $result = $this->assetMigrator->migrateAssets('package.json', $packageJson);

        $this->assertStringContainsString('"@fortawesome/fontawesome-pro": "^6.15.4"', $result);
        $this->assertStringContainsString('"@fortawesome/pro-solid-svg-icons": "^6.15.4"', $result);
        $this->assertStringContainsString('"@fortawesome/pro-light-svg-icons": "^6.15.4"', $result);
    }

    /** @test */
    public function it_migrates_vue_component_assets()
    {
        $vue = '<template>
  <div class="fa fas fa-home"></div>
</template>

<script>
import { faHome } from "@fortawesome/fontawesome-free-solid";
</script>

<style>
@import "~@fortawesome/fontawesome-free/scss/fontawesome";
</style>';

        $result = $this->assetMigrator->migrateAssets('test.vue', $vue);

        $this->assertStringContainsString('from "@fortawesome/free-solid-svg-icons"', $result);
        $this->assertStringContainsString('@import "~@fortawesome/fontawesome-free/scss/fontawesome"', $result);
    }

    /** @test */
    public function it_analyzes_assets_in_css_files()
    {
        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('get')->andReturn(
            '@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css");
@import "~@fortawesome/fontawesome-free/scss/fontawesome";'
        );

        $analysis = $this->assetMigrator->analyzeAssets('/test/path/test.css');

        $this->assertNull($analysis['error']);
        $this->assertNotEmpty($analysis['assets']);
        $this->assertCount(2, $analysis['assets']);
    }

    /** @test */
    public function it_detects_pro_assets()
    {
        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('get')->andReturn(
            'import { faHome } from "@fortawesome/fontawesome-pro-solid";
const proIcons = require("@fortawesome/pro-light-svg-icons");'
        );

        $analysis = $this->assetMigrator->analyzeAssets('/test/path/test.js');

        $this->assertNotEmpty($analysis['assets']);

        foreach ($analysis['assets'] as $asset) {
            $this->assertTrue($asset['is_pro']);
        }
    }

    /** @test */
    public function it_detects_free_assets()
    {
        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('get')->andReturn(
            'import { faHome } from "@fortawesome/fontawesome-free-solid";
const freeIcons = require("@fortawesome/free-regular-svg-icons");'
        );

        $analysis = $this->assetMigrator->analyzeAssets('/test/path/test.js');

        $this->assertNotEmpty($analysis['assets']);

        foreach ($analysis['assets'] as $asset) {
            $this->assertFalse($asset['is_pro']);
        }
    }

    /** @test */
    public function it_returns_original_content_for_unsupported_extensions()
    {
        $content = 'Some random content with fa5 references';

        $result = $this->assetMigrator->migrateAssets('test.txt', $content);

        $this->assertEquals($content, $result);
    }

    /** @test */
    public function it_handles_file_not_found_error()
    {
        File::shouldReceive('exists')->andReturn(false);

        $analysis = $this->assetMigrator->analyzeAssets('/nonexistent/file.css');

        $this->assertEquals('Fichier non trouvé', $analysis['error']);
        $this->assertEmpty($analysis['assets']);
    }

    /** @test */
    public function it_generates_asset_statistics()
    {
        $files = [
            ['path' => '/test/file1.css'],
            ['path' => '/test/file2.js'],
            ['path' => '/test/file3.html'],
        ];

        // Mock les analyses de fichiers
        File::shouldReceive('exists')->andReturn(true);
        File::shouldReceive('get')
            ->andReturn('@import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css");')
            ->andReturn('import { faHome } from "@fortawesome/fontawesome-free-solid";')
            ->andReturn('<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">');

        $stats = $this->assetMigrator->getAssetStats($files);

        $this->assertArrayHasKey('total_files_with_assets', $stats);
        $this->assertArrayHasKey('total_assets', $stats);
        $this->assertArrayHasKey('pro_assets', $stats);
        $this->assertArrayHasKey('free_assets', $stats);
        $this->assertArrayHasKey('by_type', $stats);
        $this->assertArrayHasKey('by_extension', $stats);
    }
}
