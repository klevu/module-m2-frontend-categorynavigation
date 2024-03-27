<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ThemeOptionSource implements OptionSourceInterface
{
    public const THEME_VALUE_DISABLED = 0;
    public const THEME_VALUE_KLEVU = 1;

    /**
     * @return mixed[][]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => static::THEME_VALUE_DISABLED,
                'label' => __('Native Magento'),
            ],
            [
                'value' => static::THEME_VALUE_KLEVU,
                'label' => __('Klevu JS Theme'),
            ],
        ];
    }
}
