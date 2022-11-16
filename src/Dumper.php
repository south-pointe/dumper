<?php declare(strict_types=1);

namespace SouthPointe\Dumper;

use SouthPointe\Dumper\Decorators\AnsiDecorator;
use SouthPointe\Dumper\Decorators\Decorator;
use SouthPointe\Dumper\Decorators\HtmlDecorator;
use SouthPointe\Dumper\Decorators\PlainDecorator;

class Dumper
{
    /**
     * @var Formatter
     */
    protected Formatter $formatter;

    /**
     * @param Formatter|null $formatter
     * @param Writer $writer
     * @param Config $config
     */
    public function __construct(
        ?Formatter $formatter = null,
        protected Writer $writer = new Writer(),
        protected Config $config = new Config(),
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
            $this->config,
        );
    }

    /**
     * @return Decorator
     */
    protected function makeDefaultDecorator(): Decorator
    {
        return match ($this->config->decorator) {
            'cli' => new AnsiDecorator($this->config),
            'html' => new HtmlDecorator($this->config),
            default => new PlainDecorator($this->config),
        };
    }
}
