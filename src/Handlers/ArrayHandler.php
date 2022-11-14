<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Handlers;

use ReflectionReference;
use SouthPointe\Ansi\Codes\Color;
use SouthPointe\DataDump\Decorators\Decorator;
use SouthPointe\DataDump\Options;
use function array_is_list;
use function count;

class ArrayHandler extends Handler
{
    /**
     * @param array<mixed> $var
     * @param int $depth
     * @param array<int, bool> $objectIds
     * @return string
     */
    public function handle(array $var, int $depth, array $objectIds): string
    {
        $start = '[';
        $end = ']';

        if (count($var) === 0) {
            return "{$start}{$end}";
        }

        $string = $start . $this->eol();

        $isList = array_is_list($var);
        foreach ($var as $key => $val) {
            $decoKey = $this->colorizeKey($isList ? $key : "\"{$key}\"");
            $decoVal = $this->formatter->format($val, $depth + 1, $objectIds);
            $ref = $this->colorizeRefSymbol($this->isRef($var, $key) ? '&' : '');
            $arrow = $this->colorizeDelimiter('=>');
            $string .= $this->line("{$decoKey} {$arrow} {$ref}{$decoVal}", $depth + 1);
        }

        $string .= $this->indent($end, $depth);

        return $string;
    }

    /**
     * @param array<mixed> $var
     * @param int|string $key
     * @return bool
     */
    protected function isRef(array $var, int|string $key): bool
    {
        return (bool) ReflectionReference::fromArrayElement($var, $key);
    }

    /**
     * @param string $string
     * @return string
     */
    protected function colorizeRefSymbol(string $string): string
    {
        return $this->colorize($string, Color::MediumVioletRed);
    }

    /**
     * @param int|string $key
     * @return string
     */
    protected function colorizeKey(int|string $key): string
    {
        return $this->colorize((string) $key, Color::Violet);
    }
}
