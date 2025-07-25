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

    public function test_maps_all_styles_regardless_of_license(): void
    {
        config()->set('fontawesome-migrator.license_type', 'free');

        $styleMapper = new StyleMapper();

        // mapStyle doit toujours convertir vers le bon style FA6
        $this->assertEquals('fa-light', $styleMapper->mapStyle('fal'));
        $this->assertEquals('fa-duotone', $styleMapper->mapStyle('fad'));
        $this->assertEquals('fa-thin', $styleMapper->mapStyle('fa-thin'));
    }

    public function test_applies_fallback_with_free_license(): void
    {
        config()->set('fontawesome-migrator.license_type', 'free');
        config()->set('fontawesome-migrator.fallback_strategy', 'solid');

        $styleMapper = new StyleMapper();

        // mapStyleWithFallback doit appliquer le fallback pour les styles Pro
        $this->assertEquals('fa-solid', $styleMapper->mapStyleWithFallback('fal'));
        $this->assertEquals('fa-solid', $styleMapper->mapStyleWithFallback('fad'));

        // Mais pas pour les styles Free
        $this->assertEquals('fa-solid', $styleMapper->mapStyleWithFallback('fas'));
        $this->assertEquals('fa-regular', $styleMapper->mapStyleWithFallback('far'));
    }

    public function test_handles_pro_styles_when_license_is_pro(): void
    {
        config()->set('fontawesome-migrator.license_type', 'pro');

        $styleMapper = new StyleMapper();

        // Avec licence Pro, mapStyleWithFallback conserve les styles Pro
        $this->assertEquals('fa-light', $styleMapper->mapStyleWithFallback('fal'));
        $this->assertEquals('fa-duotone', $styleMapper->mapStyleWithFallback('fad'));
        $this->assertEquals('fa-thin', $styleMapper->mapStyleWithFallback('fa-thin'));
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

    public function test_converts_full_class_with_license_fallback(): void
    {
        config()->set('fontawesome-migrator.license_type', 'free');
        config()->set('fontawesome-migrator.fallback_strategy', 'solid');

        $styleMapper = new StyleMapper();

        // Styles Free doivent être convertis normalement
        $this->assertEquals('fa-solid fa-home', $styleMapper->convertFullClass('fas fa-home'));
        $this->assertEquals('fa-regular fa-user', $styleMapper->convertFullClass('far fa-user'));

        // Styles Pro doivent utiliser le fallback avec licence Free
        $this->assertEquals('fa-solid fa-star', $styleMapper->convertFullClass('fal fa-star'));
        $this->assertEquals('fa-solid fa-heart', $styleMapper->convertFullClass('fad fa-heart'));
    }

    public function test_converts_full_class_with_pro_license(): void
    {
        config()->set('fontawesome-migrator.license_type', 'pro');

        $styleMapper = new StyleMapper();

        // Tous les styles doivent être convertis vers FA6
        $this->assertEquals('fa-solid fa-home', $styleMapper->convertFullClass('fas fa-home'));
        $this->assertEquals('fa-light fa-star', $styleMapper->convertFullClass('fal fa-star'));
        $this->assertEquals('fa-duotone fa-heart', $styleMapper->convertFullClass('fad fa-heart'));
        $this->assertEquals('fa-thin fa-circle', $styleMapper->convertFullClass('fa-thin fa-circle'));
    }
}
