<?php declare(strict_types=1);

namespace SouthPointe\DataDump;

class Options
{
    public function __construct(
        public readonly int $maxStringLength = 5000,
    )
    {
    }
}
