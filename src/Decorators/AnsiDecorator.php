<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Decorators;

use SouthPointe\Ansi\Ansi;
use SouthPointe\Ansi\Codes\Color;
use SouthPointe\DataDump\Options;
use const PHP_EOL;

class AnsiDecorator implements Decorator
{
    /**
     * @param Options $options
     */
    public function __construct(
        protected Options $options,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function root(string $string): string
    {
        return $string . $this->eol();
    }

    /**
     * @inheritDoc
     */
    public function line(string $string, int $depth): string
    {
        return $this->indent($string, $depth) . $this->eol();
    }

    /**
     * @inheritDoc
     */
    public function indent(string $string, int $depth): string
    {
        return str_repeat($this->options->indentation, $depth) . $string;
    }

    /**
     * @inheritDoc
     */
    public function eol(): string
    {
        return PHP_EOL;
    }

    /**
     * @inheritDoc
     */
    public function colorize(string $string, Color $color): string
    {
        return Ansi::buffer()
            ->foreground($color)
            ->text($string)
            ->resetStyle()
            ->toString();
    }
}
