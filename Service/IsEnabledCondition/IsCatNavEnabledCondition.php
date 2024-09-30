<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Service\IsEnabledCondition;

use Klevu\FrontendApi\Service\IsEnabledCondition\IsEnabledConditionInterface;
use Klevu\FrontendCategoryNavigation\Service\Provider\ThemeProviderInterface;
use Magento\Framework\App\RequestInterface;

class IsCatNavEnabledCondition implements IsEnabledConditionInterface
{
    public const PARAM_KLEVU_THEME = 'klevu';
    public const REQUEST_PARAM_KLEVU_CATNAV_LAYOUT_PREVIEW = 'klevu_layout_preview';

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
            key: static::REQUEST_PARAM_KLEVU_CATNAV_LAYOUT_PREVIEW,
        );
        if (
            $previewParam
            && $previewParam !== self::PARAM_KLEVU_THEME
            && $this->themeProvider->isKlevuTheme()
        ) {
            return false;
        }

        return $previewParam === self::PARAM_KLEVU_THEME
            || $this->themeProvider->isKlevuTheme();
    }
}
