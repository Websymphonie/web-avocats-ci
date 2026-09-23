<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use Doctrine\Bundle\FixturesBundle\Loader\FixturesProvider;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\ContentContext\Domain\Enum\EventStatus;
use Websymphonie\ContentContext\Domain\Enum\NewsStatus;
use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
use Websymphonie\ContentContext\Domain\Enum\DocumentAccessLevel;
use Websymphonie\ContentContext\Domain\Enum\DocumentStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\DocumentPublication\DocumentPublicationEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Event\EventEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EventCategory\EventCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\News\NewsEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\NewsCategory\NewsCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page\PageEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Batonnier\BatonnierMandateEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\CouncilMember\CouncilMemberEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\CourseModule\CourseModuleEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson\LessonEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LiveTrainingDetails\LiveTrainingDetailsEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingCategory\TrainingCategoryEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingTag\TrainingTagEntity;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\StoredFileEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Fixtures\DemoFundSolidarityResourcesFixtures;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Fixtures\DemoCarpaResourcesFixtures;
use Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Entity\TrainingOffer\TrainingOfferEntity;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class DemoFixturesIntegrityTest extends WebTestCase
{
    private static string $storageDirectory;
    private static string $demoPassword;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        self::$storageDirectory = sys_get_temp_dir() . '/avocat-data-fixtures-' . bin2hex(random_bytes(4));
        self::$demoPassword = bin2hex(random_bytes(16));
        mkdir(self::$storageDirectory, 0775, true);
        foreach ([
            'DATABASE_URL' => 'sqlite:///:memory:',
            'APP_STORAGE_DIR' => self::$storageDirectory,
            'APP_DEMO_MEMBER_PASSWORD' => self::$demoPassword,
            'MYSQL_VERSION' => '8.0.40',
            'SECURE_SCHEME' => 'https',
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
        putenv('APP_DEMO_MEMBER_PASSWORD');
        unset($_ENV['APP_DEMO_MEMBER_PASSWORD'], $_SERVER['APP_DEMO_MEMBER_PASSWORD']);
        parent::tearDownAfterClass();
    }

    public function testDemoDatasetIsCoherentAndDeterministic(): void
    {
        self::bootKernel();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $entityManager->persist((new Currencies())
            ->setCurrencyCode('XOF')
            ->setCurrencyName('Franc CFA')
            ->setDecimalPlace(0)
            ->setIsActive(true));
        $entityManager->flush();

        $loader = static::getContainer()->get('doctrine.fixtures.loader');
        self::assertInstanceOf(FixturesProvider::class, $loader);
        $fixtures = $loader->getFixtures(['demo']);
        self::assertNotEmpty($fixtures);
        (new ORMExecutor($entityManager, new ORMPurger($entityManager)))->execute($fixtures, true);

        self::assertSame(6, $entityManager->getRepository(NewsCategoryEntity::class)->count([]));
        self::assertSame(6, $entityManager->getRepository(EventCategoryEntity::class)->count([]));
        self::assertSame(11, $entityManager->getRepository(TagEntity::class)->count([]));
        self::assertNotNull($entityManager->getRepository(TagEntity::class)->findOneBy(['slug' => 'fonds-de-solidarite']));
        self::assertSame(24, $entityManager->getRepository(NewsEntity::class)->count([]));
        self::assertSame(18, $entityManager->getRepository(EventEntity::class)->count([]));
        self::assertSame(11, $entityManager->getRepository(PageEntity::class)->count([]));
        self::assertSame(7, $entityManager->getRepository(TrainingCategoryEntity::class)->count([]));
        self::assertSame(8, $entityManager->getRepository(TrainingTagEntity::class)->count([]));
        self::assertSame(14, $entityManager->getRepository(TrainingEntity::class)->count([]));
        self::assertSame(24, $entityManager->getRepository(MediaEntity::class)->count([]));
        self::assertSame(1, $entityManager->getRepository(BatonnierMandateEntity::class)->count([]));
        self::assertSame(19, $entityManager->getRepository(CouncilMemberEntity::class)->count([]));
        self::assertSame(6, $entityManager->getRepository(LiveTrainingDetailsEntity::class)->count([]));
        self::assertSame(21, $entityManager->getRepository(CourseModuleEntity::class)->count([]));
        self::assertSame(57, $entityManager->getRepository(LessonEntity::class)->count([]));
        self::assertSame(5, $entityManager->getRepository(TrainingOfferEntity::class)->count([]));

        self::assertSame(16, $entityManager->getRepository(NewsEntity::class)->count(['status' => NewsStatus::PUBLISHED]));
        self::assertSame(10, $entityManager->getRepository(EventEntity::class)->count(['status' => EventStatus::PUBLISHED]));
        self::assertSame(9, $entityManager->getRepository(TrainingEntity::class)->count(['status' => TrainingStatus::PUBLISHED]));
        self::assertSame(10, $entityManager->getRepository(PageEntity::class)->count(['status' => PageStatus::PUBLISHED]));
        self::assertSame(4, $entityManager->getRepository(PageEntity::class)->count(['editorialGroup' => PageGroup::LEGAL]));
        self::assertSame(1, $entityManager->getRepository(PageEntity::class)->count(['editorialGroup' => PageGroup::ACCOUNT]));
        self::assertSame(6, $entityManager->getRepository(PageEntity::class)->count(['editorialGroup' => PageGroup::BAR]));
        $presentation = $entityManager->getRepository(PageEntity::class)->findOneBy(['slug' => 'presentation']);
        self::assertInstanceOf(PageEntity::class, $presentation);
        self::assertSame(PageStatus::PUBLISHED, $presentation->getStatus());
        self::assertSame(PageGroup::BAR, $presentation->getGroup());
        self::assertSame(10, $presentation->getSortOrder());
        self::assertStringContainsString('Nos missions', $presentation->getContent());
        self::assertNotNull($presentation->getPublishedAt());
        $history = $entityManager->getRepository(PageEntity::class)->findOneBy(['slug' => 'historique']);
        self::assertInstanceOf(PageEntity::class, $history);
        self::assertSame(PageStatus::PUBLISHED, $history->getStatus());
        self::assertSame(PageGroup::BAR, $history->getGroup());
        self::assertSame(20, $history->getSortOrder());
        self::assertStringContainsString('2006', $history->getContent());
        self::assertNotNull($history->getPublishedAt());
        self::assertNotNull($history->getCoverMediaId());
        self::assertNotNull($entityManager->getRepository(MediaEntity::class)->find($history->getCoverMediaId()));
        $batonnierPage = $entityManager->getRepository(PageEntity::class)->findOneBy(['slug' => 'le-batonnier']);
        self::assertInstanceOf(PageEntity::class, $batonnierPage);
        self::assertSame(PageStatus::PUBLISHED, $batonnierPage->getStatus());
        self::assertSame(PageGroup::BAR, $batonnierPage->getGroup());
        self::assertSame(30, $batonnierPage->getSortOrder());
        self::assertStringContainsString('scrutin majoritaire', $batonnierPage->getContent());
        self::assertStringNotContainsString('secretariat@ordredesavocats.ci', $batonnierPage->getContent());
        $councilPage = $entityManager->getRepository(PageEntity::class)->findOneBy(['slug' => 'conseil-de-l-ordre']);
        self::assertInstanceOf(PageEntity::class, $councilPage);
        self::assertSame(PageStatus::PUBLISHED, $councilPage->getStatus());
        self::assertSame(PageGroup::BAR, $councilPage->getGroup());
        self::assertSame(40, $councilPage->getSortOrder());
        self::assertStringContainsString('organe délibérant', $councilPage->getContent());
        self::assertStringNotContainsString('Florence LOAN', $councilPage->getContent());
        $mandate = $entityManager->getRepository(BatonnierMandateEntity::class)->findOneBy(['fullName' => 'Me Florence LOAN épse MESSAN']);
        self::assertInstanceOf(BatonnierMandateEntity::class, $mandate);
        self::assertSame('2024-10-02', $mandate->getMandateStartedAt()->format('Y-m-d'));
        self::assertNull($mandate->getMandateEndedAt());
        self::assertNotNull($mandate->getPortraitMediaId());
        self::assertNotNull($mandate->getSummary());
        $members = $entityManager->getRepository(CouncilMemberEntity::class)->findBy([], ['sortOrder' => 'ASC']);
        self::assertSame(range(10, 190, 10), array_map(static fn (CouncilMemberEntity $member): int => $member->getSortOrder(), $members));
        self::assertCount(19, array_filter($members, static fn (CouncilMemberEntity $member): bool => $member->getMandateEndedAt() === null));
        self::assertSame($mandate->getPortraitMediaId(), $members[0]->getPortraitMediaId());
        self::assertSame('Bâtonnier en exercice', $members[0]->getFunction());
        self::assertSame('Secrétaire de l’Ordre', $members[1]->getFunction());
        self::assertSame('Ancien Bâtonnier 2015-2018', $members[2]->getFunction());
        foreach ($members as $member) {
            self::assertNull($member->getMandateStartedAt());
            self::assertNull($member->getMandateEndedAt());
            self::assertNotNull($member->getPortraitMediaId());
            self::assertNotNull($entityManager->getRepository(MediaEntity::class)->find($member->getPortraitMediaId()));
        }
        $fundPage = $entityManager->getRepository(PageEntity::class)->findOneBy(['slug' => 'fonds-de-solidarite']);
        self::assertInstanceOf(PageEntity::class, $fundPage);
        self::assertSame(PageStatus::PUBLISHED, $fundPage->getStatus());
        self::assertSame(PageGroup::BAR, $fundPage->getGroup());
        self::assertStringContainsString('politique CARE', $fundPage->getContent());
        self::assertStringContainsString('La santé pour tous', $fundPage->getContent());
        self::assertStringContainsString('Le dispositif Yako', $fundPage->getContent());
        self::assertStringNotContainsString('FORMULAIRE DE DEMANDE DE PRÊT', $fundPage->getContent());
        self::assertSame(50, $fundPage->getSortOrder());
        $carpaPage = $entityManager->getRepository(PageEntity::class)->findOneBy(['slug' => 'carpa']);
        self::assertInstanceOf(PageEntity::class, $carpaPage);
        self::assertSame(PageStatus::PUBLISHED, $carpaPage->getStatus());
        self::assertSame(PageGroup::BAR, $carpaPage->getGroup());
        self::assertStringContainsString('Caisse Autonome de Règlement Pécuniaire des Avocats', $carpaPage->getContent());
        self::assertStringContainsString('sous la supervision du Bâtonnier', $carpaPage->getContent());
        self::assertStringContainsString('href="/contact"', $carpaPage->getContent());
        self::assertNotNull($carpaPage->getPublishedAt());
        self::assertSame(60, $carpaPage->getSortOrder());
        self::assertSame(10, $entityManager->getRepository(PageEntity::class)->findOneBy(['slug' => 'mentions-legales'])->getSortOrder());
        self::assertSame(20, $entityManager->getRepository(PageEntity::class)->findOneBy(['slug' => 'politique-confidentialite'])->getSortOrder());
        self::assertSame(30, $entityManager->getRepository(PageEntity::class)->findOneBy(['slug' => 'conditions-generales-utilisation'])->getSortOrder());

        foreach ($entityManager->getRepository(NewsEntity::class)->findAll() as $news) {
            self::assertTrue($news->getCategories()->count() > 0);
            self::assertNotSame('', $news->getSlug());
            if ($news->getCoverMediaId() !== null) {
                self::assertNotNull($entityManager->getRepository(MediaEntity::class)->find($news->getCoverMediaId()));
            }
        }
        foreach ($entityManager->getRepository(EventEntity::class)->findAll() as $event) {
            self::assertTrue($event->getCategories()->count() > 0);
            self::assertNotSame('', $event->getSlug());
            if ($event->getCoverMediaId() !== null) {
                self::assertNotNull($entityManager->getRepository(MediaEntity::class)->find($event->getCoverMediaId()));
            }
        }
        foreach ($entityManager->getRepository(PageEntity::class)->findAll() as $page) {
            if ($page->getCoverMediaId() !== null) {
                self::assertNotNull($entityManager->getRepository(MediaEntity::class)->find($page->getCoverMediaId()));
            }
        }
        foreach ($entityManager->getRepository(TrainingEntity::class)->findAll() as $training) {
            self::assertTrue($training->getCategories()->count() > 0);
            self::assertTrue($training->getTags()->count() > 0);
            if ($training->getCoverMediaId() !== null) {
                self::assertNotNull($entityManager->getRepository(MediaEntity::class)->find($training->getCoverMediaId()));
            }
        }

        self::assertDirectoryExists(self::$storageDirectory . '/public/content/covers');
        self::assertDirectoryExists(self::$storageDirectory . '/public/training/covers');
        self::assertDirectoryExists(self::$storageDirectory . '/public/institution/portraits');
        self::assertFileExists(self::$storageDirectory . '/public/content/covers/' . $entityManager->getRepository(MediaEntity::class)->find($history->getCoverMediaId())->getStorageName());
        self::assertCount(16, $entityManager->getRepository(NewsEntity::class)->findBy(['status' => NewsStatus::PUBLISHED]));
        self::assertCount(1, $entityManager->getRepository(PageEntity::class)->findBy(['status' => PageStatus::DRAFT]));

        $fundDocuments = $entityManager->getRepository(DocumentPublicationEntity::class)->findBy(['accessLevel' => DocumentAccessLevel::LAWYER, 'status' => DocumentStatus::PUBLISHED]);
        self::assertCount(3, $fundDocuments);
        $fundTag = $entityManager->getRepository(TagEntity::class)->findOneBy(['slug' => 'fonds-de-solidarite']);
        self::assertInstanceOf(TagEntity::class, $fundTag);
        foreach ($fundDocuments as $document) {
            self::assertContains($fundTag, $document->getTags()->toArray());
            $storedFile = $entityManager->getRepository(StoredFileEntity::class)->find($document->getStoredFileId());
            self::assertInstanceOf(StoredFileEntity::class, $storedFile);
            self::assertStringStartsWith('documents/', $storedFile->getStorageName());
            self::assertFileExists(self::$storageDirectory . '/private/' . $storedFile->getStorageName());
            self::assertFileDoesNotExist(dirname(__DIR__, 2) . '/public/' . $storedFile->getStorageName());
        }

        $carpaDocument = $entityManager->getRepository(DocumentPublicationEntity::class)->findOneBy(['slug' => 'reglement-interieur-barreau-cote-ivoire']);
        self::assertInstanceOf(DocumentPublicationEntity::class, $carpaDocument);
        self::assertSame('Règlement intérieur du Barreau de Côte d’Ivoire', $carpaDocument->getTitle());
        self::assertSame(DocumentAccessLevel::PUBLIC, $carpaDocument->getAccessLevel());
        self::assertSame(DocumentStatus::PUBLISHED, $carpaDocument->getStatus());
        self::assertCount(0, $carpaDocument->getTags());
        $carpaStoredFile = $entityManager->getRepository(StoredFileEntity::class)->find($carpaDocument->getStoredFileId());
        self::assertInstanceOf(StoredFileEntity::class, $carpaStoredFile);
        self::assertStringStartsWith('documents/', $carpaStoredFile->getStorageName());
        self::assertFileExists(self::$storageDirectory . '/private/' . $carpaStoredFile->getStorageName());

        $resourceFixture = null;
        foreach ($fixtures as $fixture) {
            if ($fixture instanceof DemoFundSolidarityResourcesFixtures) {
                $resourceFixture = $fixture;
                break;
            }
        }
        self::assertInstanceOf(DemoFundSolidarityResourcesFixtures::class, $resourceFixture);
        $storedFileCount = $entityManager->getRepository(StoredFileEntity::class)->count([]);
        $resourceFixture->load($entityManager);
        self::assertSame(4, $entityManager->getRepository(DocumentPublicationEntity::class)->count([]));
        self::assertSame($storedFileCount, $entityManager->getRepository(StoredFileEntity::class)->count([]));

        $carpaFixture = null;
        foreach ($fixtures as $fixture) {
            if ($fixture instanceof DemoCarpaResourcesFixtures) {
                $carpaFixture = $fixture;
                break;
            }
        }
        self::assertInstanceOf(DemoCarpaResourcesFixtures::class, $carpaFixture);
        $carpaFixture->load($entityManager);
        self::assertSame(4, $entityManager->getRepository(DocumentPublicationEntity::class)->count([]));
        self::assertSame($storedFileCount, $entityManager->getRepository(StoredFileEntity::class)->count([]));
        self::assertFileExists(dirname(__DIR__, 2) . '/src/ContentContext/Infrastructure/Persistence/Doctrine/Fixtures/Files/Carpa/reglement-interieur-barreau-cote-ivoire.pdf');
    }
}
