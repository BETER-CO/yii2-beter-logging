<?php

namespace Beter\Yii2BeterLogging\Formatter;

use Beter\Yii2BeterLogging\Exception\InvalidConfigException;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Utils;

/**
 * Formats log entries with newlines, colors and indentation. Suits well for stdout/stderr logging for human beings.
 */
class ConsoleFormatter extends LineFormatter
{

    const DATETIME_FORMAT = 'Y-m-d\TH:i:s.v\Z';

    const COLOR_DEFAULT_LEVEL = "\033[1;37m";
    const COLOR_EXCEPTION_TITLE = "\033[1;33;41m";
    const COLOR_EXCEPTION_STACKTRACE = "\033[1;37m";
    const COLOR_END = "\033[0m";

    protected array $levelCodeToColorMap = [
        Logger::DEBUG => "\033[1;37m",
        Logger::INFO => "\033[1;34m",
        Logger::NOTICE => "\033[1;34m",
        Logger::WARNING => "\033[1;33m",
        Logger::ERROR => "\033[1;31m",
        Logger::CRITICAL => "\033[1;31m",
        Logger::ALERT => "\033[1;31m",
        Logger::EMERGENCY => "\033[1;31m",
    ];

    protected bool $colorsEnabled;
    protected string $indent = '';
    protected int $traceDepth;

    /**
     * @param bool $colorize adds colors for human beings
     * @param int $indentSize amount of whitespaces to prepend to log entries
     * @param int $traceDepth limits stacktrace lines, no limits if 0
     * @throws InvalidConfigException
     */
    public function __construct(bool $colorize = true, int $indentSize = 0, int $traceDepth = 3)
    {
        if ($indentSize < 0) {
            throw new InvalidConfigException('indentSize must be a positive integer');
        }

        if ($traceDepth < 0) {
            throw new InvalidConfigException('traceDepth must be a positive integer');
        }

        $this->colorsEnabled = $colorize;
        $this->indent = $indentSize === 0 ? '' : str_repeat(' ', $indentSize);
        $this->traceDepth = $traceDepth;

        parent::__construct(null, static::DATETIME_FORMAT, true, true);

        $this->includeStacktraces(true);
    }

    /**
     * Returns specified amount of whitespaces to indent messages
     *
     * @return string
     */
    protected function addIndent(): string
    {
        return $this->indent;
    }

    /**
     * Adds colors for the given text if colors was enabled in constructor
     *
     * @param string $text
     * @param string $color
     *
     * @return string
     */
    protected function colorize(string $text, string $color): string
    {
        if ($this->colorsEnabled) {
            return $color . $text . static::COLOR_END;
        }

        return $text;
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record): string
    {
        $levelCode = $record['level'];
        $levelColor = $this->levelCodeToColorMap[$levelCode] ?? static::COLOR_DEFAULT_LEVEL;

        $record = $this->transformFormat($record);

        $vars = $this->normalize($record);

        $output = '[' . $this->colorize($this->stringify($vars['datetime']), $levelColor) . ']';
        $output .= '[' . $this->colorize($this->stringify($vars['fields']['channel']), $levelColor) . ']';
        $output .= '[' . $this->colorize($this->stringify($vars['level']), $levelColor) . ']';

        if (isset($vars['env'])) {
            $output .= '[' . $this->colorize($this->stringify($vars['env']), $levelColor) . ']';
        }

        if (isset($vars['app'])) {
            $output .= '[' . $this->colorize($this->stringify($vars['app']), $levelColor) . ']';
        }

        if (isset($vars['service'])) {
            $output .= '[' . $this->colorize($this->stringify($vars['service']), $levelColor) . ']';
        }

        $output .= " " . $this->colorize($vars['message'], $levelColor);
        $output .= "\n";

        if (isset($vars['fields']['exception'])) {
            $output .= $vars['fields']['exception'];
        } elseif (isset($vars['fields']['log.trace'])) {
            $output .= $this->formatLogTrace($vars['fields']['log.trace']);
        }

        if (isset($vars['fields']['extra'])) {
            $output .= sprintf(
                "%sFields.extra: %s\n",
                $this->addIndent(),
                $this->colorize(json_encode($vars['fields']['extra']), $levelColor)
            );
        }

        if (isset($vars['fields']['context'])) {
            $output .= sprintf(
                "%sFields.context: %s\n",
                $this->addIndent(),
                $this->colorize(json_encode($vars['fields']['context']), $levelColor)
            );
        }

        return $output;
    }

    /**
     * @param \Throwable $throwable
     *
     * @return string
     */
    protected function formatException(\Throwable $throwable): string
    {
        $str = $this->colorize("\n", static::COLOR_EXCEPTION_TITLE);

        $soapFaultStr = ($throwable instanceof \SoapFault) ? $this->formatSoapFaultException($throwable) : '';

        $str .= $this->addIndent() . $this->colorize(
                sprintf("[%s][code: %s%s]\n", Utils::getClass($throwable), $throwable->getCode(), $soapFaultStr),
                static::COLOR_EXCEPTION_TITLE
            );

        $str .= $this->addIndent() . $this->colorize($throwable->getMessage() .
                "\n", static::COLOR_EXCEPTION_TITLE);
        $str .= $this->addIndent() . $this->colorize($throwable->getFile() .
                ':' . $throwable->getLine(), static::COLOR_EXCEPTION_TITLE);

        if ($this->includeStacktraces) {
            $str .= $this->colorize($this->stacktracesParser($throwable), static::COLOR_EXCEPTION_STACKTRACE);
        }

        return $str;
    }

    /**
     * Formats traces for log entries (not exceptions) that Yii2 may add.
     *
     * @param array $traceArray
     *
     * @return string
     */
    protected function formatLogTrace(array $traceArray): string
    {
        $lines = [];

        for ($i = 0; $i < count($traceArray); $i++) {
            $traceLine = $traceArray[$i];

            $lines[] = sprintf(
                "%s#%s %s(%s): %s%s%s()",
                $this->addIndent(),
                $i,
                $traceLine['file'],
                $traceLine['line'],
                $traceLine['class'],
                $traceLine['type'],
                $traceLine['function']
            );
        }
        return $this->colorize(
            sprintf("%s[log.trace]\n%s\n", $this->addIndent(), implode("\n", $lines)),
            static::COLOR_EXCEPTION_STACKTRACE
        );
    }

    /**
     * Formats specific type of exception - SoapFault.
     *
     * Monolog's LineFormatter implements this logic, so to make it compatible this method was implemented too.
     *
     * @param \SoapFault $e
     * @return string
     */
    protected function formatSoapFaultException(\SoapFault $e): string
    {
        $str = '';

        if (isset($e->faultcode)) {
            $str .= ' faultcode: ' . $e->faultcode;
        }

        if (isset($e->faultactor)) {
            $str .= ' faultactor: ' . $e->faultactor;
        }

        if (isset($e->detail)) {
            if (is_string($e->detail)) {
                $str .= ' detail: ' . $e->detail;
            } elseif (is_object($e->detail) || is_array($e->detail)) {
                $str .= ' detail: ' . $this->toJson($e->detail, true);
            }
        }

        return $str;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeException(\Throwable $throwable, int $depth = 0): string
    {
        $str = $this->formatException($throwable);

        if ($previous = $throwable->getPrevious()) {
            do {
                $str .= sprintf("\n%s[previous exception] %s", $this->addIndent(), $this->formatException($previous));
            } while ($previous = $previous->getPrevious());
        }

        return $str;
    }

    /**
     * Formats stacktrace.
     *
     * Monolog's LineFormatter implements this logic, so to make it compatible this method was implemented too.
     *
     * @param \Throwable $e
     * @return string
     */
    protected function stacktracesParser(\Throwable $e): string
    {
        $trace = $e->getTraceAsString();
        $traceLines = explode("\n", $trace);
        if ($this->traceDepth !== 0) {
            $traceLines = array_slice($traceLines, 0, $this->traceDepth);
        }

        $trace = $this->addIndent() . implode("\n" . $this->addIndent(), $traceLines);

        return sprintf("\n%s[stacktrace]\n%s\n", $this->addIndent(), $trace);
    }

    /**
     * Wrapper for overriding and testing purposes.
     *
     * @param array $record
     * @return array
     */
    protected function transformFormat(array $record): array
    {
        return FormatTransformer::map($record);
    }
}
