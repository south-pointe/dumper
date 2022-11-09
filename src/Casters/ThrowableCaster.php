<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Casters;

use Throwable;
use function count;
use function str_pad;
use function strlen;
use const STR_PAD_LEFT;

class ThrowableCaster extends Caster
{
    /**
     * @param Throwable $var
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

        $string = $deco->indent(
            $deco->parameterKey('trace') .
            $deco->parameterDelimiter(':') . ' ' .
            $this->formatTrace($var, $depth),
            $depth,
        );

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

        $string = '';
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
            $string.=
                $deco->indent($line, $depth + 1) .
                $deco->eol();
        }

        return
            $deco->comment('"""') . $deco->eol() .
            $string .
            $deco->indent($deco->comment('"""'), $depth + 1);
    }
}
