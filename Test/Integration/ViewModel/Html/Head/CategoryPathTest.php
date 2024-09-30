<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Test\Integration\ViewModel\Html\Head;

use Klevu\FrontendApi\ViewModel\Html\Head\CategoryPathInterface;
use Klevu\FrontendCategoryNavigation\ViewModel\Html\Head\CategoryPath;
use Klevu\Registry\Api\CategoryRegistryInterface;
use Klevu\TestFixtures\Catalog\CategoryTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Klevu\TestFixtures\Traits\TestInterfacePreferenceTrait;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\CategoryFixturePool;

/**
 * @covers CategoryPath
 * @method CategoryPathInterface instantiateTestObject(?array $arguments = null)
 * @method CategoryPathInterface instantiateTestObjectFromInterface(?array $arguments = null)
 * @magentoAppArea frontend
 */
class CategoryPathTest extends TestCase
{
    use CategoryTrait;
    use ObjectInstantiationTrait;
    use TestImplementsInterfaceTrait;
    use TestInterfacePreferenceTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null; //@phpstan-ignore-line
    /**
     * @var CategoryRegistryInterface|null
     */
    private ?CategoryRegistryInterface $categoryRegistry = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->implementationFqcn = CategoryPath::class;
        $this->interfaceFqcn = CategoryPathInterface::class;
        $this->objectManager = Bootstrap::getObjectManager();
        $this->categoryFixturePool = $this->objectManager->get(CategoryFixturePool::class);
        $this->categoryRegistry = $this->objectManager->get(CategoryRegistryInterface::class);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->categoryFixturePool->rollback();
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetCategoryPath_ReturnsEmptyString_WhenCategoryIsNotInRegistry(): void
    {
        $viewModel = $this->instantiateTestObject();
        $result = $viewModel->getCategoryPath();

        $this->assertSame(expected: '', actual: $result);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetCategoryPath_ReturnsCategoryName_WhenOneCategoryLevel(): void
    {
        $this->createCategory();
        $categoryFixture = $this->categoryFixturePool->get('test_category');
        $category = $categoryFixture->getCategory();

        $this->categoryRegistry->setCurrentCategory($category);

        $viewModel = $this->instantiateTestObject();
        $result = $viewModel->getCategoryPath();

        $this->assertSame(expected: $category->getName(), actual: $result);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetCategoryPath_ReturnsCategoryName_WhenMultipleCategoryLevels(): void
    {
        $this->createCategory([
            'key' => 'top_category',
        ]);
        $topCategoryFixture = $this->categoryFixturePool->get('top_category');
        $topCategory = $topCategoryFixture->getCategory();

        $this->createCategory([
            'key' => 'middle_category',
            'parent' => $topCategoryFixture,
        ]);
        $middleCategoryFixture = $this->categoryFixturePool->get('middle_category');
        $middleCategory = $middleCategoryFixture->getCategory();

        $this->createCategory([
            'key' => 'bottom_category',
            'parent' => $middleCategoryFixture,
        ]);
        $bottomCategoryFixture = $this->categoryFixturePool->get('bottom_category');
        $bottomCategory = $bottomCategoryFixture->getCategory();

        $this->categoryRegistry->setCurrentCategory($bottomCategory);

        $viewModel = $this->instantiateTestObject();
        $result = $viewModel->getCategoryPath();

        $this->assertSame(
            expected: $topCategory->getName() . ';' . $middleCategory->getName() . ';' . $bottomCategory->getName(),
            actual: $result,
        );
    }
}
