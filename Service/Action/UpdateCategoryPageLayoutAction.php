<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\FrontendCategoryNavigation\Service\Action;

use Klevu\Frontend\Exception\InvalidIsEnabledDeterminerException;
use Klevu\Frontend\Exception\OutputDisabledException;
use Klevu\FrontendApi\Service\IsEnabledDeterminerInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\View\LayoutInterface;
use Psr\Log\LoggerInterface;

class UpdateCategoryPageLayoutAction implements UpdateCategoryPageLayoutActionInterface
{
    public const LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX = 'klevu_category_index';

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var AppState
     */
    private readonly AppState $appState;
    /**
     * @var IsEnabledDeterminerInterface
     */
    private IsEnabledDeterminerInterface $isEnabledDeterminer;
    /**
     * @var mixed[][]
     */
    private array $isEnabledConditions;

    /**
     * @param LoggerInterface $logger
     * @param AppState $appState
     * @param IsEnabledDeterminerInterface $isEnabledDeterminer
     * @param mixed[][] $isEnabledConditions
     */
    public function __construct(
        LoggerInterface $logger,
        AppState $appState,
        IsEnabledDeterminerInterface $isEnabledDeterminer,
        array $isEnabledConditions = [],
    ) {
        $this->logger = $logger;
        $this->isEnabledDeterminer = $isEnabledDeterminer;
        $this->isEnabledConditions = $isEnabledConditions;
        $this->appState = $appState;
    }

    /**
     * @param LayoutInterface $layout
     *
     * @return void
     * @throws InvalidIsEnabledDeterminerException
     */
    public function execute(LayoutInterface $layout): void
    {
        try {
            $this->isEnabledDeterminer->executeAnd(isEnabledConditions: $this->isEnabledConditions);
        } catch (InvalidIsEnabledDeterminerException $exception) {
            if ($this->appState->getMode() !== AppState::MODE_PRODUCTION) {
                throw $exception;
            }
            $this->logger->error(
                message: 'Method: {method}, Error: {message}',
                context: [
                    'method' => __METHOD__,
                    'message' => $exception->getMessage(),
                ],
            );
            return;
        } catch (OutputDisabledException $exception) {
            // Output of category navigation is disabled.
            $this->logger->debug(
                message: 'Method: {method}, Debug: {message}',
                context: [
                    'method' => __METHOD__,
                    'message' => $exception->getMessage(),
                ],
            );
            return;
        }

        $this->addHandleCatNav($layout);
    }

    /**
     * @param LayoutInterface $layout
     *
     * @return void
     */
    private function addHandleCatNav(LayoutInterface $layout): void
    {
        $update = $layout->getUpdate();
        $update->addHandle(self::LAYOUT_HANDLE_KLEVU_CATEGORY_INDEX);
    }
}
