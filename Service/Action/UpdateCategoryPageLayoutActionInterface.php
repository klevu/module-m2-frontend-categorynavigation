<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Service\Action;

use Magento\Framework\View\LayoutInterface;

interface UpdateCategoryPageLayoutActionInterface
{
    /**
     * @param LayoutInterface $layout
     *
     * @return void
     */
    public function execute(LayoutInterface $layout): void;
}
