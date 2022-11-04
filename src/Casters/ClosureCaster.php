<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Casters;

use Closure;
use ReflectionFunction;

class ClosureCaster extends Caster
{
    /**
     * @param Closure $var
     * @param int $id
     * @param int $depth
     * @param array<int, object> $objectRegistrar
     * @return string
     */
    public function cast(object $var, int $id, int $depth, array &$objectRegistrar): string
    {
        $ref = new ReflectionFunction($var);

        if ($file = $ref->getFileName()) {
            $startLine = $ref->getStartLine();
            $endLine = $ref->getEndLine();
            $range = ($startLine !== $endLine)
                ? "{$startLine}-{$endLine}"
                : $startLine;
            return
                $this->decorator->type($var::class . "@{$file}:{$range}") . ' ' .
                $this->decorator->comment("#{$id}");
        }

        if ($class = $ref->getClosureScopeClass()) {
            return
                $this->decorator->type("{$class->getName()}::{$ref->getName()}(...)") . ' ' .
                $this->decorator->comment("#{$id}");
        }

        return
            $this->decorator->type("{$ref->getName()}(...)") . ' ' .
            $this->decorator->comment("#{$id}");
    }
}
