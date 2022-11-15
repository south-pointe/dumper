<?php declare(strict_types=1);

namespace Tests\SouthPointe\Dumper\Samples;

class CircularClass
{
    public self $next;
}
