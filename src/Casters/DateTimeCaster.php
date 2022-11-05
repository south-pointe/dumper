<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Casters;

use DateTime;

class DateTimeCaster extends Caster
{
    /**
     * @param DateTime $var
     * @inheritDoc
     */
    public function cast(object $var, int $id, int $depth, array $objectIds): string
    {
        $deco = $this->decorator;

        return
            $deco->classType($var::class) . ' ' .
            $deco->comment("#$id") . ' ' .
            $deco->scalar($var->format('Y-m-d H:i:s.u T (P)'));
    }
}
