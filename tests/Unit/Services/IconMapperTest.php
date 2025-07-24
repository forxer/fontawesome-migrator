<?php

namespace FontAwesome\Migrator\Tests\Unit\Services;

use FontAwesome\Migrator\Services\IconMapper;
use FontAwesome\Migrator\Tests\TestCase;

class IconMapperTest extends TestCase
{
    private IconMapper $iconMapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->iconMapper = new IconMapper();
    }

    public function test_can_map_renamed_icons(): void
    {
        $mappedIcon = $this->iconMapper->mapIcon('fa-times');
        
        $this->assertEquals('fa-xmark', $mappedIcon);
    }

    public function test_can_map_deprecated_icons(): void
    {
        $mappedIcon = $this->iconMapper->mapIcon('fa-glass');
        
        $this->assertEquals('fa-martini-glass-empty', $mappedIcon);
    }

    public function test_returns_original_icon_when_no_mapping_exists(): void
    {
        $mappedIcon = $this->iconMapper->mapIcon('fa-house');
        
        $this->assertEquals('fa-house', $mappedIcon);
    }

    public function test_can_handle_multiple_icon_mappings(): void
    {
        $icons = [
            'fa-times' => 'fa-xmark',
            'fa-home' => 'fa-house',
            'fa-external-link' => 'fa-external-link-alt',
        ];

        foreach ($icons as $original => $expected) {
            $mapped = $this->iconMapper->mapIcon($original);
            $this->assertEquals($expected, $mapped, "Failed mapping {$original} to {$expected}");
        }
    }

    public function test_can_detect_pro_only_icons(): void
    {
        $this->assertTrue($this->iconMapper->isProOnly('fa-light'));
        $this->assertTrue($this->iconMapper->isProOnly('fa-duotone'));
        $this->assertFalse($this->iconMapper->isProOnly('fa-solid'));
    }

    public function test_can_get_fallback_for_pro_icons(): void
    {
        config()->set('fontawesome-migrator.fallback_strategy', 'solid');
        
        $fallback = $this->iconMapper->getFallbackStyle('fa-light');
        
        $this->assertEquals('fa-solid', $fallback);
    }
}