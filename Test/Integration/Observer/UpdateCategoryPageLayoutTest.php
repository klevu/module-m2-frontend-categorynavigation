<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Test\Integration\Observer;

use Klevu\Configuration\Service\Provider\ScopeProviderInterface;
use Klevu\FrontendCategoryNavigation\Observer\UpdateCategoryPageLayout;
use Klevu\FrontendCategoryNavigation\Service\Provider\ThemeProvider;
use Klevu\Registry\Api\CategoryRegistryInterface;
use Klevu\TestFixtures\Catalog\CategoryTrait;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\SetAuthKeysTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ConfigInterface as EventConfig;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use TddWizard\Fixtures\Catalog\CategoryFixturePool;
use TddWizard\Fixtures\Core\ConfigFixture;

/**
 * @covers \Klevu\FrontendCategoryNavigation\Observer\UpdateCategoryPageLayout
 * @magentoAppArea frontend
 */
class UpdateCategoryPageLayoutTest extends TestCase
{
    use CategoryTrait;
    use ObjectInstantiationTrait;
    use SetAuthKeysTrait;
    use StoreTrait;
    use TestImplementsInterfaceTrait;

    private const OBSERVER_NAME = 'Klevu_FrontendCategoryNavigation_UpdateCategoryPageLayout';
    private const EVENT_NAME_LAYOUT_LOAD_BEFORE = 'layout_load_before';
    private const LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX = 'klevu_category_index';

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->implementationFqcn = UpdateCategoryPageLayout::class;
        $this->interfaceFqcn = ObserverInterface::class;
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

    public function testUpdateCategoryPageLayoutObserver_IsConfigured(): void
    {
        $observerConfig = $this->objectManager->create(type: EventConfig::class);
        $observers = $observerConfig->getObservers(eventName: self::EVENT_NAME_LAYOUT_LOAD_BEFORE);

        $this->assertArrayHasKey(key: self::OBSERVER_NAME, array: $observers);
        $this->assertSame(
            expected: ltrim(string: UpdateCategoryPageLayout::class, characters: '\\'),
            actual: $observers[self::OBSERVER_NAME]['instance'],
        );
    }

    public function testMagentoLayout_DisplayModePage(): void
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

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest();
        $this->setCategoryDisplayMode(Category::DM_PAGE);

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertNotContains(
            needle: self::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX,
            haystack: $update->getHandles(),
        );
    }

    public function testMagentoLayout_KlevuThemeDisabledInAdmin(): void
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

        ConfigFixture::setGlobal(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 0,
        );

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest();
        $this->setCategoryDisplayMode();

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertNotContains(
            needle: self::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX,
            haystack: $update->getHandles(),
        );
    }

    public function testKlevuLayout_KlevuThemeEnabledInAdmin_NotIntegrated(): void
    {
        ConfigFixture::setGlobal(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 1,
        );

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest();
        $this->setCategoryDisplayMode();

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertNotContains(
            needle: self::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX,
            haystack: $update->getHandles(),
        );
    }

    public function testKlevuLayout_KlevuThemeEnabledInAdmin_Integrated(): void
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

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest();
        $this->setCategoryDisplayMode();

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertContains(
            needle: self::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX,
            haystack: $update->getHandles(),
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testPreviewKlevuLayout_KlevuThemeDisabledInAdmin_RequestParam_NotIntegrated(): void
    {
        ConfigFixture::setGlobal(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 0,
        );

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest();
        $request->setParams([
            'klevu_layout_preview' => 'klevu',
        ]);
        $this->setCategoryDisplayMode();

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertNotContains(
            needle: self::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX,
            haystack: $update->getHandles(),
        );
    }

    public function testPreviewKlevuLayout_KlevuThemeDisabledInAdmin_RequestParam_Integrated(): void
    {
        $this->createStore();
        $store = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($store->get());
        $this->setAuthKeys(
            scopeProvider: $scopeProvider,
            jsApiKey: 'klevu-js-key',
            restAuthKey: 'klevu-rest-key',
        );

        ConfigFixture::setGlobal(
            path: ThemeProvider::XML_PATH_CATEGORY_THEME,
            value: 0,
        );

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest();
        $request->setParams([
            'klevu_layout_preview' => 'klevu',
        ]);
        $this->setCategoryDisplayMode();

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertContains(
            needle: self::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX,
            haystack: $update->getHandles(),
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testPreviewMagentoLayout_KlevuThemeEnabledInAdmin_RequestParam(): void
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

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest();
        $request->setParams([
            'klevu_layout_preview' => 'native',
        ]);
        $this->setCategoryDisplayMode();

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertNotContains(
            needle: self::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX,
            haystack: $update->getHandles(),
        );
    }

    public function testHandleAdded_ForOtherRoutes_IfCategoryRegistrySet(): void
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

        $layout = $this->objectManager->get(type: LayoutInterface::class);

        $request = $this->setRequest(controller: 'other');
        $this->setCategoryDisplayMode();

        $this->dispatchEvent(
            request: $request,
            layout: $layout,
            event: self::EVENT_NAME_LAYOUT_LOAD_BEFORE,
        );

        $update = $layout->getUpdate();
        $this->assertNotContains(
            needle: self::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX,
            haystack: $update->getHandles(),
        );
    }

    /**
     * @return RequestInterface
     */
    private function setRequest(
        string $route = 'catalog',
        string $controller = 'category',
        string $action = 'view',
    ): mixed {
        $request = $this->objectManager->get(type: RequestInterface::class);
        $request->setRouteName($route);
        $request->setControllerName($controller);
        $request->setActionName($action);

        return $request;
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
     * @param RequestInterface $request
     * @param LayoutInterface $layout
     * @param string $event
     *
     * @return void
     */
    private function dispatchEvent(
        RequestInterface $request,
        LayoutInterface $layout,
        string $event,
    ): void {
        /** @var EventManager $eventManager */
        $eventManager = $this->objectManager->get(type: EventManager::class);
        $fullActionName = method_exists($request, 'getFullActionName')
            ? $request->getFullActionName()
            : null;

        $eventManager->dispatch(
            $event,
            [
                'full_action_name' => $fullActionName,
                'layout' => $layout,
            ],
        );
    }
}
