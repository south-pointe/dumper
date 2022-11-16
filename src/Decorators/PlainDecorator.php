<?php declare(strict_types=1);

namespace SouthPointe\Dumper\Decorators;

use SouthPointe\Ansi\Codes\Color;
use SouthPointe\Dumper\Config;
use function str_repeat;
use const PHP_EOL;

class PlainDecorator implements Decorator
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
    public function colorStart(Color $color): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function colorEnd(): string
    {
        return '';
    }
}
