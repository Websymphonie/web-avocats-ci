<?php

declare(strict_types=1);

namespace Websymphonie\LawyerContext\Presenter\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Websymphonie\LawyerContext\Infrastructure\Import\LegacyDirectoryImporter;

#[AsCommand(
    name: 'app:data:import-legacy-directory',
    description: 'Prévisualise ou importe explicitement le dataset local de l’annuaire historique.',
)]
final class ImportLegacyDirectoryConsole extends Command
{
    public function __construct(
        private readonly LegacyDirectoryImporter $importer,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        #[Autowire('%kernel.environment%')]
        private readonly string $environment,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $dataDirectory = $this->projectDir . '/src/LawyerContext/Infrastructure/Persistence/Doctrine/Fixtures/Data';
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Calculer les actions sans écrire en base (mode par défaut).')
            ->addOption('write', null, InputOption::VALUE_NONE, 'Appliquer les créations/mises à jour dans la base configurée.')
            ->addOption('lawyers', null, InputOption::VALUE_REQUIRED, 'Chemin local du JSON avocats.', $dataDirectory . '/lawyers-directory.json')
            ->addOption('cabinets', null, InputOption::VALUE_REQUIRED, 'Chemin local du JSON Cabinets.', $dataDirectory . '/cabinets-directory.json')
            ->addOption('quality-report', null, InputOption::VALUE_REQUIRED, 'Chemin local du rapport qualité.', $dataDirectory . '/directory-quality-report.json')
            ->addOption('report', null, InputOption::VALUE_REQUIRED, 'Chemin de sortie du rapport JSON (par défaut : var/reports avec horodatage).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $write = $input->getOption('write') === true;
        if ($write && $input->getOption('dry-run') === true) {
            $io->error('Choisissez soit --dry-run, soit --write, pas les deux.');

            return Command::INVALID;
        }
        if ($write && $this->environment === 'prod') {
            $io->error('Cet import local est désactivé en environnement prod.');

            return Command::FAILURE;
        }

        $lawyersPath = $input->getOption('lawyers');
        $cabinetsPath = $input->getOption('cabinets');
        $qualityPath = $input->getOption('quality-report');
        if (!is_string($lawyersPath) || !is_string($cabinetsPath) || !is_string($qualityPath)) {
            $io->error('Les trois chemins des sources JSON doivent être des chaînes.');

            return Command::INVALID;
        }

        try {
            $reportPath = $this->resolveReportPath($input->getOption('report'));
        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        try {
            $report = $this->importer->run($lawyersPath, $cabinetsPath, $qualityPath, $write);
            $this->writeReport($reportPath, $report);
        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->title($write ? 'Import de l’annuaire historique' : 'Prévisualisation de l’annuaire historique');
        $io->table(['Entité', 'Créés', 'Mis à jour', 'Inchangés', 'Partiels / conflits', 'Avertissements', 'Échecs'], [
            ['Cabinets', $report['cabinets']['create'], $report['cabinets']['update'], $report['cabinets']['unchanged'], $report['cabinets']['partial'], $report['cabinets']['warnings'], $report['cabinets']['failures']],
            ['Avocats', $report['lawyers']['create'], $report['lawyers']['update'], $report['lawyers']['unchanged'], $report['lawyers']['potentialMatch'], $report['lawyers']['warnings'], $report['lawyers']['failures']],
        ]);
        $io->definitionList(
            ['Relations résolues' => $report['relations']['resolved']],
            ['Relations sans Cabinet' => $report['relations']['missing']],
            ['Relations asymétriques' => $report['relations']['asymmetric']],
            ['Emails avocats invalides' => count($report['contacts']['invalidLawyerEmails'])],
            ['Emails Cabinets invalides' => count($report['contacts']['invalidCabinetEmails'])],
            ['Emails absents' => $report['contacts']['missingEmails']],
            ['Téléphones absents' => $report['contacts']['missingPhones']],
            ['Portraits reportés' => $report['portraitsDeferred']],
            ['Rapport JSON' => $reportPath],
        );
        if (!$write) {
            $io->note('Mode dry-run : aucune écriture en base. Ajouter --write uniquement après vérification de la base cible isolée.');
        }

        return $report['cabinets']['failures'] + $report['lawyers']['failures'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function resolveReportPath(mixed $requestedPath): string
    {
        $path = is_string($requestedPath) && trim($requestedPath) !== ''
            ? $requestedPath
            : sprintf('%s/var/reports/legacy-directory-import-%s.json', $this->projectDir, (new \DateTimeImmutable())->format('Ymd-His'));
        if (file_exists($path)) {
            throw new \RuntimeException(sprintf('Le rapport existe déjà ; choisissez un nouveau chemin : %s', $path));
        }
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Impossible de créer le dossier du rapport : %s', $directory));
        }

        return $path;
    }

    /** @param array<string, mixed> $report */
    private function writeReport(string $path, array $report): void
    {
        $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if (file_put_contents($path, $json . PHP_EOL, LOCK_EX) === false) {
            throw new \RuntimeException(sprintf('Impossible d’écrire le rapport JSON : %s', $path));
        }
    }
}
