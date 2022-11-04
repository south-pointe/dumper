<?php declare(strict_types=1);

namespace SouthPointe\DataDump;

use SouthPointe\DataDump\Decorators\Decorator;
use function debug_backtrace;
use const DEBUG_BACKTRACE_IGNORE_ARGS;

class Dumper
{
    public function __construct(
        protected Decorator $decorator,
        protected Formatter $formatter,
    )
    {
    }

    /**
     * @param mixed $var
     * @return void
     */
    public function dump(mixed $var): void
    {
        $string = $this->formatter->format($var, 0);
        $this->decorator->output($string);
    }

    protected function getCallee(): string
    {
        $callStack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        return $this->decorator->comment($callee);
    }

}
