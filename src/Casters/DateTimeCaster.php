<?php declare(strict_types=1);

namespace SouthPointe\DataDump\Casters;

use DateTime;

class DateTimeCaster extends Caster
{
    /**
     * @param DateTime $var
     * @param int $id
     * @param int $depth
     * @return string
     */
    public function cast(object $var, int $id, int $depth): string
    {
        return
            $this->decorator->type($var::class) . ' DateTimeCaster.php' .
            $this->decorator->scalar($var->format('Y-m-d H:i:s.u T (P)')) . ' ' .
            $this->decorator->comment("#$id");
    }
}
