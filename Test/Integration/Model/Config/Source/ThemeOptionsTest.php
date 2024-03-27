<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Test\Integration\Model\Config\Source;

use Klevu\FrontendCategoryNavigation\Model\Config\Source\ThemeOptionSource as ThemeOptionsSource;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ThemeOptionsTest extends TestCase
{
    use ObjectInstantiationTrait;
    use TestImplementsInterfaceTrait;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager; // @phpstan-ignore-line

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->implementationFqcn = ThemeOptionsSource::class;
        $this->interfaceFqcn = OptionSourceInterface::class;
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testToOptionArray_ReturnsExpectedData(): void
    {
        $optionSource = $this->instantiateTestObject();
        $options = $optionSource->toOptionArray();

        $nativeOption = array_filter(
            array: $options,
            callback: static fn ($option) => (
                $option['value'] ?? null) === ThemeOptionsSource::THEME_VALUE_DISABLED
        );
        $this->assertCount(expectedCount: 1, haystack: $nativeOption);
        $keys = array_keys($nativeOption);
        $this->assertSame(expected: 'Native Magento', actual: $nativeOption[$keys[0]]['label']?->render());

        $klevuThemeOption = array_filter(
            array: $options,
            callback: static fn ($option) => (
                ($option['value'] ?? null) === ThemeOptionsSource::THEME_VALUE_KLEVU
            )
        );
        $this->assertCount(expectedCount: 1, haystack: $klevuThemeOption);
        $keys = array_keys($klevuThemeOption);
        $this->assertSame(expected: 'Klevu JS Theme', actual: $klevuThemeOption[$keys[0]]['label']?->render());
    }
}
