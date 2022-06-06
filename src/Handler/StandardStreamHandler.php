<?php

namespace Beter\Yii2BeterLogging\Handler;

use Monolog\Handler\StreamHandler;
use Beter\Yii2BeterLogging\Exception\InvalidConfigException;

class StandardStreamHandler
    extends StreamHandler
    implements HandlerWithStatsInterface, HandlerWithHandleErrorProcessingInterface
{

    use HandlerWithStatsTrait;
    use ProcessHandleResultTrait;

    protected Stats $stats;

    /**
     * @param string $stream php://stderr or php://stdout only
     * @param string $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     *
     * @throws InvalidConfigException
     */
    public function __construct(string $stream, string $level, bool $bubble)
    {
        if ($stream !== 'php://stdout' && $stream !== 'php://stderr') {
            throw new InvalidConfigException(
                "Setting 'stream' has incorrect value. Only php://stdout and php://stderr are allowed."
            );
        }

        parent::__construct($stream, $level, $bubble);

        $this->stats = new Stats();
    }
}
