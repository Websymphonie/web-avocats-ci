<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\DocumentPublication\DocumentPublicationEntity;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\StoredFileEntity;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class ContentSecurityTest extends WebTestCase
{
    private static string $storageDirectory;
    private static string|false $previousStorageDirectory = false;
    private static int $userSequence = 0;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        self::$previousStorageDirectory = getenv('APP_STORAGE_DIR');
        self::$storageDirectory = sys_get_temp_dir() . '/avocat-content-security-' . bin2hex(random_bytes(5));

        foreach ([
            'DATABASE_URL' => 'sqlite:///:memory:',
            'MYSQL_VERSION' => '8.0.40',
            'SECURE_SCHEME' => 'https',
            'APP_STORAGE_DIR' => self::$storageDirectory,
        ] as $name => $value) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        (new Filesystem())->remove(self::$storageDirectory);

        if (self::$previousStorageDirectory === false) {
            putenv('APP_STORAGE_DIR');
            unset($_ENV['APP_STORAGE_DIR'], $_SERVER['APP_STORAGE_DIR']);
        } else {
            putenv('APP_STORAGE_DIR=' . self::$previousStorageDirectory);
            $_ENV['APP_STORAGE_DIR'] = self::$previousStorageDirectory;
            $_SERVER['APP_STORAGE_DIR'] = self::$previousStorageDirectory;
        }

        parent::tearDownAfterClass();
    }

    public function testAdminCanAccessRepresentativeContentBackofficeListings(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);

        foreach (['news', 'events', 'galleries', 'documents'] as $section) {
            $client->request('GET', '/admin/content/' . $section, server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(Response::HTTP_OK, $section);
        }
    }

    /** @dataProvider deniedBackofficeRoles */
    public function testRoleIsDeniedFromRepresentativeContentBackofficeListings(string $role): void
    {
        $client = $this->authenticatedClient([$role]);

        foreach (['news', 'events', 'galleries', 'documents'] as $section) {
            $client->request('GET', '/admin/content/' . $section, server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, $role . ' / ' . $section);
        }
    }

    public function testAnonymousVisitorsAreRedirectedFromRepresentativeContentBackofficeListings(): void
    {
        $client = $this->clientWithSchema();

        foreach (['news', 'events', 'galleries', 'documents'] as $section) {
            $client->request('GET', '/admin/content/' . $section, server: ['HTTPS' => 'on']);
            self::assertResponseRedirects('/auth/login', Response::HTTP_FOUND, $section);
        }
    }

    public function testPublicPublishedDocumentIsDownloadableAnonymouslyWithSafeHeaders(): void
    {
        $client = $this->clientWithSchema();
        $uuid = $this->createDocumentFixture(DocumentAccessLevel::PUBLIC, DocumentStatus::PUBLISHED);

        $client->request('GET', '/documents/' . $uuid . '/download', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame('application/pdf', $client->getResponse()->headers->get('Content-Type'));
        self::assertStringContainsString('attachment', (string) $client->getResponse()->headers->get('Content-Disposition'));
        self::assertStringContainsString('guide.pdf', (string) $client->getResponse()->headers->get('Content-Disposition'));
    }

    public function testPublishedMemberDocumentRequiresAuthentication(): void
    {
        $anonymousClient = $this->clientWithSchema();
        $uuid = $this->createDocumentFixture(DocumentAccessLevel::MEMBER, DocumentStatus::PUBLISHED);
        $anonymousClient->request('GET', '/documents/' . $uuid . '/download', server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/auth/login');
    }

    /** @dataProvider memberRoles */
    public function testPublishedMemberDocumentAllowsAuthenticatedMembers(string $role): void
    {
        $client = $this->authenticatedClient([$role]);
        $uuid = $this->createDocumentFixture(DocumentAccessLevel::MEMBER, DocumentStatus::PUBLISHED);
        $client->request('GET', '/documents/' . $uuid . '/download', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK, $role);
    }

    public function testRestrictedDocumentRequiresTheDedicatedPermission(): void
    {
        $userClient = $this->authenticatedClient(['ROLE_USER']);
        $uuid = $this->createDocumentFixture(DocumentAccessLevel::RESTRICTED, DocumentStatus::PUBLISHED);
        $userClient->request('GET', '/documents/' . $uuid . '/download', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testRestrictedDocumentIsDownloadableWithTheDedicatedPermission(): void
    {
        $adminClient = $this->authenticatedClient(['ROLE_ADMIN']);
        $uuid = $this->createDocumentFixture(DocumentAccessLevel::RESTRICTED, DocumentStatus::PUBLISHED);
        $adminClient->request('GET', '/documents/' . $uuid . '/download', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testPrivateDocumentIsNeverAvailableThroughTheExternalDownloadRoute(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);
        $uuid = $this->createDocumentFixture(DocumentAccessLevel::PRIVATE, DocumentStatus::PUBLISHED);

        $client->request('GET', '/documents/' . $uuid . '/download', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testDraftAndArchivedDocumentsAreNotDownloadableEvenWhenPublic(): void
    {
        $client = $this->clientWithSchema();

        foreach ([DocumentStatus::DRAFT, DocumentStatus::ARCHIVED] as $status) {
            $uuid = $this->createDocumentFixture(DocumentAccessLevel::PUBLIC, $status);
            $client->request('GET', '/documents/' . $uuid . '/download', server: ['HTTPS' => 'on']);
            self::assertResponseRedirects('/auth/login', Response::HTTP_FOUND, $status->value);
        }
    }

    public function testUnknownUuidReturnsNotFound(): void
    {
        $client = $this->clientWithSchema();
        $client->request('GET', '/documents/' . Uuid::v7()->toRfc4122() . '/download', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testMissingPhysicalFileReturnsNotFoundInsteadOfAnUnhandledServerError(): void
    {
        $client = $this->clientWithSchema();
        $uuid = $this->createDocumentFixture(DocumentAccessLevel::PUBLIC, DocumentStatus::PUBLISHED, false);
        $client->request('GET', '/documents/' . $uuid . '/download', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    /** @param list<string> $roles */
    private function authenticatedClient(array $roles): KernelBrowser
    {
        $client = $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = (new User())
            ->setEmail(sprintf('content-security-%d@example.test', ++self::$userSequence))
            ->setName('Content Security Test')
            ->setPassword('test-password');
        $user->setEnabled(true);
        $user->setRoles($roles);
        $entityManager->persist($user);
        $entityManager->flush();
        $client->loginUser($user);
        $client->disableReboot();

        return $client;
    }

    private function clientWithSchema(): KernelBrowser
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $entityManager->persist(new Reglages('app_title', 'Application title', 'Avocat CI', 'text'));
        $entityManager->persist((new Images())->setName('app_logo')->setLabel('Logo'));
        $entityManager->persist((new Images())->setName('app_favicon')->setLabel('Favicon'));
        $entityManager->persist(
            (new Currencies())
                ->setCurrencyCode('XOF')
                ->setCurrencyName('Franc CFA')
                ->setRightSymbol('FCFA')
                ->setDecimalPlace(0)
                ->setIsActive(true)
        );
        $entityManager->flush();
        $client->disableReboot();

        return $client;
    }

    /** @return iterable<string, array{string}> */
    public static function deniedBackofficeRoles(): iterable
    {
        yield 'avocat' => ['ROLE_AVOCAT'];
        yield 'user' => ['ROLE_USER'];
    }

    /** @return iterable<string, array{string}> */
    public static function memberRoles(): iterable
    {
        yield 'user' => ['ROLE_USER'];
        yield 'avocat' => ['ROLE_AVOCAT'];
    }

    private function createDocumentFixture(DocumentAccessLevel $accessLevel, DocumentStatus $status, bool $physicalFile = true): string
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $fileName = bin2hex(random_bytes(24)) . '.pdf';
        $storageName = 'documents/' . $fileName;
        $file = (new StoredFileEntity())
            ->setOriginalName('guide.pdf')
            ->setStorageName($storageName)
            ->setMimeType('application/pdf')
            ->setSize(45)
            ->setChecksum(hash('sha256', 'test-pdf'));
        $entityManager->persist($file);
        $entityManager->flush();

        if ($physicalFile) {
            $path = self::$storageDirectory . '/private/' . $storageName;
            (new Filesystem())->mkdir(dirname($path));
            file_put_contents($path, "%PDF-1.4 test\n%%EOF\n");
        }

        $document = (new DocumentPublicationEntity())
            ->setTitle('Guide de test')
            ->setSlug('guide-de-test-' . bin2hex(random_bytes(3)))
            ->setDescription('Publication de test')
            ->setStoredFileId($file->getId() ?? 0)
            ->setAccessLevel($accessLevel)
            ->setStatus($status);
        $entityManager->persist($document);
        $entityManager->flush();

        return $document->getUuidAsString() ?? '';
    }
}
