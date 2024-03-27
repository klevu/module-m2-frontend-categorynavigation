<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Test\Integration\Service\Provider;

use Klevu\FrontendApi\Service\Provider\CategoryPathProviderInterface;
use Klevu\FrontendCategoryNavigation\Service\Provider\CategoryPathProvider;
use Klevu\Registry\Api\CategoryRegistryInterface;
use Klevu\TestFixtures\Catalog\CategoryTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Klevu\TestFixtures\Traits\TestInterfacePreferenceTrait;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use TddWizard\Fixtures\Catalog\CategoryFixturePool;

/**
 * @covers CategoryPathProvider
 * @method CategoryPathProviderInterface instantiateTestObject(?array $arguments = null)
 * @method CategoryPathProviderInterface instantiateTestObjectFromInterface(?array $arguments = null)
 * @magentoAppArea frontend
 */
class CategoryPathProviderTest extends TestCase
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

        $this->implementationFqcn = CategoryPathProvider::class;
        $this->interfaceFqcn = CategoryPathProviderInterface::class;
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
    public function testGet_ReturnsEmptyString_WhenCategoryIsNotInRegistry(): void
    {
        $viewModel = $this->instantiateTestObject();
        $result = $viewModel->get();

        $this->assertSame(expected: '', actual: $result);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGet_ReturnsCategoryName_WhenOneCategoryLevel(): void
    {
        $this->createCategory();
        $categoryFixture = $this->categoryFixturePool->get('test_category');
        $category = $categoryFixture->getCategory();

        $this->categoryRegistry->setCurrentCategory($category);

        $viewModel = $this->instantiateTestObject();
        $result = $viewModel->get();

        $this->assertSame(expected: $category->getName(), actual: $result);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGet_ReturnsCategoryName_WhenMultipleCategoryLevels(): void
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
        $result = $viewModel->get();

        $this->assertSame(
            expected: $topCategory->getName() . '/' . $middleCategory->getName() . '/' . $bottomCategory->getName(),
            actual: $result,
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGet_logsErrorReturnsEmptyString_WhenExceptionThrown(): void
    {
        $exceptionMessage = 'There was an error';
        $mockCategoryCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCategoryCollection->expects($this->once())
            ->method('addAttributeToSelect')
            ->willThrowException(
                new LocalizedException(__($exceptionMessage)),
            );
        $mockCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockCollectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($mockCategoryCollection);

        $mockLogger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $mockLogger->expects($this->once())
            ->method('error')
            ->with(
                'Method: {method}, Error: {message}',
                [
                    'method' => 'Klevu\FrontendCategoryNavigation\Service\Provider\CategoryPathProvider::get',
                    'message' => $exceptionMessage,
                ],
            );

        $this->createCategory([
            'key' => 'top_category',
        ]);
        $topCategoryFixture = $this->categoryFixturePool->get('top_category');

        $this->createCategory([
            'key' => 'bottom_category',
            'parent' => $topCategoryFixture,
        ]);
        $bottomCategoryFixture = $this->categoryFixturePool->get('bottom_category');
        $bottomCategory = $bottomCategoryFixture->getCategory();

        $this->categoryRegistry->setCurrentCategory($bottomCategory);

        $viewModel = $this->instantiateTestObject([
            'categoryCollectionFactory' => $mockCollectionFactory,
            'logger' => $mockLogger,
        ]);
        $result = $viewModel->get();

        $this->assertSame(expected: '', actual: $result);
    }
}
