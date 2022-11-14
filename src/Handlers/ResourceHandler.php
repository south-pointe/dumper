<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Handlers;

use SouthPointe\Ansi\Codes\Color;
use SouthPointe\DataDump\Decorators\Decorator;
use SouthPointe\DataDump\Config;
use function get_resource_id;
use function get_resource_type;
use function stream_get_meta_data;

class ResourceHandler extends Handler
{
    /**
     * @param resource $var
     * @param int $depth
     * @return string
     */
    public function handle(mixed $var, int $depth): string
    {
        $type = get_resource_type($var);
        $id = get_resource_id($var);

        // Will get Unknown if resource is closed.
        if ($type === 'Unknown') {
            return
                $this->colorizeName('resource (closed)') . ' ' .
                $this->colorizeComment("@{$id}");
        }

        $string =
            $this->colorizeName('resource (' . $type . ')') . ' ' .
            $this->colorizeComment("@{$id}") . ' {' .
            $this->eol();

        foreach (stream_get_meta_data($var) as $key => $val) {
            $decoKey = $this->colorizeKey($key);
            $decoVal = $this->formatter->format($val, $depth + 1);
            $arrow = $this->colorizeDelimiter(':');
            $string .= $this->line("{$decoKey}{$arrow} {$decoVal}", $depth + 1);
        }

        $string .= $this->indent('}', $depth);

        return $string;
    }

    /**
     * @param string $type
     * @return string
     */
    protected function colorizeName(string $type): string
    {
        return $this->colorize($type, Color::DarkCyan);
    }
}
