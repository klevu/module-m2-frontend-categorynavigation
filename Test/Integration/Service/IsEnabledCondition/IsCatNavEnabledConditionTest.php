<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Test\Integration\Service\IsEnabledCondition;

use Klevu\FrontendApi\Service\IsEnabledCondition\IsEnabledConditionInterface;
use Klevu\FrontendCategoryNavigation\Service\IsEnabledCondition\IsCatNavEnabledCondition;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Klevu\FrontendCategoryNavigation\Service\IsEnabledCondition\IsCatNavEnabledCondition
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

    /**
     * @magentoConfigFixture default/klevu_frontend/category_navigation/theme 1
     * @magentoConfigFixture default_store klevu_frontend/category_navigation/theme 0
     */
    public function testExecute_ReturnsFalse_WhenDisabled(): void
    {
        /** @var IsCatNavEnabledCondition $service */
        $service = $this->instantiateTestObject();
        $this->assertFalse(condition: $service->execute());
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/category_navigation/theme 0
     * @magentoConfigFixture default_store klevu_frontend/category_navigation/theme 1
     */
    public function testExecute_ReturnsTrue_WhenEnabled(): void
    {
        /** @var IsCatNavEnabledCondition $service */
        $service = $this->instantiateTestObject();
        $this->assertTrue(condition: $service->execute());
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/category_navigation/theme 1
     * @magentoConfigFixture default_store klevu_frontend/category_navigation/theme 0
     */
    public function testExecute_ReturnsTrue_WhenDisabled_RequestContainsKlevuPreview(): void
    {
        $request = $this->objectManager->get(RequestInterface::class);
        $request->setParams([
            'klevu_layout_preview' => 'klevu',
        ]);

        /** @var IsCatNavEnabledCondition $service */
        $service = $this->instantiateTestObject();
        $this->assertTrue(condition: $service->execute());
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/category_navigation/theme 0
     * @magentoConfigFixture default_store klevu_frontend/category_navigation/theme 1
     */
    public function testExecute_ReturnsTrue_WhenEnabled_RequestContainsNativePreview(): void
    {
        $request = $this->objectManager->get(RequestInterface::class);
        $request->setParams([
            'klevu_layout_preview' => 'native',
        ]);

        /** @var IsCatNavEnabledCondition $service */
        $service = $this->instantiateTestObject();
        $this->assertTrue(condition: $service->execute());
    }
}
