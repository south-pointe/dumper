<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Casters;

use UnitEnum;

class EnumCaster extends Caster
{
    /**
     * @param UnitEnum $var
     * @param int $id
     * @param int $depth
     * @param array<int, object> $objectRegistrar
     * @return string
     */
    public function cast(object $var, int $id, int $depth, array &$objectRegistrar): string
    {
        return
            $this->decorator->type($var::class . "::{$var->name}") . ' ' .
            $this->decorator->comment("#{$id}");
    }
}
