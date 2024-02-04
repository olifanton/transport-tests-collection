<?php declare(strict_types=1);

namespace Olifanton\TransportTests;

use Nette\PhpGenerator\Dumper;

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

    /**
     * @return array{deployment_wallet: array{secret_key: string, class: class-string, address: string}, env: class-string, runtime: class-string|null, toncenter_api_key: string, cases: array<string, class-string<TestCase>>}
     */
    public static function read(): array
    {
        $path = self::getPath();

        if (!self::isCreated()) {
            throw new \RuntimeException(sprintf("Configuration file %s not found", $path));
        }

        return include $path;
    }

    public static function save(array $config): void
    {
        $dumper = new Dumper();
        $dumper->indentation = str_repeat(" ", 4);
        file_put_contents(self::getPath(), "<?php return " . $dumper->dump($config) . ";" . PHP_EOL);
    }
}
