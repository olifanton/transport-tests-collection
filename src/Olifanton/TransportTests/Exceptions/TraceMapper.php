<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Exceptions;

final class TraceMapper
{
    public static function map(array $trace): array
    {
        $i = 0;

        return array_map(static function (array $row) use(&$i): string {
            return sprintf(
                "#%d %s(%d): %s",
                $i++,
                $row["file"],
                $row["line"],
                (isset($row["class"]) ? ($row["class"] . $row["type"] . $row["function"]) : $row["function"]) . "()",
            );
        }, $trace);
    }
}
