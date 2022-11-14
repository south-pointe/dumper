<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Handlers;

use Closure;
use ReflectionFunction;
use function assert;

class ClosureHandler extends ClassHandler
{
    /**
     * @param Closure $var
     * @inheritDoc
     */
    public function handle(object $var, int $id, int $depth, array $objectIds): string
    {
        assert($var instanceof Closure);

        $ref = new ReflectionFunction($var);

        if ($file = $ref->getFileName()) {
            $startLine = $ref->getStartLine();
            $endLine = $ref->getEndLine();
            $range = ($startLine !== $endLine)
                ? "{$startLine}-{$endLine}"
                : $startLine;
            return
                $this->colorizeName($var::class . "@{$file}:{$range}") . ' ' .
                $this->colorizeComment("#{$id}");
        }

        if ($class = $ref->getClosureScopeClass()) {
            return
                $this->colorizeName("{$class->getName()}::{$ref->getName()}(...)") . ' ' .
                $this->colorizeComment("#{$id}");
        }

        return
            $this->colorizeName("{$ref->getName()}(...)") . ' ' .
            $this->colorizeComment("#{$id}");
    }
}
