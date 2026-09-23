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
use Websymphonie\LawyerContext\Infrastructure\Import\LegacyDirectoryPortraitImporter;

#[AsCommand(
    name: 'app:data:import-legacy-directory-portraits',
    description: 'Prévisualise ou importe explicitement les portraits du dataset local de l’annuaire historique.',
)]
final class ImportLegacyDirectoryPortraitsConsole extends Command
{
    public function __construct(
        private readonly LegacyDirectoryPortraitImporter $importer,
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
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Analyser les profils et assets sans réseau ni écriture (mode par défaut).')
            ->addOption('write', null, InputOption::VALUE_NONE, 'Récupérer les assets manquants, créer les Media et associer les portraits.')
            ->addOption('dataset', null, InputOption::VALUE_REQUIRED, 'Chemin local de lawyers-directory.json.', $dataDirectory . '/lawyers-directory.json')
            ->addOption('assets-dir', null, InputOption::VALUE_REQUIRED, 'Répertoire des originaux locaux nommés par UUID source.', $dataDirectory . '/portraits')
            ->addOption('manifest', null, InputOption::VALUE_REQUIRED, 'Manifeste de provenance UUID source → Media.', $dataDirectory . '/portrait-manifest.json')
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
            $io->error('Cet import de portraits est désactivé en environnement prod.');

            return Command::FAILURE;
        }

        $dataset = $input->getOption('dataset');
        $assetsDirectory = $input->getOption('assets-dir');
        $manifestPath = $input->getOption('manifest');
        if (!is_string($dataset) || !is_string($assetsDirectory) || !is_string($manifestPath)) {
            $io->error('Les chemins du dataset, des assets et du manifeste doivent être des chaînes.');

            return Command::INVALID;
        }

        try {
            $reportPath = $this->resolveReportPath($input->getOption('report'));
            $report = $this->importer->run($dataset, $assetsDirectory, $manifestPath, $write);
            $this->writeReport($reportPath, $report);
        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->title($write ? 'Import des portraits historiques' : 'Prévisualisation des portraits historiques');
        $io->table(['Élément', 'Nombre'], [
            ['Portraits source', $report['sourcePortraits']],
            ['Sans portrait source', $report['profilesWithoutPortraitSource']],
            ['Téléchargements prévus', $report['downloadPlanned']],
            ['Téléchargements tentés / réussis / échoués', sprintf('%d / %d / %d', $report['downloadAttempted'], $report['downloadSucceeded'], $report['downloadFailed'])],
            ['Images invalides', $report['invalidImage']],
            ['Media créés / inchangés / conflits', sprintf('%d / %d / %d', $report['mediaCreated'], $report['mediaUnchanged'], $report['mediaConflicts'])],
            ['Profils associés / déjà associés / absents', sprintf('%d / %d / %d', $report['profilesAssociated'], $report['profilesAlreadyAssociated'], $report['profilesMissing'])],
            ['Erreurs détaillées', count($report['errors'])],
            ['Rapport JSON', $reportPath],
        ]);
        if (!$write) {
            $io->note('Mode dry-run : aucun téléchargement, média ou changement de profil. La validation écriture doit viser une base isolée.');
        }

        return $report['downloadFailed'] + $report['invalidImage'] + $report['mediaConflicts'] + $report['profilesMissing'] > 0
            ? Command::FAILURE
            : Command::SUCCESS;
    }

    private function resolveReportPath(mixed $requestedPath): string
    {
        $path = is_string($requestedPath) && trim($requestedPath) !== ''
            ? $requestedPath
            : sprintf('%s/var/reports/legacy-directory-portraits-%s.json', $this->projectDir, (new \DateTimeImmutable())->format('Ymd-His'));
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
