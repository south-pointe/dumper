<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Formatters;

use Closure;
use DateTime;
use SouthPointe\DataDump\Decorators\Decorator;
use SouthPointe\DataDump\Options;
use Throwable;
use UnitEnum;
use function get_debug_type;
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

class AutoFormatter
{
    /**
     * @param Decorator $decorator
     * @param Options $options
     * @param StringFormatter|null $stringFormatter
     * @param ArrayFormatter|null $arrayFormatter
     * @param ResourceFormatter|null $resourceFormatter
     * @param ClassFormatterRegistry|null $classFormatterRegistry
     */
    public function __construct(
        protected readonly Decorator $decorator,
        protected readonly Options $options,
        protected ?StringFormatter $stringFormatter = null,
        protected ?ArrayFormatter $arrayFormatter = null,
        protected ?ResourceFormatter $resourceFormatter = null,
        protected ?ClassFormatterRegistry $classFormatterRegistry = null,
    )
    {
    }

    /**
     * @param mixed $var
     * @param int $depth
     * @param array<int, bool> $objectIds
     * @return string
     */
    public function format(mixed $var, int $depth = 0, array $objectIds = []): string
    {
        $string = match (true) {
            is_null($var) => $this->formatNull(),
            is_string($var) => $this->formatString($var, $depth),
            is_bool($var) => $this->formatBool($var),
            is_int($var) => $this->formatInt($var),
            is_float($var) => $this->formatFloat($var),
            is_object($var) => $this->formatObject($var, $depth, $objectIds),
            is_array($var) => $this->formatArray($var, $depth, $objectIds),
            is_resource($var) => $this->formatResource($var, $depth),
            default => $this->formatUnknown($var, $depth),
        };

        return $depth === 0
            ? $this->decorator->root($string)
            : $string;
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
     * @param int $depth
     * @return string
     */
    protected function formatString(string $var, int $depth): string
    {
        $formatter = $this->stringFormatter ??= new StringFormatter($this->decorator, $this->options);
        return $formatter->format($var, $depth);
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
        $formatter = $this->arrayFormatter ??= new ArrayFormatter($this->decorator, $this);
        return $formatter->format($var, $depth, $objectIds);
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
        $formatter = $this->getClassFormatter($var::class);
        return $formatter->format($var, $id, $depth, $objectIds);
    }

    /**
     * @param resource $var
     * @param int $depth
     * @return string
     */
    protected function formatResource(mixed $var, int $depth): string
    {
        $formatter = $this->resourceFormatter ??= new ResourceFormatter($this, $this->decorator);
        return $formatter->format($var, $depth);
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
     * @param class-string $class
     * @param Closure(): ClassFormatter $callback
     * @return void
     */
    public function setClassFormatter(string $class, Closure $callback): void
    {
        $this->getClassFormatterRegistry()->set($class, $callback);
    }

    /**
     * @param class-string $class
     * @return ClassFormatter
     */
    protected function getClassFormatter(string $class): ClassFormatter
    {
        return $this->getClassFormatterRegistry()->get($class);
    }

    protected function getClassFormatterRegistry(): ClassFormatterRegistry
    {
        if ($this->classFormatterRegistry !== null) {
            return $this->classFormatterRegistry;
        }

        $classResolvers = [
            Closure::class => fn() => new ClosureFormatter($this, $this->decorator),
            DateTime::class => fn() => new DateTimeFormatter($this, $this->decorator),
            Throwable::class => fn() => new ThrowableFormatter($this, $this->decorator),
            UnitEnum::class => fn() => new EnumFormatter($this, $this->decorator),
        ];

        $fallbackResolver = fn() => new ClassFormatter($this, $this->decorator);

        return $this->classFormatterRegistry = new ClassFormatterRegistry(
            $classResolvers,
            $fallbackResolver,
        );
    }
}
