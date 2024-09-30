<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Test\Integration\Service\IsEnabledCondition;

use Klevu\FrontendApi\Service\IsEnabledCondition\IsEnabledConditionInterface;
use Klevu\FrontendCategoryNavigation\Service\IsEnabledCondition\IsCategoryRouteCondition;
use Klevu\TestFixtures\Catalog\CategoryTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\CategoryFixturePool;

/**
 * @covers \Klevu\FrontendCategoryNavigation\Service\IsEnabledCondition\IsCategoryRouteCondition
 * @method IsEnabledConditionInterface instantiateTestObject(?array $arguments = null)
 * @method IsEnabledConditionInterface instantiateTestObjectFromInterface(?array $arguments = null)
 * @magentoAppArea frontend
 */
class IsCategoryRouteConditionTest extends TestCase
{
    use CategoryTrait;
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

        $this->implementationFqcn = IsCategoryRouteCondition::class; // @phpstan-ignore-line
        $this->interfaceFqcn = IsEnabledConditionInterface::class;
        $this->objectManager = Bootstrap::getObjectManager();

        $this->categoryFixturePool = $this->objectManager->get(CategoryFixturePool::class);
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->categoryFixturePool->rollback();
    }

    public function testExecute_ForCategoryPath(): void
    {
        $this->setControllerPath('catalog/category/view');

        $service = $this->instantiateTestObject();
        $this->assertTrue($service->execute());
    }

    public function testExecute_ForOtherPaths(): void
    {
        $this->setControllerPath('catalog/product/view');

        $service = $this->instantiateTestObject();
        $this->assertFalse($service->execute());
    }

    /**
     * @param string $path
     *
     * @return void
     */
    private function setControllerPath(string $path = 'catalog/category/view'): void
    {
        $pathArray = explode('/', $path);
        /** @var Http $request */
        $request = $this->objectManager->get(RequestInterface::class);
        $request->setRouteName($pathArray[0]);
        $request->setControllerName($pathArray[1]);
        $request->setActionName($pathArray[2]);
    }
}
