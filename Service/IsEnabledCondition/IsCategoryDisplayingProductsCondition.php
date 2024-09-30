<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Service\IsEnabledCondition;

use Klevu\FrontendApi\Service\IsEnabledCondition\IsEnabledConditionInterface;
use Klevu\Registry\Api\CategoryRegistryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;

class IsCategoryDisplayingProductsCondition implements IsEnabledConditionInterface
{
    /**
     * @var CategoryRegistryInterface
     */
    private readonly CategoryRegistryInterface $categoryRegistry;
    
    /**
     * @param CategoryRegistryInterface $categoryRegistry
     */
    public function __construct(
        CategoryRegistryInterface $categoryRegistry,
    ) {
        $this->categoryRegistry = $categoryRegistry;
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        $category = $this->categoryRegistry->getCurrentCategory();
        if (!$category instanceof CategoryInterface) {
            return false;
        }
        if (!method_exists($category, 'getData')) {
            return true;
        }

        return $category->getData('display_mode') !== Category::DM_PAGE;
    }
}
