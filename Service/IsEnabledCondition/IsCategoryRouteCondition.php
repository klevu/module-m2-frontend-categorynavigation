<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Service\IsEnabledCondition;

use Klevu\FrontendApi\Service\IsEnabledCondition\IsEnabledConditionInterface;
use Magento\Framework\App\RequestInterface;

class IsCategoryRouteCondition implements IsEnabledConditionInterface
{
    /**
     * @var RequestInterface
     */
    private readonly RequestInterface $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request,
    ) {
        $this->request = $request;
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        if (
            !method_exists($this->request, 'getRouteName')
            || $this->request->getRouteName() !== 'catalog'
        ) {
            return false;
        }
        if (
            !method_exists($this->request, 'getControllerName')
            || $this->request->getControllerName() !== 'category'
        ) {
            return false;
        }

        return $this->request->getActionName() === 'view';
    }
}
