<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Decorators;

use SouthPointe\Ansi\Ansi;
use SouthPointe\Ansi\Codes\Color;
use function is_int;
use const PHP_EOL;

class AnsiDecorator implements Decorator
{
    /**
     * @param string $indentation
     */
    public function __construct(
        protected string $indentation = '  ',
    )
    {
    }

    /**
     * @param string $string
     * @return void
     */
    public function output(string $string): void
    {
        $eol = $this->eol();
        echo "{$eol}{$string}{$eol}";
    }

    /**
     * @param string $string
     * @return string
     */
    public function refSymbol(string $string): string
    {
        return $string;
    }

    /**
     * @param string $type
     * @return string
     */
    public function classType(string $type): string
    {
        return $this->withColor($type, Color::DarkCyan);
    }

    /**
     * @param string $type
     * @return string
     */
    public function resourceType(string $type): string
    {
        return $this->withColor($type, Color::DarkCyan);
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function scalar(mixed $value): string
    {
        return $this->withColor($value, Color::LightGoldenrod3);
    }

    /**
     * @param int|string $key
     * @return string
     */
    public function parameterKey(int|string $key): string
    {
        return is_int($key)
            ? $this->withColor((string) $key, Color::Violet)
            : $this->withColor($key, Color::CornflowerBlue);
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function parameterDelimiter(string $delimiter): string
    {
        return $this->withColor($delimiter, Color::Gray30);
    }

    /**
     * @param int|string $key
     * @return string
     */
    public function arrayKey(int|string $key): string
    {
        return $this->withColor((string) $key, Color::Violet);
    }

    /**
     * @param string $comment
     * @return string
     */
    public function comment(string $comment): string
    {
        return $this->withColor($comment, Color::Gray);
    }

    /**
     * @param string $string
     * @param int $depth
     * @return string
     */
    public function line(string $string, int $depth): string
    {
        return $this->indent($string, $depth) . $this->eol();
    }

    /**
     * @param string $string
     * @param int $depth
     * @return string
     */
    public function indent(string $string, int $depth): string
    {
        return str_repeat($this->indentation, $depth) . $string;
    }

    /**
     * @return string
     */
    public function eol(): string
    {
        return PHP_EOL;
    }

    /**
     * @param string $value
     * @param Color $color
     * @return string
     */
    protected function withColor(string $value, Color $color): string
    {
        return Ansi::buffer()
            ->foreground($color)
            ->text($value)
            ->resetStyle()
            ->toString();
    }
}
