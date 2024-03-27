<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Service\Provider;

interface ThemeProviderInterface
{
    /**
     * @return int
     */
    public function get(): int;

    /**
     * @return bool
     */
    public function isKlevuTheme(): bool;

    /**
     * @return bool
     */
    public function isNativeTheme(): bool;
}
