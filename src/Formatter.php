<?php declare(strict_types=1);

namespace SouthPointe\DataDump;

use Closure;
use DateTime;
use SouthPointe\DataDump\Decorators\Decorator;
use SouthPointe\DataDump\Handlers\ArrayHandler;
use SouthPointe\DataDump\Handlers\ClassHandler;
use SouthPointe\DataDump\Handlers\ClassHandlerFactory;
use SouthPointe\DataDump\Handlers\ClosureHandler;
use SouthPointe\DataDump\Handlers\DateTimeHandler;
use SouthPointe\DataDump\Handlers\EnumHandler;
use SouthPointe\DataDump\Handlers\NullHandler;
use SouthPointe\DataDump\Handlers\ResourceHandler;
use SouthPointe\DataDump\Handlers\ScalarHandler;
use SouthPointe\DataDump\Handlers\ThrowableHandler;
use Throwable;
use UnitEnum;
use function get_debug_type;
use function is_array;
use function is_null;
use function is_object;
use function is_resource;
use function is_scalar;
use function spl_object_id;

class Formatter
{
    /**
     * @param Decorator $decorator
     * @param Config $config
     * @param NullHandler|null $nullHandler
     * @param ScalarHandler|null $scalarHandler
     * @param ArrayHandler|null $arrayHandler
     * @param ResourceHandler|null $resourceHandler
     * @param ClassHandlerFactory|null $classHandlerFactory
     */
    public function __construct(
        protected Decorator $decorator,
        protected Config $config,
        protected ?NullHandler $nullHandler = null,
        protected ?ScalarHandler $scalarHandler = null,
        protected ?ArrayHandler $arrayHandler = null,
        protected ?ResourceHandler $resourceHandler = null,
        protected ?ClassHandlerFactory $classHandlerFactory = null,
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
            is_scalar($var) => $this->formatScalar($var, $depth),
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
        $handler = $this->nullHandler ??= new NullHandler($this, $this->decorator, $this->config);
        return $handler->handle(null);
    }

    /**
     * @param scalar $var
     * @param int $depth
     * @return string
     */
    protected function formatScalar(bool|int|float|string $var, int $depth): string
    {
        $handler = $this->scalarHandler ??= new ScalarHandler($this, $this->decorator, $this->config);
        return $handler->handle($var, $depth);
    }

    /**
     * @param array<mixed> $var
     * @param int $depth
     * @param array<int, bool> $objectIds
     * @return string
     */
    protected function formatArray(array $var, int $depth, array $objectIds): string
    {
        $handler = $this->arrayHandler ??= new ArrayHandler($this, $this->decorator, $this->config);
        return $handler->handle($var, $depth, $objectIds);
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
        return $this->getClassHandler($var)->handle($var, $id, $depth, $objectIds);
    }

    /**
     * @param resource $var
     * @param int $depth
     * @return string
     */
    protected function formatResource(mixed $var, int $depth): string
    {
        $handler = $this->resourceHandler ??= new ResourceHandler($this, $this->decorator, $this->config);
        return $handler->handle($var, $depth);
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

        return $type;
    }

    /**
     * @param class-string $class
     * @param Closure(): ClassHandler $callback
     * @return void
     */
    public function setClassHandler(string $class, Closure $callback): void
    {
        $this->getClassHandlerFactory()->set($class, $callback);
    }

    /**
     * @param object $var
     * @return ClassHandler
     */
    protected function getClassHandler(object $var): ClassHandler
    {
        return $this->getClassHandlerFactory()->get($var::class);
    }

    protected function getClassHandlerFactory(): ClassHandlerFactory
    {
        return $this->classHandlerFactory ??= new ClassHandlerFactory(
            $this->makeDefaultClassResolvers(),
            $this->makeFallbackClassHandlerResolver(),
        );
    }

    /**
     * @return array<class-string, Closure(): ClassHandler>
     */
    protected function makeDefaultClassResolvers(): array
    {
        return [
            Closure::class => fn() => new ClosureHandler($this, $this->decorator, $this->config),
            DateTime::class => fn() => new DateTimeHandler($this, $this->decorator, $this->config),
            Throwable::class => fn() => new ThrowableHandler($this, $this->decorator, $this->config),
            UnitEnum::class => fn() => new EnumHandler($this, $this->decorator, $this->config),
        ];
    }

    /**
     * @return Closure(): ClassHandler
     */
    protected function makeFallbackClassHandlerResolver(): Closure
    {
        return fn() => new ClassHandler($this, $this->decorator, $this->config);
    }
}
