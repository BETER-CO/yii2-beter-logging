<?php

namespace Beter\Yii2\Logging\Processor;

use Beter\Yii2\Logging\Exception\InvalidConfigException;
use Monolog\Processor\ProcessorInterface;

class BasicProcessor implements ProcessorInterface
{
    protected string $envName;
    protected string $app;
    protected string $service;
    protected string $host;

    /**
     * @param string $envName name of the current environment, dev, test, stage, etc
     * @param string $app application name
     * @param string $service service name, service is usually a part of application
     * @param string $host hostname of the system
     * @throws InvalidConfigException
     */
    public function __construct(string $envName, string $app, string $service, string $host)
    {
        if (empty($envName)) {
            throw new InvalidConfigException('envName must be a non-empty string');
        }

        if (empty($app)) {
            throw new InvalidConfigException('app must be a non-empty string');
        }

        if (empty($service)) {
            throw new InvalidConfigException('service must be a non-empty string');
        }

        if (empty($host)) {
            throw new InvalidConfigException('host must be a non-empty string');
        }

        $this->envName = $envName;
        $this->app = $app;
        $this->service = $service;
        $this->host = $host;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(array $record): array
    {
        $record['env'] = $this->envName;
        $record['app'] = $this->app;
        $record['service'] = $this->service;
        $record['host'] = $this->host;

        return $record;
    }
}