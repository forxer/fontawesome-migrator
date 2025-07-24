<?php

namespace FontAwesome\Migrator\Tests\Unit\Services;

use FontAwesome\Migrator\Services\StyleMapper;
use FontAwesome\Migrator\Tests\TestCase;

class StyleMapperTest extends TestCase
{
    private StyleMapper $styleMapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->styleMapper = new StyleMapper();
    }

    public function test_can_map_fa5_short_styles_to_fa6(): void
    {
        $this->assertEquals('fa-solid', $this->styleMapper->mapStyle('fas'));
        $this->assertEquals('fa-regular', $this->styleMapper->mapStyle('far'));
        $this->assertEquals('fa-brands', $this->styleMapper->mapStyle('fab'));
    }

    public function test_preserves_fa6_style_format(): void
    {
        $this->assertEquals('fa-solid', $this->styleMapper->mapStyle('fa-solid'));
        $this->assertEquals('fa-regular', $this->styleMapper->mapStyle('fa-regular'));
        $this->assertEquals('fa-brands', $this->styleMapper->mapStyle('fa-brands'));
    }

    public function test_handles_pro_styles_when_license_is_free(): void
    {
        config()->set('fontawesome-migrator.license_type', 'free');
        config()->set('fontawesome-migrator.fallback_strategy', 'solid');
        
        $styleMapper = new StyleMapper();
        
        $this->assertEquals('fa-solid', $styleMapper->mapStyle('fal'));
        $this->assertEquals('fa-solid', $styleMapper->mapStyle('fad'));
    }

    public function test_handles_pro_styles_when_license_is_pro(): void
    {
        config()->set('fontawesome-migrator.license_type', 'pro');
        config()->set('fontawesome-migrator.pro_styles.light', true);
        config()->set('fontawesome-migrator.pro_styles.duotone', true);
        
        $styleMapper = new StyleMapper();
        
        $this->assertEquals('fa-light', $styleMapper->mapStyle('fal'));
        $this->assertEquals('fa-duotone', $styleMapper->mapStyle('fad'));
    }

    public function test_handles_new_fa6_pro_styles(): void
    {
        config()->set('fontawesome-migrator.license_type', 'pro');
        config()->set('fontawesome-migrator.pro_styles.thin', true);
        config()->set('fontawesome-migrator.pro_styles.sharp', true);
        
        $styleMapper = new StyleMapper();
        
        $this->assertEquals('fa-thin', $styleMapper->mapStyle('fa-thin'));
        $this->assertEquals('fa-sharp', $styleMapper->mapStyle('fa-sharp'));
    }

    public function test_returns_original_style_when_no_mapping_exists(): void
    {
        $result = $this->styleMapper->mapStyle('unknown-style');
        
        $this->assertEquals('unknown-style', $result);
    }

    public function test_can_check_if_style_is_pro_only(): void
    {
        $this->assertTrue($this->styleMapper->isProStyle('fal'));
        $this->assertTrue($this->styleMapper->isProStyle('fad'));
        $this->assertTrue($this->styleMapper->isProStyle('fa-light'));
        $this->assertTrue($this->styleMapper->isProStyle('fa-duotone'));
        
        $this->assertFalse($this->styleMapper->isProStyle('fas'));
        $this->assertFalse($this->styleMapper->isProStyle('far'));
        $this->assertFalse($this->styleMapper->isProStyle('fa-solid'));
    }
}