<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LawyerContext\Infrastructure\Import\LegacyDirectoryImporter;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet\CabinetEntity;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\LawyerProfile\LawyerProfileEntity;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class LegacyDirectoryImporterTest extends WebTestCase
{
    private const LAWYERS_SOURCE = '11111111-1111-4111-8111-111111111111';
    private const CABINET_SOURCE = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        foreach (['DATABASE_URL' => 'sqlite:///:memory:', 'MYSQL_VERSION' => '8.0.40', 'SECURE_SCHEME' => 'https'] as $name => $value) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
        parent::setUpBeforeClass();
    }

    public function testDryRunReportsActionsWithoutWritingToDatabase(): void
    {
        [$entityManager, $importer, $paths] = $this->setUpImporter();

        $report = $importer->run($paths['lawyers'], $paths['cabinets'], $paths['quality'], false);

        self::assertSame(3, $report['cabinets']['create']);
        self::assertSame(1, $report['cabinets']['partial']);
        self::assertSame(3, $report['lawyers']['create']);
        self::assertSame(2, $report['relations']['resolved']);
        self::assertSame(1, $report['relations']['missing']);
        self::assertSame(1, $report['relations']['asymmetric']);
        self::assertSame(1, count($report['contacts']['invalidLawyerEmails']));
        self::assertSame(1, $report['contacts']['missingEmails']);
        self::assertSame(1, $report['contacts']['missingPhones']);
        self::assertSame(1, $report['portraitsDeferred']);
        self::assertSame(0, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM cabinet'));
        self::assertSame(0, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM lawyer_profile'));
        self::assertSame(0, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM user'));
    }

    public function testImportCreatesAutonomousProfilesAndIsIdempotent(): void
    {
        [$entityManager, $importer, $paths] = $this->setUpImporter();

        $first = $importer->run($paths['lawyers'], $paths['cabinets'], $paths['quality'], true);

        self::assertSame(3, $first['cabinets']['create']);
        self::assertSame(3, $first['lawyers']['create']);
        self::assertTrue($first['databaseWrite']);
        self::assertSame(3, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM cabinet WHERE legacy_source_uuid IS NOT NULL'));
        self::assertSame(3, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM lawyer_profile WHERE legacy_source_uuid IS NOT NULL'));
        self::assertSame(0, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM user'));

        $profile = $entityManager->getRepository(LawyerProfileEntity::class)->findOneBy(['legacySourceUuid' => self::LAWYERS_SOURCE]);
        self::assertInstanceOf(LawyerProfileEntity::class, $profile);
        self::assertNull($profile->getUser());
        self::assertSame('UNKNOWN', $profile->getProfessionalStatus());
        self::assertTrue($profile->isDirectoryVisible());
        self::assertSame('0102030405', $profile->getProfessionalPhone());
        self::assertSame('awa@example.test', $profile->getProfessionalEmail());
        self::assertSame(self::CABINET_SOURCE, $profile->getCabinet()?->getLegacySourceUuid()?->toRfc4122(), 'The profile is linked to the Cabinet imported earlier by its source UUID.');
        $publicUuid = $profile->getUuidAsString();

        $cabinet = $entityManager->getRepository(CabinetEntity::class)->findOneBy(['legacySourceUuid' => self::CABINET_SOURCE]);
        self::assertInstanceOf(CabinetEntity::class, $cabinet);
        self::assertSame(['+225 01 02 03', '07 08 09 10'], $cabinet->getPhones());
        self::assertSame('12 rue du Test', $cabinet->getAddress());
        self::assertNull($cabinet->getCity());
        self::assertSame('ACTIVE', $cabinet->getStatus());
        self::assertTrue($cabinet->isDirectoryVisible());

        $invalidEmailProfile = $entityManager->getRepository(LawyerProfileEntity::class)->findOneBy(['legacySourceUuid' => '33333333-3333-4333-8333-333333333333']);
        self::assertInstanceOf(LawyerProfileEntity::class, $invalidEmailProfile);
        self::assertNull($invalidEmailProfile->getProfessionalEmail());
        self::assertSame('UNKNOWN', $invalidEmailProfile->getProfessionalStatus());
        self::assertSame('email-invalide', $first['contacts']['invalidLawyerEmails'][0]['value']);

        $profileWithoutCabinet = $entityManager->getRepository(LawyerProfileEntity::class)->findOneBy(['legacySourceUuid' => '22222222-2222-4222-8222-222222222222']);
        self::assertInstanceOf(LawyerProfileEntity::class, $profileWithoutCabinet);
        self::assertNull($profileWithoutCabinet->getCabinet());

        $entityManager->clear();
        $profile = $entityManager->getRepository(LawyerProfileEntity::class)->findOneBy(['legacySourceUuid' => self::LAWYERS_SOURCE]);
        self::assertInstanceOf(LawyerProfileEntity::class, $profile);
        $profile->setProfessionalStatus('ACTIVE');
        $entityManager->flush();

        $second = $importer->run($paths['lawyers'], $paths['cabinets'], $paths['quality'], true);

        self::assertSame(0, $second['cabinets']['create']);
        self::assertSame(0, $second['cabinets']['update']);
        self::assertSame(3, $second['cabinets']['unchanged']);
        self::assertSame(0, $second['lawyers']['create']);
        self::assertSame(0, $second['lawyers']['update']);
        self::assertSame(3, $second['lawyers']['unchanged']);
        self::assertSame(3, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM cabinet WHERE legacy_source_uuid IS NOT NULL'));
        self::assertSame(3, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM lawyer_profile WHERE legacy_source_uuid IS NOT NULL'));

        $entityManager->clear();
        $profile = $entityManager->getRepository(LawyerProfileEntity::class)->findOneBy(['legacySourceUuid' => self::LAWYERS_SOURCE]);
        self::assertInstanceOf(LawyerProfileEntity::class, $profile);
        self::assertSame($publicUuid, $profile->getUuidAsString());
        self::assertSame('ACTIVE', $profile->getProfessionalStatus(), 'A later manual status verification is preserved by subsequent imports.');
    }

    public function testPotentialNameMatchIsReportedAndNotAutomaticallyMergedOrDuplicated(): void
    {
        [$entityManager, $importer, $paths] = $this->setUpImporter();
        $entityManager->persist((new LawyerProfileEntity())->setDisplayName('Maître Awa Test')->setBarNumber('CI-MANUAL-001'));
        $entityManager->flush();

        $report = $importer->run($paths['lawyers'], $paths['cabinets'], $paths['quality'], true);

        self::assertSame(1, $report['lawyers']['potentialMatch']);
        self::assertSame('POTENTIAL_MATCH', $report['lawyers']['items'][0]['action']);
        self::assertSame(['name'], $report['lawyers']['items'][0]['candidate']['fields']);
        self::assertSame(3, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM lawyer_profile'));
        self::assertSame(2, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM lawyer_profile WHERE legacy_source_uuid IS NOT NULL'));
    }

    public function testDuplicateLegacySourceUuidIsReportedAsConflictWithoutWriting(): void
    {
        [$entityManager, $importer, $paths] = $this->setUpImporter();
        $dataset = json_decode((string) file_get_contents($paths['lawyers']), true, 512, JSON_THROW_ON_ERROR);
        $dataset['entries'][1]['sourceUuid'] = $dataset['entries'][0]['sourceUuid'];
        $duplicatePath = tempnam(sys_get_temp_dir(), 'directory-duplicate-');
        self::assertNotFalse($duplicatePath);
        file_put_contents($duplicatePath, json_encode($dataset, JSON_THROW_ON_ERROR));

        try {
            $report = $importer->run($duplicatePath, $paths['cabinets'], $paths['quality'], true);
        } finally {
            unlink($duplicatePath);
        }

        self::assertSame(1, $report['lawyers']['failures']);
        self::assertSame('CONFLICT', $report['lawyers']['items'][0]['action']);
        self::assertSame(0, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM cabinet'));
        self::assertSame(0, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM lawyer_profile'));
    }

    public function testCompleteHistoricalDatasetDryRunMatchesAuditedCountsWithoutPersistence(): void
    {
        [$entityManager, $importer] = $this->setUpImporter();
        $directory = static::getContainer()->getParameter('kernel.project_dir') . '/src/LawyerContext/Infrastructure/Persistence/Doctrine/Fixtures/Data';

        $report = $importer->run(
            $directory . '/lawyers-directory.json',
            $directory . '/cabinets-directory.json',
            $directory . '/directory-quality-report.json',
            false,
        );

        self::assertSame(377, $report['cabinets']['create']);
        self::assertSame(7, $report['cabinets']['partial']);
        self::assertSame(605, $report['lawyers']['create']);
        self::assertSame(600, $report['relations']['resolved']);
        self::assertSame(5, $report['relations']['missing']);
        self::assertSame(10, $report['relations']['asymmetric']);
        self::assertSame(8, count($report['contacts']['invalidLawyerEmails']));
        self::assertSame(59, $report['contacts']['missingEmails']);
        self::assertSame(6, $report['contacts']['missingPhones']);
        self::assertSame(603, $report['portraitsDeferred']);
        self::assertSame(0, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM cabinet'));
        self::assertSame(0, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM lawyer_profile'));
    }

    /** @return array{EntityManagerInterface, LegacyDirectoryImporter, array{lawyers: string, cabinets: string, quality: string}} */
    private function setUpImporter(): array
    {
        self::ensureKernelShutdown();
        self::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($entityManager))->createSchema($entityManager->getMetadataFactory()->getAllMetadata());
        $projectDir = static::getContainer()->getParameter('kernel.project_dir');
        $directory = $projectDir . '/tests/Fixtures/LawyerContext/LegacyDirectoryImport';

        return [
            $entityManager,
            static::getContainer()->get(LegacyDirectoryImporter::class),
            ['lawyers' => $directory . '/lawyers.json', 'cabinets' => $directory . '/cabinets.json', 'quality' => $directory . '/quality-report.json'],
        ];
    }
}
