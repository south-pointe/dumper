<?php declare(strict_types=1);

namespace SouthPointe\Dumper;

use ReflectionProperty;
use SouthPointe\Dumper\Configs\DebugInfo;
use const PHP_SAPI;

class Config
{
    protected const PROPERTY_FILTER_DEFAULT =
        ReflectionProperty::IS_STATIC |
        ReflectionProperty::IS_PUBLIC |
        ReflectionProperty::IS_PROTECTED |
        ReflectionProperty::IS_PRIVATE;

    /**
     * @param int $indentSize
     * @param int $maxStringLength
     * @param string $decorator
     * @param string $dateTimeFormat
     * @param int $propertyFilter
     * @param DebugInfo $debugInfo
     */
    public function __construct(
        public readonly string $decorator = PHP_SAPI,
        public readonly int $indentSize = 2,
        public readonly int $maxStringLength = 5000,
        public readonly string $dateTimeFormat = 'Y-m-d H:i:s.u T (P)',
        public readonly int $propertyFilter = self::PROPERTY_FILTER_DEFAULT,
        public readonly DebugInfo $debugInfo = DebugInfo::Overwrite,
    )
    {
    }
}
