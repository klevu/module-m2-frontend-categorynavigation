<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Test\Integration\Setup\Patch\Data;

use Klevu\FrontendCategoryNavigation\Service\Provider\ThemeProvider;
use Klevu\FrontendCategoryNavigation\Setup\Patch\Data\MigrateLegacyConfigurationSettings;
use Klevu\TestFixtures\Store\StoreFixturesPool;
use Klevu\TestFixtures\Store\StoreTrait;
use Klevu\TestFixtures\Traits\ObjectInstantiationTrait;
use Klevu\TestFixtures\Traits\TestImplementsInterfaceTrait;
use Klevu\TestFixtures\Website\WebsiteFixturesPool;
use Klevu\TestFixtures\Website\WebsiteTrait;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\Writer as ConfigWriter;
use Magento\Framework\App\MutableScopeConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Klevu\FrontendCategoryNavigation\Setup\Patch\Data\MigrateLegacyConfigurationSettings
 */
class MigrateLegacyConfigurationSettingsTest extends TestCase
{
    use ObjectInstantiationTrait;
    use TestImplementsInterfaceTrait;
    use WebsiteTrait;
    use StoreTrait;

    /**
     * @var ObjectManagerInterface|null
     */
    private ?ObjectManagerInterface $objectManager = null;
    /**
     * @var ScopeConfigInterface|null
     */
    private ?ScopeConfigInterface $scopeConfig = null;
    /**
     * @var ConfigResource|null
     */
    private ?ConfigResource $configResource = null;
    /**
     * @var ConfigWriter|null
     */
    private ?ConfigWriter $configWriter = null;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();

        $this->implementationFqcn = MigrateLegacyConfigurationSettings::class;
        $this->interfaceFqcn = DataPatchInterface::class;

        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $this->configResource = $this->objectManager->get(ConfigResource::class);
        $this->configWriter = $this->objectManager->get(ConfigWriter::class);

        $this->websiteFixturesPool = $this->objectManager->get(WebsiteFixturesPool::class);
        $this->storeFixturesPool = $this->objectManager->get(StoreFixturesPool::class);

        $this->createStore([
            'key' => 'test_store_1',
        ]);
        $this->createWebsite();
        $testWebsite = $this->websiteFixturesPool->get('test_website');
        $this->createStore([
            'key' => 'test_store_2',
            'code' => 'klevu_test_store_2',
            'website_id' => $testWebsite->getId(),
        ]);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->storeFixturesPool->rollback();
        $this->websiteFixturesPool->rollback();
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_SrlpTheme_NativeTheme_Global(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
            value: '0',
            scope: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            scopeId: 0,
        );
        $this->scopeConfig->clean();

        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_SrlpTheme_KlevuTheme_Global(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
            value: '3',
            scope: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            scopeId: 0,
        );
        $this->scopeConfig->clean();

        $this->assertSame(
            expected: '3',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertSame(
            expected: '3',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '3',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertSame(
            expected: '1',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertSame(
            expected: '1',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '1',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_SrlpTheme_PLTheme_Global(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
            value: '2', // PL
            scope: ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            scopeId: 0,
        );
        $this->scopeConfig->clean();

        $this->assertSame(
            expected: '2', // PL
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertSame(
            expected: '2', // PL
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '2', // PL
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertSame(
            expected: '0', // PL converted to Native. PL modules handles PL migration
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            ),
        );
        $this->assertSame(
            expected: '0', // PL converted to Native. PL modules handles PL migration
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '0', // PL converted to Native. PL modules handles PL migration
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_SrlpTheme_WebsiteScope(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
            value: '3',
            scope: ScopeInterface::SCOPE_WEBSITES,
            scopeId: $testStore1->getWebsiteId(),
        );
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
            value: '1',
            scope: ScopeInterface::SCOPE_WEBSITES,
            scopeId: $testStore2->getWebsiteId(),
        );
        $this->scopeConfig->clean();

        $this->assertSame(
            expected: '3',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore1->getWebsiteId(),
            ),
        );
        $this->assertSame(
            expected: '1',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore2->getWebsiteId(),
            ),
        );
        $this->assertSame(
            expected: '3',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '1',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertSame(
            expected: '1',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore1->getWebsiteId(),
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeInterface::SCOPE_WEBSITE,
                $testStore2->getWebsiteId(),
            ),
        );
        $this->assertSame(
            expected: '1',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '0',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testApply_SrlpTheme_StoreScope(): void
    {
        $this->deleteExistingKlevuConfig();

        $testStore1 = $this->storeFixturesPool->get('test_store_1');
        $testStore2 = $this->storeFixturesPool->get('test_store_2');
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
            value: '3',
            scope: ScopeInterface::SCOPE_STORES,
            scopeId: $testStore1->getId(),
        );
        $this->configWriter->save(
            path: MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
            value: '2',
            scope: ScopeInterface::SCOPE_STORES,
            scopeId: $testStore2->getId(),
        );
        $this->scopeConfig->clean();

        $this->assertSame(
            expected: '3',
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '2', // PL
            actual: $this->scopeConfig->getValue(
                MigrateLegacyConfigurationSettings::XML_PATH_LEGACY_CATNAV_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );

        $patch = $this->instantiateTestObject();
        $patch->apply();

        $this->cleanScopeConfig();

        $this->assertSame(
            expected: '1',
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore1->getId(),
            ),
        );
        $this->assertSame(
            expected: '0', // PL converted to Native. PL modules handles PL migration
            actual: $this->scopeConfig->getValue(
                ThemeProvider::XML_PATH_CATEGORY_THEME,
                ScopeInterface::SCOPE_STORES,
                $testStore2->getId(),
            ),
        );
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function deleteExistingKlevuConfig(): void
    {
        $connection = $this->configResource->getConnection();
        $connection->delete(
            $this->configResource->getMainTable(),
            [
                'path like "klevu%"',
            ],
        );

        $this->cleanScopeConfig();
    }

    /**
     * @return void
     */
    private function cleanScopeConfig(): void
    {
        /** @var MutableScopeConfig $scopeConfig */
        $scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $scopeConfig->clean();
    }
}
