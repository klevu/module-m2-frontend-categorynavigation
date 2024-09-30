<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Test\Integration\Service\IsEnabledCondition;

use Klevu\FrontendApi\Service\IsEnabledCondition\IsEnabledConditionInterface;
use Klevu\FrontendCategoryNavigation\Service\IsEnabledCondition\IsCatNavEnabledCondition;
use Klevu\FrontendCategoryNavigation\Service\Provider\ThemeProvider;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Core\ConfigFixture;

/**
 * @covers \Klevu\FrontendCategoryNavigation\Service\IsEnabledCondition\IsCatNavEnabledCondition
 * @method IsEnabledConditionInterface instantiateTestObject(?array $arguments = null)
 * @method IsEnabledConditionInterface instantiateTestObjectFromInterface(?array $arguments = null)
 * @magentoAppArea frontend
 */
class IsCatNavEnabledConditionTest extends TestCase
{
    use ObjectInstantiationTrait;
    use TestImplementsInterfaceTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null; // @phpstan-ignore-line

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->implementationFqcn = IsCatNavEnabledCondition::class; // @phpstan-ignore-line
        $this->interfaceFqcn = IsEnabledConditionInterface::class;
        $this->objectManager = Bootstrap::getObjectManager();
    }

    public function testExecute_ReturnsFalse_WhenDisabled(): void
    {
        ConfigFixture::setGlobal(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 0,
        );
        ConfigFixture::setForStore(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 0,
            storeCode: 'default',
        );

        $service = $this->instantiateTestObject();
        $this->assertFalse(condition: $service->execute());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testExecute_ReturnsTrue_WhenEnabled(): void
    {
        ConfigFixture::setForStore(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 1,
            storeCode: 'default',
        );

        $service = $this->instantiateTestObject();
        $this->assertTrue(condition: $service->execute());
    }

    public function testExecute_ReturnsTrue_WhenDisabled_RequestContainsKlevuPreview(): void
    {
        ConfigFixture::setForStore(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 1,
            storeCode: 'default',
        );

        $request = $this->objectManager->get(RequestInterface::class);
        $request->setParams([
            'klevu_layout_preview' => 'klevu',
        ]);

        $service = $this->instantiateTestObject();
        $this->assertTrue(condition: $service->execute());
    }

    public function testExecute_ReturnsFalse_WhenEnabled_RequestContainsNativePreview(): void
    {
        ConfigFixture::setForStore(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 1,
            storeCode: 'default',
        );

        $request = $this->objectManager->get(RequestInterface::class);
        $request->setParams([
            'klevu_layout_preview' => 'native',
        ]);

        $service = $this->instantiateTestObject();
        $this->assertFalse(condition: $service->execute());
    }
}
