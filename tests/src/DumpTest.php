<?php declare(strict_types=1);

namespace Tests\SouthPointe\DataDump;

use DateTime;
use SouthPointe\DataDump\Decorators\AnsiDecorator;
use SouthPointe\DataDump\Dumper;
use SouthPointe\DataDump\Formatter;
use Tests\SouthPointe\DataDump\Samples\CircularClass;
use Tests\SouthPointe\DataDump\Samples\ContextualException;
use Tests\SouthPointe\DataDump\Samples\DebuggableClass;
use Tests\SouthPointe\DataDump\Samples\SimpleBackedEnum;
use Tests\SouthPointe\DataDump\Samples\SimpleClass;
use Tests\SouthPointe\DataDump\Samples\SimpleEnum;
use function assert;
use function fclose;
use function tmpfile;
use const INF;
use const NAN;
use const STDIN;

class DumpTest extends TestCase
{
    public function testSomething(): void
    {
        $circular = new CircularClass();
        $circular->next = new CircularClass();
        $circular->next->next = $circular;

        $ref = 'my ref';

        $closedResource = tmpfile();
        assert($closedResource !== false);
        fclose($closedResource);

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
            "text\r\n\t\"\x1B\0a3\v\sq\\na",
            "cut_me_cut_me_cut_me_cut_me_cut_me_cut_me_cut_me",
            STDIN,
            ['a' => 1, 'b' => 2, 3],
            $closedResource,
            new DateTime(),
            new ContextualException('test'),
            new SimpleClass(),
            new DebuggableClass(),
            static fn(string $str): string => 'abc' . $str,
            DateTime::createFromFormat(...),
            strstr(...),
            SimpleEnum::Option1,
            SimpleBackedEnum::Option2,
            $circular,
            &$ref,
        ];

        $decorator = new AnsiDecorator();
        $formatter = new Formatter($decorator);
        $vd = new Dumper($decorator, $formatter);
        $vd->dump($vars);

        $vd->dump("a\u{200E}\u{200A}\u{061C}\u{0012}\u{204A}b");
    }
}
