<?php

namespace Alexconesap\Commons;

use Throwable;

/**
 * LIBRARY : Exception helper
 *
 * @author Alex Conesa, 2022
 */
class ExceptionsLib
{

    /**
     * Converts a given Throwable object $e trace info to a string in "Java style" exception
     * that is much shorter and readable than the one provided by PHP.
     *
     * It is intended to reduce logging verbosity significantly.
     *
     * Example:
     * <code>
     *     try {
     *         // whatever
     *     } catch (Exception $ex) {
     *         Log::debug(ExceptionsLib::toJavaStyleTrace($ex));
     *     }
     * </code>
     *
     * @param Throwable $e The base PHP Exception we want to parse
     * @return string
     */
    public static function toJavaStyleTrace(Throwable $e): string
    {
        return self::_exceptionToJavaStyleTrace($e);
    }

    /**
     * Converts a given array to string in Java style exception that is much shorter and readable.
     * Intended to reduce logging verbosity significantly.
     *
     * Example:
     * <code>
     *     {
     *         if ($want_to_log_trace) Log::debug(ExceptionsLib::toJavaStyleTrace( debug_backtrace() ));
     *     }
     * </code>
     *
     * @param array $debug_backtrace The debug_backtrace() or $exception->getTrace() to process
     * @return string
     */
    public static function arrayToJavaStyleTrace(array $debug_backtrace): string
    {
        return join(PHP_EOL, self::_reduceArray($debug_backtrace));
    }

    private static function _exceptionToJavaStyleTrace(Throwable $e, array &$seen = []): string
    {
        $result = array_merge(
            [
                sprintf('%s%s: %s', (count($seen) > 0 ? 'Caused by: ' : ''), get_class($e), $e->getMessage())
            ],
            self::_reduceArray($e->getTrace(), $seen)
        );

        $str_result = join(PHP_EOL, $result);
        if ($prev = $e->getPrevious()) {
            $str_result .= PHP_EOL . static::_exceptionToJavaStyleTrace($prev, $seen);
        }
        return $str_result;
    }

    private static function _reduceArray(array $debug_backtrace, array &$seen = []): array
    {
        $count = count($debug_backtrace);
        if (!$count) return [];
        foreach ($debug_backtrace as $trace) {
            $current = "$trace[file]:$trace[line]";
            if (in_array($current, $seen, true)) {
                $result[] = sprintf(' ... %d more', $count + 1);
                break;
            }
            $seen[] = $current;

            $result[] = sprintf(' at %s%s%s(%s%s%s)', //
                $trace['class'] ?? '', //
                ($trace['class'] ?? '') && ($trace['function'] ?? '') ? '.' : '', //
                $trace['function'] ?? '(main)', //
                basename($trace['file'] ?? 'Unknown Source'), //
                $trace['line'] ? ':' : '', //
                $trace['line'] ?? '' //
            );
        }
        return $result;
    }
}
