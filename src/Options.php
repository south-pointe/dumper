<?php declare(strict_types=1);

namespace SouthPointe\DataDump;

use ReflectionProperty;
use const PHP_SAPI;

class Options
{
    protected const CLASS_PROPERTY_FILTER_DEFAULT =
        ReflectionProperty::IS_STATIC |
        ReflectionProperty::IS_PUBLIC |
        ReflectionProperty::IS_PROTECTED |
        ReflectionProperty::IS_PRIVATE;

    public function __construct(
        public readonly int $maxStringLength = 5000,
        public readonly string $indentation = '  ',
        public readonly string $decorator = PHP_SAPI,
        public readonly int $classPropertyFilter = self::CLASS_PROPERTY_FILTER_DEFAULT,
    )
    {
    }
}
