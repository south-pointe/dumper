<?php declare(strict_types=1);

namespace SouthPointe\DataDump;

class Dumper
{
    /**
     * @param Formatter $formatter
     * @param Writer $writer
     */
    public function __construct(
        protected Formatter $formatter = new Formatter(),
        protected Writer $writer = new Writer(),
    )
    {
    }

    /**
     * @param mixed $var
     * @return void
     */
    public function dump(mixed $var): void
    {
        $this->writer->write(
            $this->formatter->format($var)
        );
    }
}
