<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Casters;

use Exception;
use ReflectionClass;
use ReflectionProperty;
use function count;
use function implode;
use function str_pad;
use function strlen;
use const STR_PAD_LEFT;

class ExceptionCaster extends Caster
{
    /**
     * @param Exception $var
     * @inheritDoc
     */
    public function cast(object $var, int $id, int $depth, array $objectIds): string
    {
        $deco = $this->decorator;

        $summary =
            $deco->classType($var::class) . ' ' .
            $deco->comment("#{$id}") . ' ' .
            $deco->scalar("{$var->getMessage()} in {$var->getFile()}:{$var->getLine()}") .
            $deco->eol();

        $traces = $var->getTrace();
        $traceCount = count($traces);
        $maxSize = strlen((string) $traceCount);
        $padding = fn(int $i) => str_pad("{$i}", $maxSize, ' ', STR_PAD_LEFT);
        $string = '';
        foreach ($traces as $index => $trace) {
            $hasFile = isset($trace['file']) && isset($trace['line']);
             $line = $padding($index) . ': ' .
                ($trace['file'] ?? '') . ($hasFile ? ':' : '') . ($trace['line'] ?? '') .
                ($hasFile ? ' » ' : '') .
                ($trace['class'] ?? '') . ($trace['type'] ?? '') . $trace['function'] . '(⋯)';
             $line = $deco->scalar($line);
             $string.= $index < ($traceCount - 1)
                ? $deco->line($line, $depth + 1)
                : $deco->indent($line, $depth + 1);
        }

        return $summary . $string;
    }
}
