<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Casters;

use UnitEnum;

class EnumCaster extends Caster
{
    /**
     * @param UnitEnum $var
     * @param int $id
     * @param int $depth
     * @return string
     */
    public function cast(object $var, int $id, int $depth): string
    {
        return
            $this->decorator->type($var::class . "::{$var->name}") . ' EnumCaster.php' .
            $this->decorator->comment("#{$id}");
    }
}
