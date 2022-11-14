<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Handlers;

use LogicException;
use ReflectionClass;
use ReflectionProperty;
use SouthPointe\Ansi\Codes\Color;
use SouthPointe\DataDump\Decorators\Decorator;
use SouthPointe\DataDump\Config;
use function array_key_exists;
use function array_merge;
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
        $debugInfoOption = $this->config->debugInfo;

        if ($debugInfoOption === Config::DEBUG_INFO_OVERWRITE) {
            $debugInfo = $this->getDebugInfo($var);
            if ($debugInfo !== null) {
                return $debugInfo;
            }
        }

        $classReflection = new ReflectionClass($var);
        $propertyReflections = $classReflection->getProperties(
            $this->config->classPropertyFilter,
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

        if ($debugInfoOption === Config::DEBUG_INFO_APPEND) {
            $debugInfo = $this->getDebugInfo($var);
            if ($debugInfo !== null) {
                $properties = array_merge($debugInfo, $properties);
            }
        }

        return $properties;
    }

    /**
     * @param object $var
     * @return array<string, mixed>|null
     */
    protected function getDebugInfo(object $var): ?array
    {
        if (!method_exists($var, '__debugInfo')) {
            return null;
        }

        return $var->__debugInfo();
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
