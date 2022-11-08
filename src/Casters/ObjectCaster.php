<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Casters;

use ReflectionClass;
use ReflectionProperty;
use function array_key_exists;
use function count;

class ObjectCaster extends Caster
{
    /**
     * @inheritDoc
     */
    public function cast(object $var, int $id, int $depth, array $objectIds): string
    {
        $deco = $this->decorator;

        $properties = (new ReflectionClass($var))->getProperties(
            ReflectionProperty::IS_STATIC |
            ReflectionProperty::IS_PUBLIC |
            ReflectionProperty::IS_PROTECTED |
            ReflectionProperty::IS_PRIVATE,
        );

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
            function (int $depth) use ($deco, $var, $properties, $objectIds) {
                $string = '';
                foreach ($properties as $prop) {
                    $access = ($prop->getModifiers() & ReflectionProperty::IS_STATIC)
                        ? 'static '
                        : '';
                    $string .= $deco->line(
                        $deco->parameterKey($access . $prop->getName()) .
                        $deco->parameterDelimiter(':') . ' ' .
                        $this->formatter->format($prop->getValue($var), $depth, $objectIds) .
                        $deco->parameterDelimiter(','),
                        $depth,
                    );
                }
                return $string;
            },
        );
    }
}
