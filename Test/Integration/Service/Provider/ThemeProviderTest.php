<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Test\Integration\Service\Provider;

use Klevu\Configuration\Service\Provider\ScopeProviderInterface;
use Klevu\FrontendCategoryNavigation\Service\Provider\ThemeProvider;
use Klevu\FrontendCategoryNavigation\Service\Provider\ThemeProviderInterface;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Klevu\TestFixtures\Traits\TestInterfacePreferenceTrait;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Klevu\FrontendCategoryNavigation\Service\Provider\ThemeProvider
 * @magentoAppArea frontend
 */
class ThemeProviderTest extends TestCase
{
    use ObjectInstantiationTrait;
    use StoreTrait;
    use TestImplementsInterfaceTrait;
    use TestInterfacePreferenceTrait;

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

        $this->implementationFqcn = ThemeProvider::class;
        $this->interfaceFqcn = ThemeProviderInterface::class;
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeFixturesPool = $this->objectManager->get(StoreFixturesPool::class);
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->storeFixturesPool->rollback();
    }

    public function testGet_ReturnsDefaultConfigValue_WhenNotSet(): void
    {
        /** @var ThemeProvider $provider */
        $provider = $this->instantiateTestObject();
        $this->assertSame(
            expected: 0,
            actual: $provider->get(),
        );
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/category_navigation/theme 0
     * @magentoConfigFixture klevu_test_store_1_store klevu_frontend/category_navigation/theme 1
     */
    public function testGet_ReturnsStoreScopeValue(): void
    {
        $this->createStore();
        $store = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($store->get());

        /** @var ThemeProvider $provider */
        $provider = $this->instantiateTestObject();
        $this->assertSame(
            expected: 1,
            actual: $provider->get(),
        );
    }

    public function testIsKlevuTheme_returnsFalseWhenNotSet(): void
    {
        $this->createStore();
        $store = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($store->get());

        /** @var ThemeProvider $provider */
        $provider = $this->instantiateTestObject();
        $this->assertFalse($provider->isKlevuTheme(), 'Is Klevu Theme Disabled');
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/category_navigation/theme 0
     * @magentoConfigFixture klevu_test_store_1_store klevu_frontend/category_navigation/theme 1
     */
    public function testIsKlevuTheme_returnsTrueWhenSet_StoreScope(): void
    {
        $this->createStore();
        $store = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($store->get());

        /** @var ThemeProvider $provider */
        $provider = $this->instantiateTestObject();
        $this->assertTrue($provider->isKlevuTheme(), 'Is Klevu Theme Enabled');
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/category_navigation/theme 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_frontend/category_navigation/theme 0
     */
    public function testIsKlevuTheme_returnsFalseWhenKlevuThemeNotSet_StoreScope(): void
    {
        $this->createStore();
        $store = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($store->get());

        /** @var ThemeProvider $provider */
        $provider = $this->instantiateTestObject();
        $this->assertFalse($provider->isKlevuTheme(), 'Is Klevu Theme Disabled');
    }

    public function testIsNativeTheme_returnsTrueWhenKlevuThemeNotSet(): void
    {
        $this->createStore();
        $store = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($store->get());

        /** @var ThemeProvider $provider */
        $provider = $this->instantiateTestObject();
        $this->assertTrue($provider->isNativeTheme(), 'Is Klevu Theme Disabled');
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/category_navigation/theme 0
     * @magentoConfigFixture klevu_test_store_1_store klevu_frontend/category_navigation/theme 1
     */
    public function testIsNativeTheme_returnsFalseWhenKlevuThemeSet_StoreScope(): void
    {
        $this->createStore();
        $store = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($store->get());

        /** @var ThemeProvider $provider */
        $provider = $this->instantiateTestObject();
        $this->assertFalse($provider->isNativeTheme(), 'Is Klevu Theme Enabled');
    }

    /**
     * @magentoConfigFixture default/klevu_frontend/category_navigation/theme 1
     * @magentoConfigFixture klevu_test_store_1_store klevu_frontend/category_navigation/theme 0
     */
    public function testIsNativeTheme_returnsTrueWhenKlevuThemeNotSet_StoreScope(): void
    {
        $this->createStore();
        $store = $this->storeFixturesPool->get('test_store');
        $scopeProvider = $this->objectManager->get(ScopeProviderInterface::class);
        $scopeProvider->setCurrentScope($store->get());

        /** @var ThemeProvider $provider */
        $provider = $this->instantiateTestObject();
        $this->assertTrue($provider->isNativeTheme(), 'Is Klevu Theme Disabled');
    }
}
