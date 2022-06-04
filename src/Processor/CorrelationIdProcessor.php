<?php

namespace Beter\Yii2BeterLogging\Processor;

use Beter\Yii2BeterLogging\Exception\InvalidConfigException;
use Monolog\Processor\ProcessorInterface;

class CorrelationIdProcessor implements ProcessorInterface
{
    protected string $envName;
    protected string $app;
    protected string $service;
    protected string $correlationId;

    /**
     * @param int $length length of generated correlationId if it isn't present in headers (or search in headers is
     *  disabled.
     * @param bool $searchInHeaders enables search of the $correlationIdHeaderName in the headers for non cli SAPI.
     * @param string $correlationIdHeaderName default value is 'X-Request-Id', but you may redefine id.
     *
     * @throws InvalidConfigException
     */
    public function __construct(
        int $length,
        bool $searchInHeaders = true,
        string $correlationIdHeaderName = 'X-Request-Id'
    )
    {
        if ($length <= 0) {
            throw new InvalidConfigException('length must be a positive integer');
        }

        if ($searchInHeaders && php_sapi_name() !== 'cli') {
            $this->correlationId = $this->findCorrelationIdInHeaders($correlationIdHeaderName) ??
                $this->generateRandomHexString($length);
        } else {
            $this->correlationId = $this->generateRandomHexString($length);
        }
    }

    protected function findCorrelationIdInHeaders(string $correlationIdHeader): ?string
    {
        $headers = [];

        if (!function_exists('getallheaders')) {
            $headers = getallheaders();
        } elseif (function_exists('http_get_request_headers')) {
            $headers = http_get_request_headers();
        } else {
            $prefix = 'HTTP_';
            $prefixLength = strlen($prefix);

            foreach ($_SERVER as $name => $value) {
                if (strncmp($name, $prefix, $prefixLength) === 0) {
                    $lowered = strtolower(str_replace('_', ' ', substr($name, $prefixLength)));
                    $name = str_replace(' ', '-', ucwords($lowered));
                    $headers[$name] = $value;
                }
            }
        }

        if (empty($headers) || !isset($headers[$correlationIdHeader])) {
            return null;
        }

        return $headers[$correlationIdHeader];
    }

    /**
     * @param int $length
     * @return string
     */
    protected function generateRandomHexString(int $length = 3): string
    {
        try {
            $bytes = intval(ceil($length / 2));

            $result = substr(bin2hex(random_bytes($bytes)), 0, $length);
            if ($result === false) {
                throw new \Exception('Failed');
            }

            return $result;
        } catch (\Throwable $throwable) {
            return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

                // 16 bits for "time_mid"
                mt_rand( 0, 0xffff ),

                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                mt_rand( 0, 0x0fff ) | 0x4000,

                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                mt_rand( 0, 0x3fff ) | 0x8000,

                // 48 bits for "node"
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(array $record): array
    {
        $record['extra']['correlationId'] = $this->correlationId;

        return $record;
    }
}