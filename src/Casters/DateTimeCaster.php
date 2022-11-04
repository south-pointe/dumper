<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Casters;

use DateTime;

class DateTimeCaster extends Caster
{
    /**
     * @param DateTime $var
     * @param int $id
     * @param int $depth
     * @param array<int, object> $objectRegistrar
     * @return string
     */
    public function cast(object $var, int $id, int $depth, array &$objectRegistrar): string
    {
        return
            $this->decorator->type($var::class) . ' ' .
            $this->decorator->comment("#$id") . ' ' .
            $this->decorator->scalar($var->format('Y-m-d H:i:s.u T (P)'));
    }
}
