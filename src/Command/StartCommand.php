<?php

declare(strict_types=1);

namespace Yii3Swoole\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Yii3Swoole\Server;

final class StartCommand extends Command
{
    protected static $defaultName = 'swoole/start';
    protected static $defaultDescription = 'Starts the Swoole web server';

    public function __construct(private Server $server)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'Host to listen on')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port to listen on');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $host = $input->getOption('host');
        $port = $input->getOption('port');

        $server = $this->server;

        if ($host !== null) {
            $server = $server->withHost((string) $host);
        }

        if ($port !== null) {
            $server = $server->withPort((int) $port);
        }

        $output->writeln('<info>Starting Swoole server...</info>');

        // We can't easily print the actual host/port here if we don't expose getters on Server, 
        // but we know what we passed.

        $server->run();

        return Command::SUCCESS;
    }
}
