<?php

namespace Beter\Yii2BeterLogging\Formatter;

/**
 * Set of methods to remap Monolog-specific array that represents log record
 */
class FormatTransformer
{

    protected const ROOT_DEFAULT_KEYS_TO_MOVE = ['datetime', 'host', 'app', 'service', 'env', 'message'];
    protected const CONTEXT_DEFAULT_KEYS_TO_MOVE = ['log.trace', 'log.category', 'exception'];

    /**
     * Returns remapped array that represent monolog's log record.
     *
     * @param array $record
     * @param string $dateTimeFormat
     *
     * @return array
     */
    public static function map(array $record, string $dateTimeFormat = 'Y-m-d\TH:i:s.v\Z'): array
    {
        $fields = [];
        $result = [];

        static::copyByKeys(static::ROOT_DEFAULT_KEYS_TO_MOVE, $record, $result);
        static::copyByKeys(['channel'], $record, $fields);

        if (isset($record['level_name']) && !empty($record['level_name'])) {
            $result['level'] = $record['level_name'];
        }

        if (!empty($record['context'])) {
            static::copyByKeys(static::CONTEXT_DEFAULT_KEYS_TO_MOVE, $record['context'], $fields, true);

            if (isset($record['context']['log.trueTime'])) {
                $fields['log.sent.time'] = $result['datetime'];
                $result['datetime'] = $record['context']['log.trueTime'];
                unset($record['context']['log.trueTime']);
            }

            if (!empty($record['context'])) {
                $fields['context'] = $record['context'];
            }
        }

        if (!empty($record['extra'])) {
            $fields['extra'] = $record['extra'];
        }

        if (!empty($fields)) {
            $result['fields'] = $fields;
        }

        return $result;
    }

    /**
     * Mutates $target and fills it with values from $originalRecord array for keys $keysToMove
     *
     * @param array $keysToMove
     * @param array $originalRecord
     * @param array $target
     * @param bool $removeFromOriginalRecord
     *
     * @return void
     */
    protected static function copyByKeys(
        array $keysToMove,
        array &$originalRecord,
        array &$target,
        bool $removeFromOriginalRecord = false
    )
    {
        foreach ($keysToMove as $key) {
            if (isset($originalRecord[$key]) && !empty($originalRecord[$key])) {
                $target[$key] = $originalRecord[$key];
                if ($removeFromOriginalRecord) {
                    unset($originalRecord[$key]);
                }
            }
        }
    }
}
