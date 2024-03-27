<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Observer;

use Klevu\Configuration\Service\IsStoreIntegratedServiceInterface;
use Klevu\FrontendCategoryNavigation\Service\Provider\ThemeProviderInterface;
use Klevu\Registry\Api\CategoryRegistryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

class UpdateCategoryPageLayout implements ObserverInterface
{
    public const PARAM_KLEVU_THEME = 'klevu';
    public const REQUEST_PARAM_KLEVU_CATNAV_LAYOUT_PREVIEW = 'klevu_layout_preview';
    private const LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX = 'klevu_category_index';

    /**
     * @var CategoryRegistryInterface
     */
    private readonly CategoryRegistryInterface $categoryRegistry;
    /**
     * @var RequestInterface
     */
    private readonly RequestInterface $request;
    /**
     * @var ThemeProviderInterface
     */
    private readonly ThemeProviderInterface $themeProvider;
    /**
     * @var IsStoreIntegratedServiceInterface
     */
    private readonly IsStoreIntegratedServiceInterface $isStoreIntegratedService;

    /**
     * @param CategoryRegistryInterface $categoryRegistry
     * @param RequestInterface $request
     * @param ThemeProviderInterface $themeProvider
     * @param IsStoreIntegratedServiceInterface $isStoreIntegratedService
     */
    public function __construct(
        CategoryRegistryInterface $categoryRegistry,
        RequestInterface $request,
        ThemeProviderInterface $themeProvider,
        IsStoreIntegratedServiceInterface $isStoreIntegratedService,
    ) {
        $this->categoryRegistry = $categoryRegistry;
        $this->request = $request;
        $this->themeProvider = $themeProvider;
        $this->isStoreIntegratedService = $isStoreIntegratedService;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if (
            !$this->isCategoryDisplayingProducts($observer)
            || !$this->isKlevuThemeOrPreview()
        ) {
            return;
        }
        $this->addHandleCatNav($observer);
    }

    /**
     * @param Observer $observer
     *
     * @return bool
     */
    private function isCategoryDisplayingProducts(
        Observer $observer, //phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): bool {
        $category = $this->categoryRegistry->getCurrentCategory();
        if (!$category instanceof CategoryInterface) {
            return false;
        }
        if (!method_exists($category, 'getData')) {
            return true;
        }

        return $category->getData('display_mode') !== Category::DM_PAGE;
    }

    /**
     * @return bool
     */
    private function isKlevuThemeOrPreview(): bool
    {
        if (!$this->isStoreIntegratedService->execute()) {
            return false;
        }
        $klevuPreviewParam = $this->request->getParam(static::REQUEST_PARAM_KLEVU_CATNAV_LAYOUT_PREVIEW);
        if (
            $klevuPreviewParam
            && $klevuPreviewParam !== self::PARAM_KLEVU_THEME
            && $this->themeProvider->isKlevuTheme()
        ) {
            return false;
        }

        return $klevuPreviewParam === self::PARAM_KLEVU_THEME
            || $this->themeProvider->isKlevuTheme();
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    private function addHandleCatNav(Observer $observer): void
    {
        /** @var LayoutInterface $layout */
        $layout = $observer->getData('layout');
        $update = $layout->getUpdate();
        $update->addHandle(self::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX);
    }
}
