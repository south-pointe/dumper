<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Formatters;

use SouthPointe\DataDump\Decorators\Decorator;
use SouthPointe\DataDump\Options;
use function explode;
use function implode;
use function mb_ord;
use function mb_strcut;
use function preg_replace_callback_array;
use function sprintf;
use function str_contains;

class StringFormatter
{
    /**
     * @param Decorator $decorator
     * @param Options $options
     */
    public function __construct(
        protected Decorator $decorator,
        protected Options $options,
    )
    {
    }

    /**
     * @param string $var
     * @param int $depth
     * @return string
     */
    public function format(string $var, int $depth): string
    {
        $deco = $this->decorator;
        $singleLine = !str_contains($var, "\n");
        $tooLong = mb_strlen($var) > $this->options->maxStringLength;

        // Trim the string if too long
        // Ellipsis will be added after control and space replacement.
        if ($tooLong) {
            $var = mb_strcut($var, 0, $this->options->maxStringLength);
        }

        // Replace control and space chars with raw string representation.
        $var = (string) preg_replace_callback_array([
            '/[\pC]/u' => fn(array $match) => $this->formatControlChar($match[0]),
            '/[\pZ]/u' => fn(array $match) => $this->formatSpaceChar($match[0]),
        ], $var);

        if ($tooLong) {
            $var.= $deco->comment(' … <truncated>');
        }

        if ($singleLine) {
            return
                $deco->comment('"') .
                $deco->scalar($var) .
                $deco->comment('"');
        }

        $string = $deco->comment('"""') . $deco->eol();
        $parts = [];
        foreach (explode('\n', $var) as $line) {
            $parts[]= $deco->indent($deco->scalar($line), $depth + 1);
        }
        $string.= implode($deco->escapedString("\\n\n"), $parts);
        $string.= $deco->eol();
        $string.= $deco->indent($deco->comment('"""'), $depth + 1);
        return $string;
    }

    /**
     * @param string $char
     * @return string
     */
    protected function formatControlChar(string $char): string
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

        return $this->decorator->escapedString($escaped);
    }

    /**
     * @param string $space
     * @return string
     */
    protected function formatSpaceChar(string $space): string
    {
        if ($space === ' ') {
            return $space;
        }
        $escaped = sprintf('\u%02X', mb_ord($space));
        return $this->decorator->escapedString($escaped);
    }
}
