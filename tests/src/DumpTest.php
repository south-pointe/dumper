<?php declare(strict_types=1);

namespace Tests\SouthPointe\DataDump;

use DateTime;
use SouthPointe\DataDump\Decorators\AnsiDecorator;
use SouthPointe\DataDump\Dumper;
use SouthPointe\DataDump\Formatter;
use Tests\SouthPointe\DataDump\Samples\CircularClass;
use Tests\SouthPointe\DataDump\Samples\SimpleBackedEnum;
use Tests\SouthPointe\DataDump\Samples\SimpleClass;
use Tests\SouthPointe\DataDump\Samples\SimpleEnum;
use const INF;
use const NAN;
use const STDIN;

class DumpTest extends TestCase
{
    public function testSomething(): void
    {
        $ref = new CircularClass();
        $ref->ref = new CircularClass();
        $ref->ref->ref = $ref;

        $vars = [
            null,
            -1,
            -0.0,
            1,
            1.1,
            true,
            false,
            NAN,
            INF,
            -INF,
            "text",
            "あいう",
            STDIN,
            new DateTime(),
//            new Exception(),
            new SimpleClass(),
            static fn(string $str): string => 'abc' . $str,
            DateTime::createFromFormat(...),
            strstr(...),
            SimpleEnum::Option1,
            SimpleBackedEnum::Option2,
            $ref,
        ];

        $decorator = new AnsiDecorator();
        $formatter = new Formatter($decorator);
        $vd = new Dumper($decorator, $formatter);
        $vd->dump($vars);
    }
}
