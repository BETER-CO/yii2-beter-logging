<?php

namespace Beter\Yii2\Logging\Handler;

use Monolog\Handler\SocketHandler;

class LogstashHandler
    extends SocketHandler
    implements HandlerWithStatsInterface, HandlerWithHandleErrorProcessingInterface
{

    use HandlerWithStatsTrait;
    use ProcessHandleResultTrait;

    protected static array $allowedSocketTransport = ['tcp', 'udp'];

    protected Stats $stats;

    /**
     * @param string $host
     * @param int $port
     * @param string $socketTransport
     * @param int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     * @param bool $persistent Flag to enable/disable persistent connections
     * @param float $socketTimeout Socket timeout to wait until the request is being aborted
     * @param float $writingTimeout Socket timeout to wait until the request should've been sent/written
     * @param float|null $connectionTimeout Socket connect timeout to wait until the connection should've been
     *                                      established
     * @param int|null $chunkSize Sets the chunk size. Only has effect during connection in the writing cycle
     */
    public function __construct(
        string $host,
        int $port,
        string $socketTransport,
        $level,
        $bubble,
        bool $persistent,
        float $socketTimeout,
        float $writingTimeout,
        ?float $connectionTimeout = null,
        ?int $chunkSize = null
    )
    {
        if (!in_array($socketTransport, static::$allowedSocketTransport)) {
            throw new \InvalidArgumentException("socketTransport $socketTransport is not supported");
        }

        $connectionString = "$socketTransport://$host:$port";

        parent::__construct(
            $connectionString,
            $level,
            $bubble,
            $persistent,
            $socketTimeout,
            $writingTimeout,
            $connectionTimeout,
            $chunkSize
        );

        $this->stats = new Stats($this->execTimeQueueMaxSize);
    }
}
