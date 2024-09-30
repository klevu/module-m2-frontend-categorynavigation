<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Observer;

use Klevu\FrontendCategoryNavigation\Service\Action\UpdateCategoryPageLayoutActionInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

class UpdateCategoryPageLayout implements ObserverInterface
{
    /**
     * @var UpdateCategoryPageLayoutActionInterface
     */
    private readonly UpdateCategoryPageLayoutActionInterface $updateCategoryPageLayoutAction;

    /**
     * @param UpdateCategoryPageLayoutActionInterface $updateCategoryPageLayoutAction
     */
    public function __construct(
        UpdateCategoryPageLayoutActionInterface $updateCategoryPageLayoutAction,
    ) {
        $this->updateCategoryPageLayoutAction = $updateCategoryPageLayoutAction;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $layout = $observer->getData('layout');
        if (!($layout instanceof LayoutInterface)) {
            return;
        }
        $this->updateCategoryPageLayoutAction->execute(
            layout: $layout,
        );
    }
}
