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
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Event\EventEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EventCategory\EventCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\News\NewsEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\NewsCategory\NewsCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page\PageEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\CourseModule\CourseModuleEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Lesson\LessonEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LiveTrainingDetails\LiveTrainingDetailsEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingCategory\TrainingCategoryEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingTag\TrainingTagEntity;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;
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
        self::assertSame(10, $entityManager->getRepository(TagEntity::class)->count([]));
        self::assertSame(24, $entityManager->getRepository(NewsEntity::class)->count([]));
        self::assertSame(18, $entityManager->getRepository(EventEntity::class)->count([]));
        self::assertSame(6, $entityManager->getRepository(PageEntity::class)->count([]));
        self::assertSame(7, $entityManager->getRepository(TrainingCategoryEntity::class)->count([]));
        self::assertSame(8, $entityManager->getRepository(TrainingTagEntity::class)->count([]));
        self::assertSame(14, $entityManager->getRepository(TrainingEntity::class)->count([]));
        self::assertSame(4, $entityManager->getRepository(MediaEntity::class)->count([]));
        self::assertSame(6, $entityManager->getRepository(LiveTrainingDetailsEntity::class)->count([]));
        self::assertSame(21, $entityManager->getRepository(CourseModuleEntity::class)->count([]));
        self::assertSame(57, $entityManager->getRepository(LessonEntity::class)->count([]));
        self::assertSame(5, $entityManager->getRepository(TrainingOfferEntity::class)->count([]));

        self::assertSame(16, $entityManager->getRepository(NewsEntity::class)->count(['status' => NewsStatus::PUBLISHED]));
        self::assertSame(10, $entityManager->getRepository(EventEntity::class)->count(['status' => EventStatus::PUBLISHED]));
        self::assertSame(9, $entityManager->getRepository(TrainingEntity::class)->count(['status' => TrainingStatus::PUBLISHED]));
        self::assertSame(4, $entityManager->getRepository(PageEntity::class)->count(['status' => PageStatus::PUBLISHED]));
        self::assertSame(4, $entityManager->getRepository(PageEntity::class)->count(['editorialGroup' => PageGroup::LEGAL]));
        self::assertSame(1, $entityManager->getRepository(PageEntity::class)->count(['editorialGroup' => PageGroup::ACCOUNT]));
        self::assertSame(1, $entityManager->getRepository(PageEntity::class)->count(['editorialGroup' => PageGroup::BAR]));

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
        self::assertCount(16, $entityManager->getRepository(NewsEntity::class)->findBy(['status' => NewsStatus::PUBLISHED]));
        self::assertCount(2, $entityManager->getRepository(PageEntity::class)->findBy(['status' => PageStatus::DRAFT]));
    }
}
