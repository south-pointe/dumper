<?php declare(strict_types=1);

namespace SouthPointe\Dumper\Decorators;

use SouthPointe\Ansi\Codes\Color;

interface Decorator
{
    /**
     * @param string $string
     * @return string
     */
    public function root(string $string): string;

    /**
     * @param string $string
     * @param int $depth
     * @return string
     */
    public function indent(string $string, int $depth): string;

    /**
     * @param string $string
     * @param int $depth
     * @return string
     */
    public function line(string $string, int $depth): string;

    /**
     * @return string
     */
    public function eol(): string;

    /**
     * @param string $string
     * @param Color $color
     * @return string
     */
    public function colorize(string $string, Color $color): string;
}
