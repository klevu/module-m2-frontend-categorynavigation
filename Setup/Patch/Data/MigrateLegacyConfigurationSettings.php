<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Setup\Patch\Data;

use Klevu\FrontendCategoryNavigation\Model\Config\Source\ThemeOptionSource;
use Klevu\FrontendCategoryNavigation\Service\Provider\ThemeProvider;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigCollectionFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class MigrateLegacyConfigurationSettings implements DataPatchInterface
{
    public const XML_PATH_LEGACY_CATNAV_THEME = 'klevu_search/categorylanding/enabledcategorynavigation';
    public const XML_VALUE_LEGACY_CATNAV_THEME_NATIVE = '1';
    public const XML_VALUE_LEGACY_CATNAV_THEME_PL = '2';
    public const XML_VALUE_LEGACY_CATNAV_THEME_JS = '3';

    /**
     * @var WriterInterface
     */
    private readonly WriterInterface $configWriter;
    /**
     * @var ConfigCollectionFactory
     */
    private readonly ConfigCollectionFactory $configCollectionFactory;
    /**
     * @var mixed[]|null
     */
    private ?array $legacyConfigSettings = null;

    /**
     * @param WriterInterface $configWriter
     * @param ConfigCollectionFactory $configCollectionFactory
     */
    public function __construct(
        WriterInterface $configWriter,
        ConfigCollectionFactory $configCollectionFactory,
    ) {
        $this->configWriter = $configWriter;
        $this->configCollectionFactory = $configCollectionFactory;
    }

    /**
     * @return $this
     */
    public function apply(): self
    {
        $this->migrateTheme();

        return $this;
    }

    /**
     * @return string[]
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return void
     */
    private function migrateTheme(): void
    {
        // mapValues sets PL to native (in case PL module is not installed)
        $this->renameConfigValue(
            fromPath: static::XML_PATH_LEGACY_CATNAV_THEME,
            toPath: ThemeProvider::XML_PATH_CATEGORY_THEME,
            mapValues: [
                static::XML_VALUE_LEGACY_CATNAV_THEME_NATIVE => (string)ThemeOptionSource::THEME_VALUE_DISABLED,
                static::XML_VALUE_LEGACY_CATNAV_THEME_PL => (string)ThemeOptionSource::THEME_VALUE_DISABLED,
                static::XML_VALUE_LEGACY_CATNAV_THEME_JS => (string)ThemeOptionSource::THEME_VALUE_KLEVU,
            ],
        );
    }

    /**
     * @param string $fromPath
     * @param string $toPath
     * @param mixed[]|null $mapValues
     *
     * @return void
     */
    private function renameConfigValue(
        string $fromPath,
        string $toPath,
        ?array $mapValues = [],
    ): void {
        $legacyConfigSettings = $this->getLegacyConfigSettings();
        if (!($legacyConfigSettings[$fromPath] ?? null)) {
            return;
        }

        foreach ($legacyConfigSettings[$fromPath] as $scope => $scopeValues) {
            foreach ($scopeValues as $scopeId => $value) {
                $this->configWriter->save(
                    path: $toPath,
                    value: $mapValues[$value] ?? $value,
                    scope: $scope,
                    scopeId: $scopeId,
                );
            }
        }
    }

    /**
     * @return mixed[]
     */
    private function getLegacyConfigSettings(): array
    {
        if (null === $this->legacyConfigSettings) {
            $configCollection = $this->configCollectionFactory->create();
            $configCollection->addFieldToFilter(
                field: 'path',
                condition: [
                    'in' => [
                        static::XML_PATH_LEGACY_CATNAV_THEME,
                    ],
                ],
            );
            $this->legacyConfigSettings = [];
            /** @var Value[] $result */
            $result = $configCollection->getItems();
            foreach ($result as $row) {
                $this->legacyConfigSettings[$row->getPath()][$row->getScope()][$row->getScopeId()] = $row->getValue();
            }
        }

        return $this->legacyConfigSettings;
    }
}
