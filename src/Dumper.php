<?php declare(strict_types=1);

namespace SouthPointe\DataDump;

use SouthPointe\DataDump\Decorators\AnsiDecorator;
use SouthPointe\DataDump\Decorators\Decorator;
use SouthPointe\DataDump\Decorators\NoDecorator;
use SouthPointe\DataDump\Formatters\AutoFormatter;

class Dumper
{
    /**
     * @var AutoFormatter
     */
    protected AutoFormatter $autoFormatter;

    /**
     * @param AutoFormatter|null $autoFormatter
     * @param Writer $writer
     * @param Options $options
     */
    public function __construct(
        ?AutoFormatter $autoFormatter = null,
        protected Writer $writer = new Writer(),
        protected Options $options = new Options(),
    )
    {
        $this->autoFormatter = $autoFormatter ?? $this->makeAutoFormatter();
    }

    /**
     * @param mixed $var
     * @return void
     */
    public function dump(mixed $var): void
    {
        $this->writer->write(
            $this->autoFormatter->format($var)
        );
    }

    /**
     * @return AutoFormatter
     */
    protected function makeAutoFormatter(): AutoFormatter
    {
        return new AutoFormatter(
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
