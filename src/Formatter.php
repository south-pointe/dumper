<?php declare(strict_types=1);

namespace SouthPointe\DataDump;

use Closure;
use DateTime;
use ReflectionReference;
use SouthPointe\DataDump\Casters\Caster;
use SouthPointe\DataDump\Casters\ClosureCaster;
use SouthPointe\DataDump\Casters\DateTimeCaster;
use SouthPointe\DataDump\Casters\EnumCaster;
use SouthPointe\DataDump\Casters\ObjectCaster;
use SouthPointe\DataDump\Decorators\Decorator;
use UnitEnum;
use function array_is_list;
use function array_key_exists;
use function count;
use function get_debug_type;
use function get_resource_id;
use function get_resource_type;
use function is_a;
use function is_array;
use function is_bool;
use function is_float;
use function is_infinite;
use function is_int;
use function is_nan;
use function is_null;
use function is_object;
use function is_resource;
use function is_string;
use function spl_object_id;
use function str_contains;
use function stream_get_meta_data;

class Formatter
{
    /**
     * @var array<class-string, Closure(): Caster>
     */
    protected array $casterResolvers = [];

    /**
     * @var array<class-string, Caster>
     */
    protected array $resolvedCasters = [];

    public function __construct(
        protected Decorator $decorator,
    )
    {
        $this->casterResolvers += [
            Closure::class => fn() => new ClosureCaster($this->decorator, $this),
            DateTime::class => fn() => new DateTimeCaster($this->decorator, $this),
            UnitEnum::class => fn() => new EnumCaster($this->decorator, $this),
        ];
    }

    /**
     * @param mixed $var
     * @param int $depth
     * @param array<int, bool> $objectIds
     * @return string
     */
    public function format(mixed $var, int $depth, array $objectIds = []): string
    {
        return match (true) {
            is_null($var) => $this->formatNull(),
            is_string($var) => $this->formatString($var),
            is_bool($var) => $this->formatBool($var),
            is_int($var) => $this->formatInt($var),
            is_float($var) => $this->formatFloat($var),
            is_object($var) => $this->formatObject($var, $depth, $objectIds),
            is_array($var) => $this->formatArray($var, $depth, $objectIds),
            is_resource($var) => $this->formatResource($var, $depth),
            default => $this->formatUnknown($var, $depth),
        };
    }

    /**
     * @return string
     */
    protected function formatNull(): string
    {
        return $this->decorator->scalar('null');
    }

    /**
     * @param string $var
     * @return string
     */
    protected function formatString(string $var): string
    {
        return $this->decorator->scalar("\"{$var}\"");
    }

    /**
     * @param bool $var
     * @return string
     */
    protected function formatBool(bool $var): string
    {
        return $this->decorator->scalar($var ? 'true' : 'false');
    }

    /**
     * @param int $var
     * @return string
     */
    protected function formatInt(int $var): string
    {
        return $this->decorator->scalar((string) $var);
    }

    /**
     * @param float $var
     * @return string
     */
    protected function formatFloat(float $var): string
    {
        $deco = $this->decorator;

        $string = (string) $var;

        if (str_contains($string, '.') || is_nan($var) || is_infinite($var)) {
            return $deco->scalar($string);
        }

        return $deco->scalar($string . '.0');
    }

    /**
     * @param array<mixed> $var
     * @param int $depth
     * @param array<int, bool> $objectIds
     * @return string
     */
    protected function formatArray(array $var, int $depth, array $objectIds): string
    {
        $deco = $this->decorator;

        $start = $deco->classType('array(' . count($var) . ')') . ' [';
        $end = ']';

        if (count($var) === 0) {
            return "{$start}{$end}";
        }

        return $this->block(
            $start,
            $end,
            $depth,
            function(int $depth) use ($deco, $var, $objectIds) {
                $string = '';
                $isList = array_is_list($var);
                foreach ($var as $key => $val) {
                    $decoKey = $deco->arrayKey($isList ? $key : "\"{$key}\"");
                    $decoVal = $this->format($val, $depth, $objectIds);
                    $ref = $deco->refSymbol(ReflectionReference::fromArrayElement($var, $key) ? '&' : '');
                    $arrow = $deco->parameterDelimiter('=>');
                    $comma = $deco->parameterDelimiter(',');
                    $string .= $deco->line("{$decoKey} {$arrow} {$ref}{$decoVal}{$comma}", $depth);
                }
                return $string;
            },
        );
    }

    /**
     * @param object $var
     * @param int $depth
     * @param array<int, bool> $objectIds
     * @return string
     */
    protected function formatObject(object $var, int $depth, array $objectIds): string
    {
        $id = spl_object_id($var);
        return $this->getCaster($var)->cast($var, $id, $depth, $objectIds);
    }

    /**
     * @param resource $var
     * @param int $depth
     * @return string
     */
    protected function formatResource(mixed $var, int $depth): string
    {
        $deco = $this->decorator;

        $type = get_resource_type($var);
        $id = get_resource_id($var);

        // Will get Unknown if resource is closed.
        if ($type === 'Unknown') {
            return
                $deco->resourceType('resource (closed)') . ' ' .
                $deco->comment("@{$id}");
        }

        $summary =
            $deco->resourceType($type) . ' ' .
            $deco->comment("@{$id}");

        return $this->block(
            "{$summary} {",
            "}",
            $depth,
            function(int $depth) use ($deco, $var) {
                $string = '';
                foreach (stream_get_meta_data($var) as $key => $val) {
                    $decoKey = $deco->parameterKey($key);
                    $decoVal = $this->format($val, $depth);
                    $arrow = $deco->parameterDelimiter(':');
                    $comma = $deco->parameterDelimiter(',');
                    $string .= $deco->line("{$decoKey}{$arrow} {$decoVal}{$comma}", $depth);
                }
                return $string;
            },
        );
    }

    /**
     * @param mixed $var
     * @param int $depth
     * @return string
     */
    protected function formatUnknown(mixed $var, int $depth): string
    {
        $type = get_debug_type($var);

        /**
         * HACK: Function is_resource(...) cannot detect closed resources, so we have to
         * detect it using get_debug_type(...) which can detect them.
         * @see https://www.php.net/manual/en/function.is-resource.php#refsect1-function.is-resource-notes
         */
        if ($type === 'resource (closed)') {
            return $this->formatResource($var, $depth);
        }

        return $this->decorator->classType($type);
    }

    /**
     * @param string $start
     * @param string $end
     * @param int $depth
     * @param Closure(int): string $block
     * @return string
     */
    public function block(string $start, string $end, int $depth, Closure $block): string
    {
        $deco = $this->decorator;

        $string = ($depth === 0)
            ? $deco->line($start, $depth)
            : $start . $deco->eol();

        ++$depth;
        $string .= $block($depth);
        --$depth;

        $string .= $deco->indent($end, $depth);

        if ($depth === 0) {
            $string .= $deco->eol();
        }

        return $string;
    }

    /**
     * @param class-string $class
     * @param Closure(): Caster $callback
     * @return void
     */
    public function setCaster(string $class, Closure $callback): void
    {
        $this->casterResolvers[$class] = $callback;
    }

    /**
     * @param object $var
     * @return Caster
     */
    protected function getCaster(object $var): Caster
    {
        $class = $var::class;

        // Check if class already exists in resolved casters.
        if (array_key_exists($class, $this->resolvedCasters)) {
            return $this->resolvedCasters[$class];
        }

        // Check if class exists as resolver.
        if (array_key_exists($class, $this->casterResolvers)) {
            return $this->resolvedCasters[$class] ??= ($this->casterResolvers[$class])();
        }

        // Even if the class doesn't exist, check through all resolvers
        // and see if it inherits any registered classes.
        foreach ($this->casterResolvers as $_class => $resolver) {
            if (is_a($var, $_class)) {
                return $this->resolvedCasters[$_class] = $resolver();
            }
        }

        // If no match is found set it to null and let it run the default.
        return $this->resolvedCasters[$class] = new ObjectCaster($this->decorator, $this);
    }
}
