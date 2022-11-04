<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Casters;

use UnitEnum;

class EnumCaster extends Caster
{
    /**
     * @param UnitEnum $var
     * @inheritDoc
     */
    public function cast(object $var, int $id, int $depth, array $objectIds): string
    {
        $deco = $this->decorator;

        return
            $deco->type($var::class . "::{$var->name}") . ' ' .
            $deco->comment("#{$id}");
    }
}
