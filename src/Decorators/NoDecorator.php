<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Decorators;

use SouthPointe\DataDump\Options;
use const PHP_EOL;

class NoDecorator implements Decorator
{
    /**
     * @param Options $options
     */
    public function __construct(
        protected Options $options = new Options(),
    )
    {
    }

    /**
     * @param string $string
     * @return string
     */
    public function root(string $string): string
    {
        return $string . $this->eol();
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
     * @param string $string
     * @return string
     */
    public function escapedString(string $string): string
    {
        return $string;
    }

    /**
     * @param string $type
     * @return string
     */
    public function classType(string $type): string
    {
        return $type;
    }

    /**
     * @param string $type
     * @return string
     */
    public function resourceType(string $type): string
    {
        return $type;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function scalar(mixed $value): string
    {
        return (string) $value;
    }

    /**
     * @param int|string $key
     * @return string
     */
    public function parameterKey(int|string $key): string
    {
        return (string) $key;
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function parameterDelimiter(string $delimiter): string
    {
        return $delimiter;
    }

    /**
     * @param int|string $key
     * @return string
     */
    public function arrayKey(int|string $key): string
    {
        return (string) $key;
    }

    /**
     * @param string $comment
     * @return string
     */
    public function comment(string $comment): string
    {
        return $comment;
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
        return str_repeat($this->options->indentation, $depth) . $string;
    }

    /**
     * @return string
     */
    public function eol(): string
    {
        return PHP_EOL;
    }
}
