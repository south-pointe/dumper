<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Formatters;

use SouthPointe\DataDump\Decorators\Decorator;
use SouthPointe\DataDump\Options;
use function get_resource_id;
use function get_resource_type;
use function stream_get_meta_data;

class ResourceFormatter
{
    /**
     * @param AutoFormatter $autoFormatter
     * @param Decorator $decorator
     * @param Options $options
     */
    public function __construct(
        protected AutoFormatter $autoFormatter,
        protected Decorator $decorator,
    )
    {
    }

    /**
     * @param resource $var
     * @param int $depth
     * @return string
     */
    public function format(mixed $var, int $depth): string
    {
        $deco = $this->decorator;

        $type = get_resource_type($var);
        $id = get_resource_id($var);

        // Will get Unknown if resource is closed.
        if ($type === 'Unknown') {
            return
                $deco->resourceType('resource (closed)') . ' ' .
                $deco->comment("@{$id}");
        }

        $string =
            $deco->resourceType('resource (' . $type . ')') . ' ' .
            $deco->comment("@{$id}") . ' {' .
            $deco->eol();

        foreach (stream_get_meta_data($var) as $key => $val) {
            $decoKey = $deco->parameterKey($key);
            $decoVal = $this->autoFormatter->format($val, $depth + 1);
            $arrow = $deco->parameterDelimiter(':');
            $string.= $deco->line("{$decoKey}{$arrow} {$decoVal}", $depth + 1);
        }

        $string.= $deco->indent('}', $depth);

        return $string;
    }
}
