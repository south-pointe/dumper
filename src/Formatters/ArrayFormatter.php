<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Formatters;

use ReflectionReference;
use SouthPointe\DataDump\Decorators\Decorator;
use SouthPointe\DataDump\Options;
use function array_is_list;
use function count;

class ArrayFormatter
{
    /**
     * @param AutoFormatter $autoFormatter
     * @param Decorator $decorator
     * @param Options $options
     */
    public function __construct(
        protected AutoFormatter $autoFormatter,
        protected Decorator $decorator,
        protected Options $options,
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

        $start = '[';
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
