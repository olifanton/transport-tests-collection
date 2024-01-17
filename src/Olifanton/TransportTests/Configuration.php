<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

final class Configuration
{
    public const CONFIGURATION_FILE = "transport-tests.conf.php";

    public static function isCreated(): bool
    {
        return file_exists(self::getPath());
    }

    public static function getPath(): string
    {
        return getcwd() . DIRECTORY_SEPARATOR . self::CONFIGURATION_FILE;
    }

    public static function read(): array
    {
        $path = self::getPath();

        if (!self::isCreated()) {
            throw new \RuntimeException(sprintf("Configuration file %s not found", $path));
        }

        return include $path;
    }
}
