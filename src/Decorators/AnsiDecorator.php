<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Decorators;

use SouthPointe\Ansi\Ansi;
use SouthPointe\Ansi\Codes\Color;
use SouthPointe\DataDump\Config;
use function str_repeat;
use const PHP_EOL;

class AnsiDecorator implements Decorator
{
    /**
     * @param Config $config
     */
    public function __construct(
        protected Config $config,
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
        return str_repeat(' ', $depth * $this->config->indentSize) . $string;
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
