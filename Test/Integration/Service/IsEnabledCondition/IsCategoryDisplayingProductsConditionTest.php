<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Test\Integration\Service\IsEnabledCondition;

use Klevu\FrontendApi\Service\IsEnabledCondition\IsEnabledConditionInterface;
use Klevu\FrontendCategoryNavigation\Service\IsEnabledCondition\IsCategoryDisplayingProductsCondition;
use Klevu\Registry\Api\CategoryRegistryInterface;
use Klevu\TestFixtures\Catalog\CategoryTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\CategoryFixturePool;

/**
 * @covers \Klevu\FrontendCategoryNavigation\Service\IsEnabledCondition\IsCategoryDisplayingProductsCondition
 * @method IsEnabledConditionInterface instantiateTestObject(?array $arguments = null)
 * @method IsEnabledConditionInterface instantiateTestObjectFromInterface(?array $arguments = null)
 * @magentoAppArea frontend
 */
class IsCategoryDisplayingProductsConditionTest extends TestCase
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

        $this->implementationFqcn = IsCategoryDisplayingProductsCondition::class; // @phpstan-ignore-line
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

    /**
     * @testWith ["PAGE", false]
     *           ["PRODUCTS", true]
     *           ["PRODUCTS_AND_PAGE", true]
     */
    public function testExecute(string $displayMode, bool $expected): void
    {
        $this->createCategory([
            'display_mode' => $displayMode,
        ]);
        $categoryFixture = $this->categoryFixturePool->get('test_category');
        $categoryRegistry = $this->objectManager->get(CategoryRegistryInterface::class);
        $categoryRegistry->setCurrentCategory($categoryFixture->getCategory());

        $service = $this->instantiateTestObject();
        $this->assertSame(
            expected: $expected,
            actual: $service->execute(),
            message: $displayMode,
        );
    }
}
