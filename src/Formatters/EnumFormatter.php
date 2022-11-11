<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Formatters;

use UnitEnum;

class EnumFormatter extends ClassFormatter
{
    /**
     * @param UnitEnum $var
     * @inheritDoc
     */
    public function format(object $var, int $id, int $depth, array $objectIds): string
    {
        $deco = $this->decorator;

        return
            $deco->classType($var::class . "::{$var->name}") . ' ' .
            $deco->comment("#{$id}");
    }
}
