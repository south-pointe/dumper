<?php declare(strict_types=1);

namespace SouthPointe\Dumper\Decorators;

use SouthPointe\Ansi\Ansi;
use SouthPointe\Ansi\Codes\Color;
use SouthPointe\Dumper\Config;
use function dechex;
use function implode;
use function str_repeat;
use const PHP_EOL;

class HtmlDecorator implements Decorator
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
        $attrs = implode(';', [
            'background-color: black',
            'color: white',
            'display: block',
            'font-size: 0.85rem',
            'padding: 0.5rem',
            'white-space: pre',
        ]);
        return "<code style='{$attrs}'>{$string}</code>";
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
        $length = ($depth * $this->config->indentSize);
        return str_repeat(' ', $length) . "{$string}";
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
        $rgb = implode(',', $this->ansiToRgb((int) $color->value));
        return "<span style='color: rgb({$rgb})'>";
    }

    /**
     * @inheritDoc
     */
    public function colorEnd(): string
    {
        return "</span>";
    }

    /**
     * Shamelessly taken from stackoverflow
     * @see https://stackoverflow.com/a/27165165
     * @param int<0, 255> $code
     * @return array{ int, int, int }
     */
    protected function ansiToRgb(int $code): array
    {
        if ($code < 16) {
            return match ($code) {
                 0 => [  0,   0,   0], // Black
                 1 => [194,  54,  33], // Red
                 2 => [ 37, 188,  36], // Green
                 3 => [173, 173,  39], // Yellow
                 4 => [ 73,  46, 225], // Blue
                 5 => [211,  56, 211], // Magenta
                 6 => [ 51, 187, 200], // Cyan
                 7 => [203, 204, 205], // White
                 8 => [129, 131, 131], // Gray
                 9 => [252,  57,  31], // Bright Red
                10 => [ 49, 231,  34], // Bright Green
                11 => [234, 236,  35], // Bright Yellow
                12 => [ 88,  51, 255], // Bright Blue
                13 => [249,  53, 248], // Bright Magenta
                14 => [ 20, 240, 240], // Bright Cyan
                15 => [233, 235, 235], // Bright White
            };
        }

        if ($code >= 232) {
            $num = ($code - 232) * 10 + 8;
            return [$num, $num, $num];
        }

        return [
            (int) ((($code - 16) / 36) * 51),
            (int) (((($code - 16) % 36) / 6) * 51),
            (int) ((($code - 16) % 6) * 51),
        ];
    }
}
