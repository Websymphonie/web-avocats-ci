<?php
declare(strict_types=1);

namespace Websymphonie\IdentityContext\Presenter\Console;

use DomainException;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Websymphonie\IdentityContext\Application\Usecase\Command\User\AddUserCommand;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandBus;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creer un utilisateur avec le rôle super admin',
)]
class CreateAdminConsole extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly CommandBus $commandBus,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Email')
            ->addArgument('name', InputArgument::OPTIONAL, 'Nom & Prénoms');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if (
            null !== $input->getArgument('email') &&
            null !== $input->getArgument('name')
        ) {
            return;
        }

        $this->io->text("Processus d'ajout d'un nouvel super admin");
        $this->askArgument($input, 'email');
        $this->askArgument($input, 'name');
        $this->io->text('Un lien d’activation sera envoyé à cette adresse.');
    }

    private function askArgument(InputInterface $input, string $name, bool $hidden = false): void
    {
        $value = strval($input->getArgument($name));

        if ('' !== $value) {
            $this->io->text((sprintf('> <info>%s</info>: %s', $name, $value)));
        } else {
            $value = match ($hidden) {
                false => $this->io->ask($name),
                default => $this->io->askHidden($name)
            };

            $input->setArgument($name, $value);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $command = new AddUserCommand(
            name: strval($input->getArgument('name')),
            email: strval($input->getArgument('email')),
            roles: ['ROLE_SUPER_ADMIN'],
            enabled: false,
            sendMail: true,
        );
        try {
            $this->commandBus->handle($command);
            $this->io->success("Super administrateur créé. Un lien d’activation a été envoyé.");
            return Command::SUCCESS;
        } catch (InvalidArgumentException|DomainException $e) {
            $this->io->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
