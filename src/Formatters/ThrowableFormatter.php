<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Formatters;

use Throwable;
use function count;
use function method_exists;
use function str_pad;
use function strlen;
use const STR_PAD_LEFT;

class ThrowableFormatter extends ClassFormatter
{
    /**
     * @param Throwable $var
     * @inheritDoc
     */
    public function format(object $var, int $id, int $depth, array $objectIds): string
    {
        $deco = $this->decorator;

        $summary =
            $deco->classType($var::class) . ' ' .
            $deco->comment("#{$id}") . ' ' .
            $deco->scalar("{$var->getMessage()} in {$var->getFile()}:{$var->getLine()}") .
            $deco->eol();

        $string =
            $this->formatTrace($var, $depth + 1) .
            $this->formatContext($var, $depth + 1, $objectIds);

        return $summary . $string;
    }

    /**
     * @param Throwable $var
     * @param int $depth
     * @return string
     */
    protected function formatTrace(Throwable $var, int $depth): string
    {
        $deco = $this->decorator;

        $string = $deco->line(
            $deco->parameterKey('trace') .
            $deco->parameterDelimiter(':') . ' ',
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
            $line = $deco->scalar("{$number}: {$file}{$function}");
            $string .= $deco->line($line, $depth + 1);
        }

        return $string;
    }

    /**
     * @param Throwable $var
     * @param int $depth
     * @param array<int, bool> $objectIds
     * @return string
     */
    protected function formatContext(Throwable $var, int $depth, array $objectIds): string
    {
        $deco = $this->decorator;

        if (!method_exists($var, 'getContext')) {
            return '';
        }

        return
            $deco->indent(
                $deco->parameterKey('context') .
                $deco->parameterDelimiter(':') . ' ' .
                $this->autoFormatter->format(
                    $var->getContext(), $depth, $objectIds,
                ),
                $depth,
            );
    }
}
