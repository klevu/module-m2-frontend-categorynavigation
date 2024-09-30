<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Test\Integration\Service\Action;

use Klevu\Configuration\Service\Provider\ScopeProviderInterface;
use Klevu\FrontendCategoryNavigation\Service\Action\UpdateCategoryPageLayoutAction;
use Klevu\FrontendCategoryNavigation\Service\Action\UpdateCategoryPageLayoutActionInterface;
use Klevu\FrontendCategoryNavigation\Service\Provider\ThemeProvider;
use Klevu\Registry\Api\CategoryRegistryInterface;
use Klevu\TestFixtures\Catalog\CategoryTrait;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\SetAuthKeysTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Klevu\TestFixtures\Traits\TestInterfacePreferenceTrait;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\CategoryFixturePool;
use TddWizard\Fixtures\Core\ConfigFixture;

/**
 * @covers \Klevu\FrontendCategoryNavigation\Service\Action\UpdateCategoryPageLayoutAction::class
 * @method UpdateCategoryPageLayoutActionInterface instantiateTestObject(?array $arguments = null)
 * @method UpdateCategoryPageLayoutActionInterface instantiateTestObjectFromInterface(?array $arguments = null)
 * @magentoAppArea frontend
 */
class UpdateCategoryPageLayoutTest extends TestCase
{
    use CategoryTrait;
    use ObjectInstantiationTrait;
    use SetAuthKeysTrait;
    use StoreTrait;
    use TestImplementsInterfaceTrait;
    use TestInterfacePreferenceTrait;

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

        $this->implementationFqcn = UpdateCategoryPageLayoutAction::class;
        $this->interfaceFqcn = UpdateCategoryPageLayoutActionInterface::class;
        $this->objectManager = Bootstrap::getObjectManager();

        $this->storeFixturesPool = $this->objectManager->get(StoreFixturesPool::class);
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
        $this->storeFixturesPool->rollback();
    }

    public function testExecute_DoesNotAddKlevuLayout_WhenNotIntegrated(): void
    {
        $this->setCategoryDisplayMode(Category::DM_PRODUCT);

        ConfigFixture::setGlobal(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 1,
        );
        $this->setControllerPath('catalog/category/view');
        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $action = $this->instantiateTestObject();
        $action->execute($layout);

        $update = $layout->getUpdate();
        $this->assertNotContains(
            needle: UpdateCategoryPageLayoutAction::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX,
            haystack: $update->getHandles(),
        );
    }

    public function testExecute_DoesNotAddKlevuLayout_WhenCatNavNotEnabled(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());
        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-js-key',
            restAuthKey: 'klevu-rest-key',
        );

        ConfigFixture::setForStore(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 0,
            storeCode: $storeFixture->getCode(),
        );
        $this->setControllerPath('catalog/category/view');
        $this->setCategoryDisplayMode(Category::DM_PRODUCT);

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $action = $this->instantiateTestObject();
        $action->execute($layout);

        $update = $layout->getUpdate();
        $this->assertNotContains(
            needle: UpdateCategoryPageLayoutAction::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX,
            haystack: $update->getHandles(),
        );
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/category_navigation/theme 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_frontend/category_navigation/theme 1
     */
    public function testExecute_DoesNotAddKlevuLayout_WhenDisplayModePage(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());
        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-js-key',
            restAuthKey: 'klevu-rest-key',
        );

        ConfigFixture::setForStore(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 1,
            storeCode: $storeFixture->getCode(),
        );
        $this->setControllerPath('catalog/category/view');
        $this->setCategoryDisplayMode(Category::DM_PAGE);

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $action = $this->instantiateTestObject();
        $action->execute($layout);

        $update = $layout->getUpdate();
        $this->assertNotContains(
            needle: UpdateCategoryPageLayoutAction::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX,
            haystack: $update->getHandles(),
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testExecute_AddsKlevuLayout(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());
        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-js-key',
            restAuthKey: 'klevu-rest-key',
        );
        ConfigFixture::setForStore(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 1,
            storeCode: $storeFixture->getCode(),
        );
        $this->setControllerPath('catalog/category/view');
        $this->setCategoryDisplayMode(Category::DM_MIXED);

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $action = $this->instantiateTestObject();
        $action->execute($layout);

        $update = $layout->getUpdate();
        $this->assertContains(
            needle: UpdateCategoryPageLayoutAction::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX,
            haystack: $update->getHandles(),
        );
    }

    public function testExecute_AddsKlevuLayout_ViaPreviewParamInRequest(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());
        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-js-key',
            restAuthKey: 'klevu-rest-key',
        );
        ConfigFixture::setForStore(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 0,
            storeCode: $storeFixture->getCode(),
        );
        $this->setControllerPath('catalog/category/view');
        $this->setCategoryDisplayMode(Category::DM_MIXED);

        $request = $this->objectManager->get(RequestInterface::class);
        $request->setParams([
            'klevu_layout_preview' => 'klevu',
        ]);

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $action = $this->instantiateTestObject();
        $action->execute($layout);

        $update = $layout->getUpdate();
        $this->assertContains(
            needle: UpdateCategoryPageLayoutAction::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX,
            haystack: $update->getHandles(),
        );
    }

    public function testExecute_DoesNotAddsKlevuLayout_ViaPreviewParamInRequest(): void
    {
        $this->createStore();
        $storeFixture = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($storeFixture->get());
        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-js-key',
            restAuthKey: 'klevu-rest-key',
        );
        ConfigFixture::setForStore(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 1,
            storeCode: $storeFixture->getCode(),
        );
        $this->setControllerPath('catalog/category/view');
        $this->setCategoryDisplayMode(Category::DM_MIXED);

        $request = $this->objectManager->get(RequestInterface::class);
        $request->setParams([
            'klevu_layout_preview' => 'native',
        ]);

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $action = $this->instantiateTestObject();
        $action->execute($layout);

        $update = $layout->getUpdate();
        $this->assertContains(
            needle: UpdateCategoryPageLayoutAction::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX,
            haystack: $update->getHandles(),
        );
    }

    /**
     * @param string|null $displayMode
     *
     * @return void
     * @throws \Exception
     */
    private function setCategoryDisplayMode(?string $displayMode = Category::DM_PRODUCT): void
    {
        $this->createCategory([
            'display_mode' => $displayMode,
        ]);
        $categoryFixture = $this->categoryFixturePool->get('test_category');
        $categoryRegistry = $this->objectManager->get(CategoryRegistryInterface::class);
        $categoryRegistry->setCurrentCategory($categoryFixture->getCategory());
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
