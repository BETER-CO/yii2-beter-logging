<?php

namespace Beter\Yii2BeterLogging\Handler;

use Beter\Yii2BeterLogging\Exception\HandleException;
use Beter\Yii2BeterLogging\Exception\InvalidConfigException;

trait ProcessHandleResultTrait {

    /**
     * @var callable|null
     */
    protected $handleExceptionHandler = null;

    /**
     * No limits if null, max amount of fails before handler deactivation if positive int
     *
     * @var int|null
     */
    protected ?int $handleFailsLeftToDisableHandler = null;

    public function handle(array $record): bool
    {
        if ($this->isHandlerDisabled()) {
            // force bubbling to process error with other handler
            return false;
        }

        $startTime = microtime(true);

        try {
            $handleResult = parent::handle($record);

            if ($this instanceof HandlerWithStatsInterface) {
                $this->stats->incSuccessfulHandleCalls();
                $this->stats->addHandleExecTime(microtime(true) - $startTime);
            }

            return $handleResult;
        } catch (\Throwable $throwable) {
            $e = new HandleException('Failed to handle log record by ' . __CLASS__ . ' handler', 0, $throwable);

            if ($this instanceof HandlerWithStatsInterface) {
                $this->stats->incFailedHandleCalls();
                $this->stats->addHandleExecTime(microtime(true) - $startTime);
            }

            $this->trackHandleErrorForDisabling();

            if ($this->handleExceptionHandler) {
                ($this->handleExceptionHandler)($this, $e, $record);
            }

            // force bubbling to process error with other handler
            return false;
        }
    }

    /**
     * @return $this
     */
    protected function trackHandleErrorForDisabling(): self
    {
        if ($this->handleFailsLeftToDisableHandler === null || $this->handleFailsLeftToDisableHandler <= 0) {
            return $this;
        }

        $this->handleFailsLeftToDisableHandler--;
        if ($this->handleFailsLeftToDisableHandler <= 0) {
            if ($this->handleExceptionHandler) {
                $e = new HandleException('Handler ' . __CLASS__ . ' was disabled after reaching max amount ' .
                    'of errors in the handle method');
                ($this->handleExceptionHandler)($this, $e, null);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    protected function isHandlerDisabled(): bool
    {
        if ($this->handleFailsLeftToDisableHandler === null || $this->handleFailsLeftToDisableHandler > 0) {
            return false;
        }

        return true;
    }

    /**
     * Sets exception handler for the errors occurred during the `handle` method call.
     *
     * Handler may throw exception and such a throw will block other handlers to execute. So, it's better
     * to prevent from throwing errors in production. But it may work for dev environments to highlight
     * issues ASAP.
     *
     * @param callable|null $callback must have signature `f(HandlerInterface $handler, \Throwable $t, array $record)`
     *
     * @return $this
     */
    public function setHandleExceptionHandler(?callable $callback): self
    {
        $this->handleExceptionHandler = $callback;

        return $this;
    }

    /**
     * Sets the maximum amount of fails during the `handle` call before handler deactivation.
     *
     * @param int|null $maxAmountOfHandleErrors null to prevent deactivation at all
     *                                          or positive amount of max fails instead
     *
     * @return $this
     *
     * @throws InvalidConfigException
     */
    public function setMaxHandleErrorsBeforeDisabling(?int $maxAmountOfHandleErrors = null): self
    {
        if ($maxAmountOfHandleErrors === null) {
            return $this;
        }

        if ($maxAmountOfHandleErrors < 1) {
            throw new InvalidConfigException('maxAmountOfHandleErrors must be a positive integer');
        }

        $this->handleFailsLeftToDisableHandler = $maxAmountOfHandleErrors;

        return $this;
    }
}
