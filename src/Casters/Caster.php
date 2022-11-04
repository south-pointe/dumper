<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Casters;

use SouthPointe\DataDump\Decorators\Decorator;
use SouthPointe\DataDump\Formatter;

abstract class Caster
{
    public function __construct(
        protected Decorator $decorator,
        protected Formatter $formatter,
    )
    {
    }

    /**
     * @param object $var
     * @param int $id
     * @param int $depth
     * @param array<int, bool> $objectIds
     * @return string
     */
    abstract public function cast(object $var, int $id, int $depth, array $objectIds): string;
}
