<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Casters;

use DateTime;
use ReflectionClass;
use ReflectionProperty;
use function array_key_exists;
use function count;

class ObjectCaster extends Caster
{
    /**
     * @param DateTime $var
     * @param int $id
     * @param int $depth
     * @param array<int, object> $objectRegistrar
     * @return string
     */
    public function cast(object $var, int $id, int $depth, array &$objectRegistrar): string
    {
        $properties = (new ReflectionClass($var))->getProperties(
            ReflectionProperty::IS_STATIC |
            ReflectionProperty::IS_PUBLIC |
            ReflectionProperty::IS_PROTECTED |
            ReflectionProperty::IS_PRIVATE,
        );

        $summary =
            $this->decorator->type($var::class) . ' ' .
            $this->decorator->comment("#{$id}");

        if (count($properties) === 0) {
            return $summary;
        }

        if (array_key_exists($id, $objectRegistrar)) {
            return $summary . ' ' . $this->decorator->comment('<circular>');
        }

        return $this->formatter->block(
            "{$summary} {",
            "}",
            $depth,
            function (int $depth) use ($var, $id, $properties, $objectRegistrar) {
                $objectRegistrar[$id] ??= $var;
                $string = '';
                foreach ($properties as $prop) {
                    $access = ($prop->getModifiers() & ReflectionProperty::IS_STATIC)
                        ? 'static '
                        : '';
                    $string .= $this->decorator->line(
                        $this->decorator->parameterKey($access . $prop->getName()) .
                        $this->decorator->parameterDelimiter(':') . ' ' .
                        $this->formatter->format($prop->getValue($var), $depth, $objectRegistrar) .
                        $this->decorator->parameterDelimiter(','),
                        $depth,
                    );
                }
                unset($objectRegistrar[$id]);
                return $string;
            },
        );
    }
}
