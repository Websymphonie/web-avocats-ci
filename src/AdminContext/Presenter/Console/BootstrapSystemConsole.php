<?php

declare(strict_types=1);

namespace Websymphonie\AdminContext\Presenter\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Websymphonie\AdminContext\Infrastructure\Bootstrap\SystemBootstrapper;

#[AsCommand(name: 'app:data:bootstrap-system', description: 'Installe les réglages et assets système indispensables.')]
final class BootstrapSystemConsole extends Command
{
    public function __construct(private readonly SystemBootstrapper $bootstrapper)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $created = $this->bootstrapper->bootstrap();
        $output->writeln(sprintf('Bootstrap système terminé : %d réglage(s), %d entrée(s) image créée(s).', $created['settings'], $created['images']));

        return Command::SUCCESS;
    }
}
