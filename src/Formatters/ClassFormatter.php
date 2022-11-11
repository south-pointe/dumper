<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Formatters;

use ReflectionClass;
use ReflectionProperty;
use SouthPointe\DataDump\Decorators\Decorator;
use function array_key_exists;
use function count;
use function method_exists;

class ClassFormatter
{
    /**
     * @param AutoFormatter $autoFormatter
     * @param Decorator $decorator
     */
    public function __construct(
        protected AutoFormatter $autoFormatter,
        protected Decorator $decorator,
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
    public function format(object $var, int $id, int $depth, array $objectIds): string
    {
        $deco = $this->decorator;
        $properties = $this->getProperties($var);

        $summary =
            $deco->classType($var::class) . ' ' .
            $deco->comment("#{$id}");

        if (count($properties) === 0) {
            return $summary;
        }

        if (array_key_exists($id, $objectIds)) {
            return
                $summary . ' ' .
                $deco->comment('<circular>') . ' ' .
                '{ ' .
                $deco->comment('⋯') .
                ' }';
        }

        $objectIds[$id] ??= true;

        $string = "{$summary} {" . $deco->eol();
        foreach ($properties as $key => $val) {
            $string .= $deco->line(
                $deco->parameterKey($key) .
                $deco->parameterDelimiter(':') . ' ' .
                $this->autoFormatter->format($val, $depth + 1, $objectIds),
                $depth + 1,
            );
        }
        $string .= $deco->indent('}', $depth);

        return $string;
    }

    /**
     * @param object $var
     * @return array<string, mixed>
     */
    protected function getProperties(object $var): array
    {
        if (method_exists($var, '__debugInfo')) {
            return $var->__debugInfo();
        }

        $reflections = (new ReflectionClass($var))->getProperties(
            ReflectionProperty::IS_STATIC |
            ReflectionProperty::IS_PUBLIC |
            ReflectionProperty::IS_PROTECTED |
            ReflectionProperty::IS_PRIVATE,
        );
        $properties = [];
        foreach ($reflections as $reflection) {
            $access = ($reflection->getModifiers() & ReflectionProperty::IS_STATIC)
                ? 'static '
                : '';
            $name = $access . $reflection->getName();
            $value = $reflection->getValue($var);
            $properties[$name] = $value;
        }
        return $properties;
    }
}
