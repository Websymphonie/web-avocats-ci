<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Presenter\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Websymphonie\ContentContext\Infrastructure\Bootstrap\InstitutionalContentBootstrapper;

#[AsCommand(name: 'app:data:bootstrap-institutional-content', description: 'Installe les contenus institutionnels sans fixtures de démonstration.')]
final class BootstrapInstitutionalContentConsole extends Command
{
    public function __construct(private readonly InstitutionalContentBootstrapper $bootstrapper)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $created = $this->bootstrapper->bootstrap();
        $output->writeln(sprintf(
            'Bootstrap institutionnel terminé : %d page(s), %d Bâtonnier(s), %d membre(s) du Conseil et %d document(s) créé(s).',
            $created['pages'],
            $created['batonnier'],
            $created['councilMembers'],
            $created['documents'],
        ));

        if ($created['documentConflicts'] !== []) {
            $output->writeln('<error>Conflits documentaires (non modifiés) : ' . implode(', ', $created['documentConflicts']) . '</error>');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
