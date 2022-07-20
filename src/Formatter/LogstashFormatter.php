<?php

namespace Beter\Yii2\Logging\Formatter;

use Beter\Yii2\Logging\Formatter\FormatTransformer;
use Beter\Yii2\Logging\Exception\InvalidConfigException;
use Monolog\Formatter\NormalizerFormatter;

class LogstashFormatter extends NormalizerFormatter {

    const FORMAT_DATETIME = 'Y-m-d\TH:i:s.v\Z';

    protected int $traceDepth;

    /**
     * @throws InvalidConfigException In case of misconfiguration
     */
    public function __construct(int $traceDepth = 3)
    {
        if ($traceDepth < 0) {
            throw new InvalidConfigException('traceDepth must be a positive integer');
        }

        parent::__construct(static::FORMAT_DATETIME);

        $this->traceDepth = $traceDepth;
    }

    /**
     * {@inheritDoc}
     */
    public function format(array $record): string
    {
        $record = parent::format($record);

        $remappedRecord = $this->transformFormat($record);
        $toAdd = [
            '@timestamp' => $remappedRecord['datetime'],
            '@version' => 1,
        ];

        unset($remappedRecord['datetime']);
        $remappedRecord = $toAdd + $remappedRecord;

        if (isset($remappedRecord['fields']) && isset($remappedRecord['fields']['log.trace'])) {
            if (isset($remappedRecord['fields']['exception'])) {
                unset($remappedRecord['fields']['log.trace']);
            } else {
                $remappedRecord['fields']['log.trace'] = $this->formatTraceLog($remappedRecord['fields']['log.trace']);
            }
        }

        return $this->toJson($remappedRecord) . "\n";
    }

    protected function formatTraceLog($traceArray): array
    {
        $lines = [];
        for ($i = 0; $i < count($traceArray); $i++) {
            $traceLine = $traceArray[$i];

            $lines[] = sprintf(
                "#%s %s(%s): %s%s%s()",
                $i,
                $traceLine['file'],
                $traceLine['line'],
                $traceLine['class'],
                $traceLine['type'],
                $traceLine['function']
            );
        }

        return $lines;
    }

    /**
     * @{inheritdoc}
     */
    protected function normalizeException(\Throwable $e, int $depth = 0)
    {
        $traceArray = parent::normalizeException($e, $depth);

        if ($this->traceDepth !== 0 && isset($traceArray['trace']) && is_array($traceArray['trace'])) {
            $traceArray['trace'] = array_slice($traceArray['trace'], 0, $this->traceDepth);
        }

        return $traceArray;
    }

    /**
     * Wrapper for overriding and testing purposes.
     *
     * @param $record
     * @return array
     */
    protected function transformFormat($record): array
    {
        return FormatTransformer::map($record, static::FORMAT_DATETIME);
    }
}