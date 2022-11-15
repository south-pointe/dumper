<?php declare(strict_types=1);

namespace SouthPointe\Dumper;

use function assert;
use function fopen;
use function fwrite;

class Writer
{
    /**
     * @param resource $resource
     */
    public function __construct(
        protected mixed $resource = null,
    )
    {
        if ($resource === null) {
            $stdout = fopen('php://stdout', 'w');
            assert($stdout !== false);
            $this->resource = $stdout;
        }
    }

    public function write(string $data): void
    {
        fwrite($this->resource, $data);
    }
}
