<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Handlers;

use ReflectionClass;
use ReflectionProperty;
use SouthPointe\Ansi\Codes\Color;
use SouthPointe\DataDump\Decorators\Decorator;
use SouthPointe\DataDump\Options;
use function array_key_exists;
use function count;
use function method_exists;

class ClassHandler extends Handler
{
    /**
     * @param object $var
     * @param int $id
     * @param int $depth
     * @param array<int, bool> $objectIds
     * @return string
     */
    public function handle(object $var, int $id, int $depth, array $objectIds): string
    {
        $properties = $this->getProperties($var);

        $summary =
            $this->colorizeName($var::class) . ' ' .
            $this->colorizeComment("#{$id}");

        if (count($properties) === 0) {
            return $summary;
        }

        if (array_key_exists($id, $objectIds)) {
            return
                $summary . ' ' .
                $this->colorizeComment('<circular>') . ' ' .
                '{ ' .
                $this->colorizeComment('⋯') .
                ' }';
        }

        $objectIds[$id] ??= true;

        $string = "{$summary} {" . $this->eol();
        foreach ($properties as $key => $val) {
            $string .= $this->line(
                $this->colorizeKey($key) .
                $this->colorizeDelimiter(':') . ' ' .
                $this->formatter->format($val, $depth + 1, $objectIds),
                $depth + 1,
            );
        }
        $string .= $this->indent('}', $depth);

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

        $classReflection = new ReflectionClass($var);
        $propertyReflections = $classReflection->getProperties(
            $this->options->classPropertyFilter,
        );

        $properties = [];
        foreach ($propertyReflections as $reflection) {
            $access = ($reflection->getModifiers() & ReflectionProperty::IS_STATIC)
                ? 'static '
                : '';
            $name = $access . $reflection->getName();
            $value = $reflection->getValue($var);
            $properties[$name] = $value;
        }
        return $properties;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function colorizeName(string $name): string
    {
        return $this->colorize($name, Color::DarkCyan);
    }
}
