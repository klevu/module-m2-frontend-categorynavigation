<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Service\IsEnabledCondition;

use Klevu\FrontendApi\Service\IsEnabledCondition\IsEnabledConditionInterface;
use Klevu\FrontendCategoryNavigation\Observer\UpdateCategoryPageLayout;
use Klevu\FrontendCategoryNavigation\Service\Provider\ThemeProviderInterface;
use Magento\Framework\App\RequestInterface;

class IsCatNavEnabledCondition implements IsEnabledConditionInterface
{
    /**
     * @var ThemeProviderInterface
     */
    private readonly ThemeProviderInterface $themeProvider;
    /**
     * @var RequestInterface
     */
    private readonly RequestInterface $request;

    /**
     * @param ThemeProviderInterface $themeProvider
     * @param RequestInterface $request
     */
    public function __construct(
        ThemeProviderInterface $themeProvider,
        RequestInterface $request,
    ) {
        $this->themeProvider = $themeProvider;
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        $previewParam = $this->request->getParam(
            key: UpdateCategoryPageLayout::REQUEST_PARAM_KLEVU_CATNAV_LAYOUT_PREVIEW,
        );
        if ($previewParam === UpdateCategoryPageLayout::PARAM_KLEVU_THEME) {
            return true;
        }

        return $this->themeProvider->isKlevuTheme();
    }
}
