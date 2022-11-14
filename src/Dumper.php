<?php declare(strict_types=1);

namespace SouthPointe\DataDump;

use SouthPointe\DataDump\Decorators\AnsiDecorator;
use SouthPointe\DataDump\Decorators\Decorator;
use SouthPointe\DataDump\Decorators\NoDecorator;

class Dumper
{
    /**
     * @var Formatter
     */
    protected Formatter $formatter;

    /**
     * @param Formatter|null $formatter
     * @param Writer $writer
     * @param Options $options
     */
    public function __construct(
        ?Formatter $formatter = null,
        protected Writer $writer = new Writer(),
        protected Options $options = new Options(),
    )
    {
        $this->formatter = $formatter ?? $this->makeFormatter();
    }

    /**
     * @param mixed $var
     * @return void
     */
    public function dump(mixed $var): void
    {
        $this->writer->write(
            $this->formatter->format($var),
        );
    }

    /**
     * @return Formatter
     */
    protected function makeFormatter(): Formatter
    {
        return new Formatter(
            $this->makeDefaultDecorator(),
            $this->options,
        );
    }

    /**
     * @return Decorator
     */
    protected function makeDefaultDecorator(): Decorator
    {
        return match ($this->options->decorator) {
            'cli' => new AnsiDecorator($this->options),
            default => new NoDecorator($this->options),
        };
    }
}
