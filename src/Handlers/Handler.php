<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Handlers;

use SouthPointe\Ansi\Codes\Color;
use SouthPointe\DataDump\Decorators\Decorator;
use SouthPointe\DataDump\Formatter;
use SouthPointe\DataDump\Config;
use function is_int;

abstract class Handler
{
    /**
     * @var Color
     */
    protected Color $scalarColor = Color::LightGoldenrod3;

    /**
     * @param Formatter $formatter
     * @param Decorator $decorator
     * @param Config $config
     */
    public function __construct(
        protected Formatter $formatter,
        protected readonly Decorator $decorator,
        protected readonly Config $config,
    )
    {
    }

    /**
     * @param string $string
     * @param int $depth
     * @return string
     */
    protected function indent(string $string, int $depth): string
    {
        return $this->decorator->indent($string, $depth);
    }

    /**
     * @param string $string
     * @param int $depth
     * @return string
     */
    protected function line(string $string, int $depth): string
    {
        return $this->decorator->line($string, $depth);
    }

    /**
     * @return string
     */
    protected function eol(): string
    {
        return $this->decorator->eol();
    }

    /**
     * @param string $comment
     * @return string
     */
    protected function colorizeComment(string $comment): string
    {
        return $this->colorize($comment, Color::Gray);
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function colorizeScalar(mixed $value): string
    {
        return $this->colorize((string) $value, $this->scalarColor);
    }

    /**
     * @param int|string $key
     * @return string
     */
    protected function colorizeKey(int|string $key): string
    {
        return is_int($key)
            ? $this->colorize((string) $key, Color::Violet)
            : $this->colorize($key, Color::CornflowerBlue);
    }

    /**
     * @param string $delimiter
     * @return string
     */
    protected function colorizeDelimiter(string $delimiter): string
    {
        return $this->colorize($delimiter, Color::Gray30);
    }

    /**
     * @param string $value
     * @param Color $color
     * @return string
     */
    protected function colorize(string $value, Color $color): string
    {
        return $this->decorator->colorize($value, $color);
    }
}
