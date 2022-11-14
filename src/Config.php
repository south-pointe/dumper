<?php declare(strict_types=1);

namespace SouthPointe\DataDump;

use ReflectionProperty;
use const PHP_SAPI;

class Config
{
    protected const CLASS_PROPERTY_FILTER_DEFAULT =
        ReflectionProperty::IS_STATIC |
        ReflectionProperty::IS_PUBLIC |
        ReflectionProperty::IS_PROTECTED |
        ReflectionProperty::IS_PRIVATE;

    public const DEBUG_INFO_IGNORE = 0;
    public const DEBUG_INFO_OVERWRITE = 1;
    public const DEBUG_INFO_APPEND = 2;

    /**
     * @param int $indentSize
     * @param int $maxStringLength
     * @param string $decorator
     * @param string $dateTimeFormat
     * @param int $classPropertyFilter
     * @param int $debugInfo
     */
    public function __construct(
        public readonly int $indentSize = 2,
        public readonly int $maxStringLength = 5000,
        public readonly string $decorator = PHP_SAPI,
        public readonly string $dateTimeFormat = 'Y-m-d H:i:s.u T (P)',
        public readonly int $classPropertyFilter = self::CLASS_PROPERTY_FILTER_DEFAULT,
        public readonly int $debugInfo = self::DEBUG_INFO_OVERWRITE,
    )
    {
    }
}
