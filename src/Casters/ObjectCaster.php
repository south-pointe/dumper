<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Casters;

use ReflectionClass;
use ReflectionProperty;
use function array_key_exists;
use function count;
use function method_exists;

class ObjectCaster extends Caster
{
    /**
     * @inheritDoc
     */
    public function cast(object $var, int $id, int $depth, array $objectIds): string
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

        return $this->formatter->block(
            "{$summary} {",
            "}",
            $depth,
            function (int $depth) use ($deco, $properties, $objectIds) {
                $string = '';
                foreach ($properties as $key => $val) {
                    $string .= $deco->line(
                        $deco->parameterKey($key) .
                        $deco->parameterDelimiter(':') . ' ' .
                        $this->formatter->format($val, $depth, $objectIds),
                        $depth,
                    );
                }
                return $string;
            },
        );
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
