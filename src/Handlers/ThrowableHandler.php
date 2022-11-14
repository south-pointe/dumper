<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Handlers;

use Throwable;
use function count;
use function method_exists;
use function str_pad;
use function strlen;
use const STR_PAD_LEFT;

class ThrowableHandler extends ClassHandler
{
    /**
     * @param Throwable $var
     * @inheritDoc
     */
    public function handle(object $var, int $id, int $depth, array $objectIds): string
    {
        $summary =
            $this->colorizeName($var::class) . ' ' .
            $this->colorizeComment("#{$id}") . ' ' .
            $this->eol();

        $string =
            $this->handleMessage($var, $depth + 1) .
            $this->handleFile($var, $depth + 1) .
            $this->handleLine($var, $depth + 1) .
            $this->handleTrace($var, $depth + 1) .
            $this->handleContext($var, $depth + 1, $objectIds);

        return $summary . $string;
    }

    protected function handleMessage(Throwable $var, int $depth): string
    {
        return $this->line(
            $this->colorizeKey('message') .
            $this->colorizeDelimiter(':') . ' ' .
            $this->colorizeScalar($var->getMessage()),
            $depth,
        );
    }

    /**
     * @param Throwable $var
     * @param int $depth
     * @return string
     */
    protected function handleFile(Throwable $var, int $depth): string
    {
        return $this->line(
            $this->colorizeKey('file') .
            $this->colorizeDelimiter(':') . ' ' .
            $this->colorizeScalar($var->getFile()),
            $depth,
        );
    }

    /**
     * @param Throwable $var
     * @param int $depth
     * @return string
     */
    protected function handleLine(Throwable $var, int $depth): string
    {
        return $this->line(
            $this->colorizeKey('line') .
            $this->colorizeDelimiter(':') . ' ' .
            $this->colorizeScalar($var->getLine()),
            $depth,
        );
    }

    /**
     * @param Throwable $var
     * @param int $depth
     * @return string
     */
    protected function handleTrace(Throwable $var, int $depth): string
    {
        $string = $this->line(
            $this->colorizeKey('trace') .
            $this->colorizeDelimiter(':') . ' ',
            $depth,
        );

        $traces = $var->getTrace();
        $padLength = strlen((string) count($traces));
        foreach ($traces as $index => $trace) {
            $hasFile = isset($trace['file']) && isset($trace['line']);
            $number = str_pad("{$index}", $padLength, ' ', STR_PAD_LEFT);
            $file = ($trace['file'] ?? '') .
                ($hasFile ? ':' : '') .
                ($trace['line'] ?? '') .
                ($hasFile ? ' » ' : '');
            $function = ($trace['class'] ?? '') .
                ($trace['type'] ?? '') .
                $trace['function'] .
                (count($trace['args'] ?? []) > 0 ? '(⋯)' : '()');
            $line = $this->colorizeScalar("{$number}: {$file}{$function}");
            $string .= $this->line($line, $depth + 1);
        }

        return $string;
    }

    /**
     * @param Throwable $var
     * @param int $depth
     * @param array<int, bool> $objectIds
     * @return string
     */
    protected function handleContext(Throwable $var, int $depth, array $objectIds): string
    {
        if (!method_exists($var, 'getContext')) {
            return '';
        }

        return
            $this->indent(
                $this->colorizeKey('context') .
                $this->colorizeDelimiter(':') . ' ' .
                $this->formatter->format(
                    $var->getContext(), $depth, $objectIds,
                ),
                $depth,
            );
    }
}
