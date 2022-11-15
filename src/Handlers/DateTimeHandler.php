<?php declare(strict_types=1);

namespace SouthPointe\Dumper\Handlers;

use DateTime;

class DateTimeHandler extends ClassHandler
{
    /**
     * @param DateTime $var
     * @inheritDoc
     */
    public function handle(object $var, int $id, int $depth, array $objectIds): string
    {
        return
            $this->colorizeName($var::class) . ' ' .
            $this->colorizeComment("#$id") . ' ' .
            $this->colorizeScalar($var->format($this->config->dateTimeFormat));
    }
}
