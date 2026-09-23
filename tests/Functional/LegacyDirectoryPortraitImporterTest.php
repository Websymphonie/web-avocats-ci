<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Uid\Uuid;
use Websymphonie\LawyerContext\Infrastructure\Import\LegacyDirectoryPortraitImporter;
use Websymphonie\LawyerContext\Infrastructure\Import\LegacyPortraitDownloader;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\LawyerProfile\LawyerProfileEntity;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Domain\Exception\MediaInUseException;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class LegacyDirectoryPortraitImporterTest extends WebTestCase
{
    private const WITH_PORTRAIT = '11111111-1111-4111-8111-111111111111';
    private const MISSING_PROFILE = '22222222-2222-4222-8222-222222222222';
    private const NO_PORTRAIT = '33333333-3333-4333-8333-333333333333';
    private const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC';

    private static string $storageDirectory;
    private static ?string $previousDatabaseUrl = null;
    private static ?string $previousStorageDirectory = null;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        self::$previousDatabaseUrl = getenv('DATABASE_URL') === false ? null : (string) getenv('DATABASE_URL');
        self::$previousStorageDirectory = getenv('APP_STORAGE_DIR') === false ? null : (string) getenv('APP_STORAGE_DIR');
        self::$storageDirectory = sys_get_temp_dir() . '/legacy-directory-portrait-storage-' . bin2hex(random_bytes(6));
        foreach (['DATABASE_URL' => 'sqlite:///:memory:', 'MYSQL_VERSION' => '8.0.40', 'SECURE_SCHEME' => 'https', 'APP_STORAGE_DIR' => self::$storageDirectory] as $name => $value) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        $filesystem = new Filesystem();
        if (isset(self::$storageDirectory)) {
            $filesystem->remove(self::$storageDirectory);
        }
        self::restoreEnvironment('DATABASE_URL', self::$previousDatabaseUrl);
        self::restoreEnvironment('APP_STORAGE_DIR', self::$previousStorageDirectory);
    }

    public function testDryRunAndTwoWritesReuseOneMediaAndPreserveSourceAndUsageProtection(): void
    {
        [$entityManager, $workspace, $importer, $requestCount] = $this->setUpImporter();

        $dryRun = $importer->run($workspace . '/lawyers.json', $workspace . '/portraits', $workspace . '/manifest.json', false);
        self::assertSame(2, $dryRun['sourcePortraits']);
        self::assertSame(1, $dryRun['profilesWithoutPortraitSource']);
        self::assertSame(1, $dryRun['profilesMissing']);
        self::assertSame(1, $dryRun['downloadPlanned']);
        self::assertSame(1, $dryRun['mediaPlanned']);
        self::assertSame(0, $requestCount());
        self::assertSame(0, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM media'));

        $first = $importer->run($workspace . '/lawyers.json', $workspace . '/portraits', $workspace . '/manifest.json', true);
        self::assertSame(1, $first['downloadAttempted']);
        self::assertSame(1, $first['downloadSucceeded']);
        self::assertSame(1, $first['mediaCreated']);
        self::assertSame(1, $first['profilesAssociated']);
        self::assertSame(1, $first['profilesMissing']);
        self::assertSame(1, $requestCount());
        self::assertFileExists($workspace . '/portraits/' . self::WITH_PORTRAIT . '.png');
        self::assertSame(base64_decode(self::PNG, true), file_get_contents($workspace . '/portraits/' . self::WITH_PORTRAIT . '.png'));

        $entityManager->clear();
        $profile = $entityManager->getRepository(LawyerProfileEntity::class)->findOneBy(['legacySourceUuid' => Uuid::fromString(self::WITH_PORTRAIT)]);
        self::assertInstanceOf(LawyerProfileEntity::class, $profile);
        self::assertNull($profile->getUser());
        self::assertNotNull($profile->getPortraitMediaId());
        $media = static::getContainer()->get(MediaRepositoryInterface::class)->getById($profile->getPortraitMediaId());
        self::assertSame('institution/lawyers/' . $media->storageName, $media->storagePath);
        self::assertSame(1, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM media'));

        try {
            static::getContainer()->get(MediaUploadServiceInterface::class)->delete($media);
            self::fail('Un portrait référencé doit rester protégé contre la suppression.');
        } catch (MediaInUseException) {
            self::assertSame(1, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM media'));
        }

        $second = $importer->run($workspace . '/lawyers.json', $workspace . '/portraits', $workspace . '/manifest.json', true);
        self::assertSame(0, $second['downloadAttempted']);
        self::assertSame(0, $second['mediaCreated']);
        self::assertSame(1, $second['mediaUnchanged']);
        self::assertSame(1, $second['profilesAlreadyAssociated']);
        self::assertSame(1, $requestCount());
        self::assertSame(1, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM media'));
    }

    public function testManualPortraitIsReportedAsConflictAndIsNotOverwritten(): void
    {
        [$entityManager, $workspace, $importer, $requestCount] = $this->setUpImporter();
        $profile = $entityManager->getRepository(LawyerProfileEntity::class)->findOneBy(['legacySourceUuid' => Uuid::fromString(self::WITH_PORTRAIT)]);
        self::assertInstanceOf(LawyerProfileEntity::class, $profile);
        $source = $workspace . '/manual.png';
        file_put_contents($source, base64_decode(self::PNG, true));
        $media = static::getContainer()->get(MediaUploadServiceInterface::class)->upload(new UploadedFile($source, 'manual.png', 'image/png', null, true), 'institution/lawyers');
        $profile->setPortraitMediaId($media->id);
        $entityManager->flush();

        $report = $importer->run($workspace . '/lawyers.json', $workspace . '/portraits', $workspace . '/manifest.json', true);

        self::assertSame(1, $report['mediaConflicts']);
        self::assertSame('association', $report['errors'][0]['stage']);
        self::assertSame(0, $requestCount());
        self::assertSame(1, (int) $entityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM media'));
        self::assertSame($media->id, $profile->getPortraitMediaId());
    }

    /** @return array{EntityManagerInterface, string, LegacyDirectoryPortraitImporter, \Closure(): int} */
    private function setUpImporter(): array
    {
        self::ensureKernelShutdown();
        self::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($entityManager))->createSchema($entityManager->getMetadataFactory()->getAllMetadata());
        $workspace = sys_get_temp_dir() . '/legacy-directory-portrait-test-' . bin2hex(random_bytes(6));
        mkdir($workspace, 0755, true);
        foreach ([
            self::WITH_PORTRAIT => 'Portrait Test',
            self::NO_PORTRAIT => 'Sans Portrait Test',
        ] as $sourceUuid => $name) {
            $profile = (new LawyerProfileEntity())
                ->setLegacySourceUuid(Uuid::fromString($sourceUuid))
                ->setDisplayName($name)
                ->setDirectoryVisible(true)
                ->setProfessionalStatus('UNKNOWN');
            $entityManager->persist($profile);
        }
        $entityManager->flush();
        $entries = [
            ['sourceUuid' => self::WITH_PORTRAIT, 'displayName' => 'Portrait Test', 'portraitSourceUrl' => 'https://app.ordredesavocats-ci.net/storage/Profile/test.png'],
            ['sourceUuid' => self::MISSING_PROFILE, 'displayName' => 'Profil absent', 'portraitSourceUrl' => 'https://app.ordredesavocats-ci.net/storage/Profile/missing.png'],
            ['sourceUuid' => self::NO_PORTRAIT, 'displayName' => 'Sans Portrait Test', 'portraitSourceUrl' => null],
        ];
        file_put_contents($workspace . '/lawyers.json', json_encode(['entries' => $entries], JSON_THROW_ON_ERROR));
        $requestCount = 0;
        $client = new MockHttpClient(static function () use (&$requestCount): MockResponse {
            $requestCount++;
            $bytes = base64_decode(self::PNG, true);

            return new MockResponse($bytes, [
                'http_code' => 200,
                'response_headers' => ['content-type' => 'image/png', 'content-length' => (string) strlen((string) $bytes)],
            ]);
        });
        $importer = new LegacyDirectoryPortraitImporter(
            $entityManager,
            static::getContainer()->get(MediaUploadServiceInterface::class),
            static::getContainer()->get(MediaRepositoryInterface::class),
            new LegacyPortraitDownloader($client, (int) static::getContainer()->getParameter('media.gallery.max_size')),
            new Filesystem(),
            (int) static::getContainer()->getParameter('media.gallery.max_size'),
        );

        return [$entityManager, $workspace, $importer, static function () use (&$requestCount): int { return $requestCount; }];
    }

    private static function restoreEnvironment(string $name, ?string $value): void
    {
        if ($value === null) {
            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);
        } else {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}
