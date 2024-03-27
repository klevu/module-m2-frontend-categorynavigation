<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\ViewModel\Html\Head;

use Klevu\FrontendApi\Service\Provider\CategoryPathProviderInterface;
use Klevu\FrontendApi\ViewModel\Html\Head\CategoryPathInterface;

class CategoryPath implements CategoryPathInterface
{
    /**
     * @var CategoryPathProviderInterface
     */
    private readonly CategoryPathProviderInterface $categoryPathProvider;

    /**
     * @param CategoryPathProviderInterface $categoryPathProvider
     */
    public function __construct(CategoryPathProviderInterface $categoryPathProvider)
    {
        $this->categoryPathProvider = $categoryPathProvider;
    }

    /**
     * @return string
     */
    public function getCategoryPath(): string
    {
        return $this->categoryPathProvider->get();
    }
}
