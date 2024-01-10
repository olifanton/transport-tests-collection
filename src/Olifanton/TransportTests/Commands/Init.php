<?php declare(strict_types=1);

namespace Olifanton\TransportTests\Commands;

use danog\ClassFinder\ClassFinder;
use Nette\PhpGenerator\Dumper;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3Options;
use Olifanton\Ton\Contracts\Wallets\V3\WalletV3R2;
use Olifanton\TransportTests\AsCase;
use Olifanton\TransportTests\Constants;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand("init")]
class Init extends Command
{
    protected function configure()
    {
        $this->setDescription("Toolchain initialization");
    }

    /**
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');
        $tcApiKeyQuestion = new Question("Enter API key for https://testnet.toncenter.com/: ", "");

        $toncenterApiKey = trim($helper->ask($input, $output, $tcApiKeyQuestion));

        if (empty($toncenterApiKey)) {
            $io
                ->caution(
                    "You have provided an empty Toncenter (testnet) API key. Get own key in Telegram bot https://t.me/tontestnetapibot and put key later in configuration file " . Constants::CONFIGURATION_FILE
                );
        }

        $kp = \Olifanton\Ton\Helpers\KeyPair::random();
        $deploymentWalletAddress = (new WalletV3R2(new WalletV3Options(publicKey: $kp->publicKey)))
            ->getAddress()
            ->toString(
                isUserFriendly: true,
                isUrlSafe: true,
                isBounceable: false,
                isTestOnly: false,
            );

        $workdir = getcwd();
        $outfile = $workdir . DIRECTORY_SEPARATOR . Constants::CONFIGURATION_FILE;
        $classes = ClassFinder::getClassesInNamespace("Olifanton\\TransportTests", ClassFinder::RECURSIVE_MODE);

        if (file_exists($outfile)) {
            $io->error("Configuration file " . $outfile . " exists");

            return self::FAILURE;
        }

        $cases = [];

        foreach ($classes as $class) {
            if ($caseAttribs = (new \ReflectionClass($class))->getAttributes(AsCase::class)) {
                /** @var AsCase $caseConfig */
                $caseConfig = $caseAttribs[0]->newInstance();
                $cases[$caseConfig->alias] = $class;
            }
        }

        $result = [
            "deployment_wallet" => [
                "secret_key" => Bytes::bytesToBase64($kp->secretKey),
                "address" => $deploymentWalletAddress,
            ],
            "toncenter_api_key" => $toncenterApiKey,
            "cases" => $cases,
        ];
        $dumper = new Dumper();
        $dumper->indentation = str_repeat(" ", 4);
        file_put_contents($outfile, "<?php return " . $dumper->dump($result) . ";" . PHP_EOL);

        $io->success("Done!");
        $io->block(
            [
                "Your deployment wallet address: " . $deploymentWalletAddress,
                "/!\ Make sure you get test Toncoin's for your Deployment wallet in the Telegram bot https://t.me/testgiver_ton_bot /!\\",
            ],
            'NOTE',
            'fg=yellow;options=bold',
            ' ! '
        );
        $io->info([
            "Configuration file path: " . $outfile,
            "Don't forget to add the configuration file to version control system ignore list (.gitignore)",
        ]);

        return self::SUCCESS;
    }
}