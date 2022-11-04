<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Casters;

use Closure;
use ReflectionFunction;
use function assert;

class ClosureCaster extends Caster
{
    /**
     * @param Closure $var
     * @inheritDoc
     */
    public function cast(object $var, int $id, int $depth, array $objectIds): string
    {
        assert($var instanceof Closure);

        $deco = $this->decorator;

        $ref = new ReflectionFunction($var);

        if ($file = $ref->getFileName()) {
            $startLine = $ref->getStartLine();
            $endLine = $ref->getEndLine();
            $range = ($startLine !== $endLine)
                ? "{$startLine}-{$endLine}"
                : $startLine;
            return
                $deco->type($var::class . "@{$file}:{$range}") . ' ' .
                $deco->comment("#{$id}");
        }

        if ($class = $ref->getClosureScopeClass()) {
            return
                $deco->type("{$class->getName()}::{$ref->getName()}(...)") . ' ' .
                $deco->comment("#{$id}");
        }

        return
            $deco->type("{$ref->getName()}(...)") . ' ' .
            $deco->comment("#{$id}");
    }
}
