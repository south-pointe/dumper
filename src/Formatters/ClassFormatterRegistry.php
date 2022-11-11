<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Formatters;

use Closure;
use function array_key_exists;
use function is_a;

class ClassFormatterRegistry
{
    /**
     * @var array<class-string, ClassFormatter>
     */
    protected array $resolved = [];

    /**
     * @var ClassFormatter|null
     */
    protected ?ClassFormatter $fallback = null;

    /**
     * @param array<class-string, Closure(): ClassFormatter> $resolvers
     * @param Closure(): ClassFormatter $fallbackResolver
     */
    public function __construct(
        protected array $resolvers,
        protected Closure $fallbackResolver,
    )
    {
    }

    /**
     * @param class-string $class
     * @param Closure(): ClassFormatter $callback
     * @return void
     */
    public function set(string $class, Closure $callback): void
    {
        $this->resolvers[$class] = $callback;
    }

    /**
     * @param class-string $class
     * @return ClassFormatter
     */
    public function get(string $class): ClassFormatter
    {
        // Check if class already exists in resolved formatters.
        if (array_key_exists($class, $this->resolved)) {
            return $this->resolved[$class];
        }

        // Check if class exists as resolver.
        if (array_key_exists($class, $this->resolvers)) {
            return $this->resolved[$class] ??= ($this->resolvers[$class])();
        }

        // Even if the class doesn't exist, check through all resolvers
        // and see if it inherits any registered classes.
        foreach ($this->resolvers as $registered => $resolver) {
            if (is_a($class, $registered, true)) {
                return $this->resolved[$registered] = $resolver();
            }
        }

        // If no match is found set it to null and let it run the default.
        return $this->fallback ??= ($this->fallbackResolver)();
    }
}
