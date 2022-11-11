<?php declare(strict_types=1);

namespace SouthPointe\DataDump;

use const PHP_SAPI;

class Options
{
    public function __construct(
        public readonly int $maxStringLength = 5000,
        public readonly string $indentation = '  ',
        public readonly string $decorator = PHP_SAPI,
    )
    {
    }
}
