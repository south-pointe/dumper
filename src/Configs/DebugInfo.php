<?php declare(strict_types=1);

namespace SouthPointe\Dumper\Configs;

enum DebugInfo
{
    case Ignore;
    case Overwrite;
    case Append;
}
