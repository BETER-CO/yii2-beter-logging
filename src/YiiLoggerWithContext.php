<?php

namespace Beter\Yii2\Logging;

use yii\log\Logger;

class YiiLoggerWithContext extends Logger
{
    /**
     * Logs a message with the given type, category and context (optionally).
     * If [[traceLevel]] is greater than 0, additional call stack information about
     * the application code will be logged as well.
     * @param string|array|\Throwable $message the message to be logged. This can be a simple string or a more
     * complex data structure that will be handled by a [[Target|log target]].
     * @param int $level the level of the message. This must be one of the following:
     * `Logger::LEVEL_ERROR`, `Logger::LEVEL_WARNING`, `Logger::LEVEL_INFO`, `Logger::LEVEL_TRACE`, `Logger::LEVEL_PROFILE`,
     * `Logger::LEVEL_PROFILE_BEGIN`, `Logger::LEVEL_PROFILE_END`.
     * @param string $category the category of the message.
     * @param string $context an extra data to add to log records
     */
    public function log($message, $level, $category = 'application', array $context = [])
    {
        $time = microtime(true);
        $traces = [];
        if ($this->traceLevel > 0) {
            $count = 0;
            $ts = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_pop($ts); // remove the last trace since it would be the entry script, not very useful
            foreach ($ts as $trace) {
                if (isset($trace['file'], $trace['line']) && strpos($trace['file'], YII2_PATH) !== 0) {
                    unset($trace['object'], $trace['args']);
                    $traces[] = $trace;
                    if (++$count >= $this->traceLevel) {
                        break;
                    }
                }
            }
        }
        $data = [$message, $level, $category, $time, $traces, memory_get_usage(), $context];
        if ($this->profilingAware && in_array($level, [self::LEVEL_PROFILE_BEGIN, self::LEVEL_PROFILE_END])) {
            $this->messages[($level == self::LEVEL_PROFILE_BEGIN ? 'begin-' : 'end-') . md5(json_encode($message))] = $data;
        } else {
            $this->messages[] = $data;
        }

        if ($this->flushInterval > 0 && count($this->messages) >= $this->flushInterval) {
            $this->flush();
        }
    }
}