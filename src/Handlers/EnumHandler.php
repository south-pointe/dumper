<?php declare(strict_types=1);

namespace SouthPointe\Dumper\Handlers;

use UnitEnum;

class EnumHandler extends ClassHandler
{
    /**
     * @param UnitEnum $var
     * @inheritDoc
     */
    public function handle(object $var, int $id, int $depth, array $objectIds): string
    {
        return
            $this->colorizeName($var::class . "::{$var->name}") . ' ' .
            $this->colorizeComment("#{$id}");
    }
}
