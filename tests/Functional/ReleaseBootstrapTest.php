<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Infrastructure\Bootstrap\InstitutionalContentBootstrapper;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Batonnier\BatonnierMandateEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\CouncilMember\CouncilMemberEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Event\EventEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\News\NewsEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\DocumentPublication\DocumentPublicationEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page\PageEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\ContentContext\Infrastructure\SeedData\InstitutionalDocumentData;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\StoredFileEntity;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class ReleaseBootstrapTest extends WebTestCase
{
    private static string $storageDirectory;
    private static string|false $previousStorageDirectory = false;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        self::$previousStorageDirectory = getenv('APP_STORAGE_DIR');
        self::$storageDirectory = sys_get_temp_dir() . '/avocat-release-bootstrap-' . bin2hex(random_bytes(5));

        foreach ([
            'DATABASE_URL' => 'sqlite:///:memory:',
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

    public function testReleaseBootstrapsAreIdempotentAndServePublicPagesWithoutDemoData(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        (new SchemaTool($entityManager))->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $systemBootstrap = static::getContainer()->get(\Websymphonie\AdminContext\Infrastructure\Bootstrap\SystemBootstrapper::class);
        $institutionalBootstrap = static::getContainer()->get(InstitutionalContentBootstrapper::class);
        $client->disableReboot();

        $client->request('GET', '/', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK, 'homepage sans réglages préinstallés');
        $client->request('GET', '/auth/login', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK, 'login sans réglages préinstallés');

        self::assertSame(['settings' => 4, 'images' => 2], $systemBootstrap->bootstrap());
        self::assertSame(['settings' => 0, 'images' => 0], $systemBootstrap->bootstrap());
        self::assertSame([
            'pages' => 11,
            'batonnier' => 1,
            'councilMembers' => 19,
            'documents' => 4,
            'documentConflicts' => [],
        ], $institutionalBootstrap->bootstrap());

        $becomeLawyer = $entityManager->getRepository(PageEntity::class)->findOneBy([
            'editorialGroup' => PageGroup::PROFESSION,
            'slug' => 'devenir-avocat',
        ]);
        self::assertInstanceOf(PageEntity::class, $becomeLawyer);
        self::assertSame(PageStatus::DRAFT, $becomeLawyer->getStatus());
        $becomeLawyerUuid = $becomeLawyer->getUuidAsString();

        self::assertSame([
            'pages' => 0,
            'batonnier' => 0,
            'councilMembers' => 0,
            'documents' => 0,
            'documentConflicts' => [],
        ], $institutionalBootstrap->bootstrap());
        $entityManager->clear();

        self::assertSame(11, $entityManager->getRepository(PageEntity::class)->count([]));
        self::assertSame(1, $entityManager->getRepository(BatonnierMandateEntity::class)->count([]));
        self::assertSame(19, $entityManager->getRepository(CouncilMemberEntity::class)->count([]));
        self::assertSame(0, $entityManager->getRepository(User::class)->count([]));
        self::assertSame(0, $entityManager->getRepository(NewsEntity::class)->count([]));
        self::assertSame(0, $entityManager->getRepository(EventEntity::class)->count([]));
        self::assertSame(4, $entityManager->getRepository(DocumentPublicationEntity::class)->count([]));
        self::assertSame(4, $entityManager->getRepository(StoredFileEntity::class)->count([]));
        self::assertSame(1, $entityManager->getRepository(TagEntity::class)->count(['slug' => 'fonds-de-solidarite']));

        foreach (InstitutionalDocumentData::definitions() as $definition) {
            $publication = $entityManager->getRepository(DocumentPublicationEntity::class)->findOneBy(['slug' => $definition['slug']]);
            self::assertInstanceOf(DocumentPublicationEntity::class, $publication);
            self::assertSame($definition['title'], $publication->getTitle());
            self::assertSame($definition['description'], $publication->getDescription());
            self::assertSame($definition['accessLevel'], $publication->getAccessLevel());
            self::assertSame(DocumentStatus::PUBLISHED, $publication->getStatus());
            self::assertSame($definition['tagSlug'] === null ? 0 : 1, $publication->getTags()->count());

            $storedFile = $entityManager->getRepository(StoredFileEntity::class)->find($publication->getStoredFileId());
            self::assertInstanceOf(StoredFileEntity::class, $storedFile);
            self::assertSame($definition['filename'], $storedFile->getOriginalName());
            self::assertSame(hash_file('sha256', InstitutionalDocumentData::sourcePath($definition)), $storedFile->getChecksum());
            self::assertStringStartsWith('documents/', $storedFile->getStorageName());
            self::assertFileExists(self::$storageDirectory . '/private/' . $storedFile->getStorageName());
        }

        $becomeLawyerAfterSecondRun = $entityManager->getRepository(PageEntity::class)->findOneBy([
            'editorialGroup' => PageGroup::PROFESSION,
            'slug' => 'devenir-avocat',
        ]);
        self::assertSame($becomeLawyerUuid, $becomeLawyerAfterSecondRun?->getUuidAsString());
        self::assertSame(PageStatus::DRAFT, $becomeLawyerAfterSecondRun?->getStatus());

        foreach ([
            '/',
            '/auth/login',
            '/le-barreau',
            '/le-barreau/presentation',
            '/carpa',
            '/carpa/presentation',
            '/lbc-ft-fp',
            '/informations/mentions-legales',
            '/informations/politique-confidentialite',
            '/assistance-violences-domestiques',
            '/contact',
            '/avocats',
        ] as $path) {
            $client->request('GET', $path, server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(Response::HTTP_OK, $path);
        }

        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/auth/login');
        $client->request('GET', '/admin', server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/auth/login');

        $publicDocument = $entityManager->getRepository(DocumentPublicationEntity::class)->findOneBy(['slug' => 'reglement-interieur-barreau-cote-ivoire']);
        self::assertInstanceOf(DocumentPublicationEntity::class, $publicDocument);
        $client->request('GET', '/documents/' . $publicDocument->getUuidAsString() . '/download', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame('application/pdf', $client->getResponse()->headers->get('Content-Type'));

        $lawyerDocuments = [];
        foreach (array_slice(InstitutionalDocumentData::definitions(), 1) as $definition) {
            $lawyerDocument = $entityManager->getRepository(DocumentPublicationEntity::class)->findOneBy(['slug' => $definition['slug']]);
            self::assertInstanceOf(DocumentPublicationEntity::class, $lawyerDocument);
            $lawyerDocuments[] = $lawyerDocument;
            $client->request('GET', '/documents/' . $lawyerDocument->getUuidAsString() . '/download', server: ['HTTPS' => 'on']);
            self::assertResponseRedirects('/auth/login');
        }

        $lawyer = (new User())
            ->setEmail('release-lawyer@example.test')
            ->setName('Release Lawyer')
            ->setPassword('not-a-real-password');
        $lawyer->setEnabled(true);
        $lawyer->setRoles(['ROLE_AVOCAT']);
        $entityManager->persist($lawyer);
        $entityManager->flush();
        $client->loginUser($lawyer);
        foreach ($lawyerDocuments as $lawyerDocument) {
            $client->request('GET', '/documents/' . $lawyerDocument->getUuidAsString() . '/download', server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(Response::HTTP_OK);
            self::assertStringContainsString('private', (string) $client->getResponse()->headers->get('Cache-Control'));
            self::assertStringContainsString('no-store', (string) $client->getResponse()->headers->get('Cache-Control'));
        }
        $client->request('GET', '/espace/ressources/fonds-de-solidarite', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Formulaire de demande de prêt');
        self::assertSelectorTextContains('body', 'Formulaire de demande de don');
        self::assertSelectorTextContains('body', 'Guide du réseau de soins');

        $publication = $entityManager->getRepository(DocumentPublicationEntity::class)->findOneBy(['slug' => 'fonds-solidarite-demande-pret']);
        self::assertInstanceOf(DocumentPublicationEntity::class, $publication);
        $publication->setTitle('Titre divergent préexistant');
        $entityManager->flush();
        $conflictResult = $institutionalBootstrap->bootstrap();
        self::assertSame(0, $conflictResult['documents']);
        self::assertSame(['fonds-solidarite-demande-pret'], $conflictResult['documentConflicts']);
        self::assertSame(4, $entityManager->getRepository(StoredFileEntity::class)->count([]));
        self::assertSame('Titre divergent préexistant', $publication->getTitle());

        $client->request('GET', '/devenir-avocat', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        self::assertInstanceOf(Reglages::class, $entityManager->getRepository(Reglages::class)->findOneBy(['name' => 'app_title']));
        self::assertInstanceOf(Images::class, $entityManager->getRepository(Images::class)->findOneBy(['name' => 'app_logo']));
    }
}
