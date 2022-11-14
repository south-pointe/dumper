<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Handlers;

use SouthPointe\Ansi\Ansi;
use SouthPointe\Ansi\Codes\Color;
use function explode;
use function implode;
use function is_bool;
use function is_float;
use function is_infinite;
use function is_int;
use function is_nan;
use function mb_ord;
use function mb_strcut;
use function preg_replace_callback_array;
use function sprintf;
use function str_contains;

class ScalarHandler extends Handler
{
    /**
     * @param bool|int|float|string $var
     * @param int $depth
     * @return string
     */
    public function handle(bool|int|float|string $var, int $depth): string
    {
        if (is_bool($var)) {
            return $this->handleBool($var);
        }
        if (is_int($var)) {
            return $this->handleInt($var);
        }
        if (is_float($var)) {
            return $this->handleFloat($var);
        }
        return $this->handleString($var, $depth);
    }

    /**
     * @param bool $var
     * @return string
     */
    protected function handleBool(bool $var): string
    {
        return $this->colorizeScalar($var ? 'true' : 'false');
    }

    /**
     * @param int $var
     * @return string
     */
    protected function handleInt(int $var): string
    {
        return $this->colorizeScalar((string) $var);
    }

    /**
     * @param float $var
     * @return string
     */
    protected function handleFloat(float $var): string
    {
        $string = (string) $var;

        if (str_contains($string, '.') || is_nan($var) || is_infinite($var)) {
            return $this->colorizeScalar($string);
        }

        return $this->colorizeScalar($string . '.0');
    }

    /**
     * @param string $var
     * @param int $depth
     * @return string
     */
    protected function handleString(string $var, int $depth): string
    {
        $singleLine = !str_contains($var, "\n");
        $tooLong = mb_strlen($var) > $this->config->maxStringLength;

        // Trim the string if too long
        // Ellipsis will be added after control and space replacement.
        if ($tooLong) {
            $var = mb_strcut($var, 0, $this->config->maxStringLength);
        }

        // Replace control and space chars with raw string representation.
        $var = (string)preg_replace_callback_array([
            '/[\pC]/u' => fn(array $match) => $this->handleControlChar($match[0]),
            '/[\pZ]/u' => fn(array $match) => $this->handleSpaceChar($match[0]),
        ], $var);

        if ($tooLong) {
            $var .= $this->colorizeComment(' … <truncated>');
        }

        if ($singleLine) {
            return
                $this->colorizeComment('"') .
                $this->colorizeScalar($var) .
                $this->colorizeComment('"');
        }

        $string = $this->colorizeComment('"""') . $this->eol();
        $parts = [];
        foreach (explode('\n', $var) as $line) {
            $parts[] = $this->indent($this->colorizeScalar($line), $depth + 1);
        }
        $string .= implode($this->colorizeEscaped("\\n\n"), $parts);
        $string .= $this->eol();
        $string .= $this->indent($this->colorizeComment('"""'), $depth + 1);
        return $string;
    }

    /**
     * @param string $char
     * @return string
     */
    protected function handleControlChar(string $char): string
    {
        // Use shorthand representation, where possible.
        $escaped = match ($char) {
            "\0" => '\0',
            "\e" => '\e',
            "\f" => '\f',
            "\n" => '\n',
            "\r" => '\r',
            "\t" => '\t',
            "\v" => '\v',
            default => null,
        };

        if ($escaped === null) {
            $codepoint = mb_ord($char);
            $escaped = $codepoint > 255
                ? sprintf('\u%04X', $codepoint)
                : sprintf('\x%02X', $codepoint);
        }

        return $this->colorizeEscaped($escaped);
    }

    /**
     * @param string $space
     * @return string
     */
    protected function handleSpaceChar(string $space): string
    {
        if ($space === ' ') {
            return $space;
        }
        $escaped = sprintf('\u%02X', mb_ord($space));
        return $this->colorizeEscaped($escaped);
    }

    /**
     * @param string $string
     * @return string
     */
    protected function colorizeEscaped(string $string): string
    {
        return Ansi::buffer()
            ->foreground(Color::DarkOrange3_A)
            ->text($string)
            ->foreground($this->scalarColor)
            ->toString();
    }
}
