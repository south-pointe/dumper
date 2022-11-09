<?php declare(strict_types=1);

namespace Tests\SouthPointe\DataDump\Samples;

use RuntimeException;

class ContextualException extends RuntimeException
{
    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return [
            'data' => [
                1,
                2,
            ],
        ];
    }
}
