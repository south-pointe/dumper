<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Formatters;

use ReflectionReference;
use SouthPointe\DataDump\Decorators\Decorator;
use function array_is_list;
use function count;

class ArrayFormatter
{
    /**
     * @param Decorator $decorator
     * @param AutoFormatter $autoFormatter
     */
    public function __construct(
        protected Decorator $decorator,
        protected AutoFormatter $autoFormatter,
    )
    {
    }

    /**
     * @param array<mixed> $var
     * @param int $depth
     * @param array<int, bool> $objectIds
     * @return string
     */
    public function format(array $var, int $depth, array $objectIds): string
    {
        $deco = $this->decorator;

        $start = $deco->classType('array(' . count($var) . ')') . ' [';
        $end = ']';

        if (count($var) === 0) {
            return "{$start}{$end}";
        }

        $string = $start . $deco->eol();

        $isList = array_is_list($var);
        foreach ($var as $key => $val) {
            $decoKey = $deco->arrayKey($isList ? $key : "\"{$key}\"");
            $decoVal = $this->autoFormatter->format($val, $depth + 1, $objectIds);
            $ref = $deco->refSymbol(ReflectionReference::fromArrayElement($var, $key) ? '&' : '');
            $arrow = $deco->parameterDelimiter('=>');
            $string .= $deco->line("{$decoKey} {$arrow} {$ref}{$decoVal}", $depth + 1);
        }

        $string .= $deco->indent($end, $depth);

        return $string;
    }
}
