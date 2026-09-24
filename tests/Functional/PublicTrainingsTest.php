<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use DateTimeImmutable;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\LearningContext\Domain\Enum\LiveDeliveryMode;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingType;
use Websymphonie\LearningContext\Domain\Enum\TrainingVisibility;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\LiveTrainingDetails\LiveTrainingDetailsEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingCategory\TrainingCategoryEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\TrainingTag\TrainingTagEntity;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;
use Websymphonie\PaymentContext\Infrastructure\Persistence\Doctrine\Entity\TrainingOffer\TrainingOfferEntity;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class PublicTrainingsTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        foreach ([
            'DATABASE_URL' => 'sqlite:///:memory:',
            'MYSQL_VERSION' => '8.0.40',
            'SECURE_SCHEME' => 'https',
        ] as $name => $value) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        parent::setUpBeforeClass();
    }

    public function testPublicCatalogueShowsOnlyPublishedPublicTrainingsAndFilters(): void
    {
        $client = $this->clientWithSchema();
        [, , $category] = $this->createTrainingDataset();

        $client->request('GET', '/formations', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Formations');
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Cours public', $content);
        self::assertStringContainsString('Live public', $content);
        self::assertStringNotContainsString('Cours brouillon', $content);
        self::assertStringNotContainsString('Formation réservée', $content);

        $client->request('GET', '/formations?categorie=' . $category->getSlug(), server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Cours public', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Live public', (string) $client->getResponse()->getContent());

        $client->request('GET', '/formations?type=LIVE', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Live public', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Cours public', (string) $client->getResponse()->getContent());
    }

    public function testPublicCourseAndLiveDetailsDoNotExposeProtectedLiveData(): void
    {
        $client = $this->clientWithSchema();
        [$course, $live] = $this->createTrainingDataset();

        $client->request('GET', '/formations/' . $course->getSlug(), server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Cours public');
        self::assertStringContainsString('/uploads/content/covers/public-training.jpg', (string) $client->getResponse()->getContent());

        $client->request('GET', '/formations/' . $live->getSlug(), server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Live public', $content);
        self::assertStringContainsString('En ligne', $content);
        self::assertStringNotContainsString('https://example.test/learning/live/public', $content);
        self::assertStringNotContainsString('M7lc1UVf-VE', $content);
        self::assertStringNotContainsString('youtube-nocookie.com', $content);
        self::assertStringContainsString('/assets/default.jpg', $content);
        self::assertStringContainsString('data-controller="image-fallback"', $content);
        self::assertStringContainsString('25 000 FCFA', $content);
        self::assertStringContainsString('Se connecter pour continuer', $content);
        self::assertStringNotContainsString('payment_member_training_initiate', $content);
    }

    public function testPaidTrainingWithoutActiveOfferShowsNoPriceOrPaymentAction(): void
    {
        $client = $this->clientWithSchema();
        $this->createTrainingDataset();

        $client->request('GET', '/formations/formation-payante-sans-offre', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Tarif indisponible pour le moment.', $content);
        self::assertStringNotContainsString('Continuer vers le paiement', $content);
        self::assertStringNotContainsString('payment_member_training_initiate', $content);
    }

    public function testEmptyCategoryFilterExplainsTheSelectionAndOffersAFullReset(): void
    {
        $client = $this->clientWithSchema();
        [, , , , $emptyCategory] = $this->createTrainingDataset();

        $client->request('GET', '/formations?categorie=' . $emptyCategory->getSlug(), server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h3#trainings-empty-title', 'Aucune formation disponible dans la catégorie « Droit social »');
        self::assertSelectorExists('[aria-labelledby="trainings-empty-title"] a[href="/formations"]');
        self::assertSelectorTextContains('[aria-labelledby="trainings-empty-title"] a[href="/formations"]', 'Voir toutes les formations');
        self::assertSelectorExists('button[aria-expanded="false"][aria-controls="training-category-options-mobile"]');
    }

    public function testCategorySidebarUsesCompactMobileDisclosureAndPreservesDesktopNavigation(): void
    {
        $client = $this->clientWithSchema();
        [, , $category] = $this->createTrainingDataset();

        $client->request('GET', '/formations?categorie=' . $category->getSlug(), server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('button[aria-expanded="false"]');
        self::assertSelectorTextContains('button[aria-controls="training-category-options-mobile"]', 'Pratique professionnelle');
        self::assertSelectorTextContains('a[aria-label="Réinitialiser la catégorie"]', 'Effacer');
        self::assertSelectorExists('aside nav[aria-label="Catégories de formations"] a[aria-current="page"]');
    }

    public function testDraftMemberAndUnknownTrainingsAreNotPubliclyAccessible(): void
    {
        $client = $this->clientWithSchema();
        $this->createTrainingDataset();

        $client->request('GET', '/formations/cours-brouillon', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $client->request('GET', '/formations/formation-reservee', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $client->request('GET', '/formations/does-not-exist', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testTrainingNavigationIsAvailableFromHomepageAndActiveInPublicMenu(): void
    {
        $client = $this->clientWithSchema();
        [$course] = $this->createTrainingDataset();

        $client->request('GET', '/', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('href="/formations"', (string) $client->getResponse()->getContent());

        $client->request('GET', '/formations', server: ['HTTPS' => 'on']);
        self::assertSelectorTextContains('nav[aria-label="Navigation principale"] a[aria-current="page"]', 'Formations');

        $client->request('GET', '/formations/' . $course->getSlug(), server: ['HTTPS' => 'on']);
        self::assertSelectorTextContains('nav[aria-label="Navigation principale"] a[aria-current="page"]', 'Formations');
    }

    private function clientWithSchema(): \Symfony\Bundle\FrameworkBundle\KernelBrowser
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($entityManager))->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $entityManager->persist(new Reglages('app_title', 'Application title', 'Avocat CI', 'text'));
        $entityManager->persist(new Reglages('app_paginate_limit', 'Pagination', '10', 'number'));
        $entityManager->persist((new Images())->setName('app_favicon')->setLabel('Favicon'));
        $entityManager->persist((new Currencies())->setCurrencyCode('XOF')->setCurrencyName('Franc CFA')->setRightSymbol('FCFA')->setDecimalPlace(0)->setIsActive(true));
        $entityManager->flush();
        $client->disableReboot();

        return $client;
    }

    /** @return array{TrainingEntity, TrainingEntity, TrainingCategoryEntity, TrainingCategoryEntity, TrainingCategoryEntity} */
    private function createTrainingDataset(): array
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $category = (new TrainingCategoryEntity())->setName('Pratique professionnelle')->setSlug('pratique-professionnelle');
        $otherCategory = (new TrainingCategoryEntity())->setName('Déontologie')->setSlug('deontologie');
        $emptyCategory = (new TrainingCategoryEntity())->setName('Droit social')->setSlug('droit-social');
        $tag = (new TrainingTagEntity())->setName('Cabinet')->setSlug('cabinet');
        $cover = (new MediaEntity())
            ->setOriginalName('public-training.jpg')
            ->setStorageName('training-cover.jpg')
            ->setMimeType('image/jpeg')
            ->setSize(120)
            ->setWidth(1200)
            ->setHeight(675)
            ->setStoragePath('content/covers/public-training.jpg');
        foreach ([$category, $otherCategory, $emptyCategory, $tag, $cover] as $item) {
            $entityManager->persist($item);
        }
        $entityManager->flush();

        $course = $this->training('Cours public', 'cours-public', TrainingType::COURSE, TrainingStatus::PUBLISHED, TrainingVisibility::PUBLIC, TrainingAccessType::FREE, [$category], [$tag], $cover->getId());
        $live = $this->training('Live public', 'live-public', TrainingType::LIVE, TrainingStatus::PUBLISHED, TrainingVisibility::PUBLIC, TrainingAccessType::PAID, [$otherCategory]);
        $draft = $this->training('Cours brouillon', 'cours-brouillon', TrainingType::COURSE, TrainingStatus::DRAFT, TrainingVisibility::PUBLIC, TrainingAccessType::FREE, [$category]);
        $member = $this->training('Formation réservée', 'formation-reservee', TrainingType::COURSE, TrainingStatus::PUBLISHED, TrainingVisibility::MEMBER, TrainingAccessType::RESTRICTED, [$category]);
        $paidWithoutOffer = $this->training('Formation payante sans offre', 'formation-payante-sans-offre', TrainingType::COURSE, TrainingStatus::PUBLISHED, TrainingVisibility::PUBLIC, TrainingAccessType::PAID, [$category]);
        foreach ([$course, $live, $draft, $member, $paidWithoutOffer] as $training) {
            $entityManager->persist($training);
        }
        $entityManager->flush();

        $startsAt = new DateTimeImmutable('+3 days 10:00');
        $liveDetails = (new LiveTrainingDetailsEntity())
            ->setTrainingId($live->getId() ?? 0)
            ->setStartsAt($startsAt)
            ->setEndsAt($startsAt->modify('+2 hours'))
            ->setDeliveryMode(LiveDeliveryMode::ONLINE)
            ->setJoinUrl('https://example.test/learning/live/public')
            ->setStreamProvider(\Websymphonie\LearningContext\Domain\Enum\VideoProvider::YOUTUBE)
            ->setExternalStreamId('M7lc1UVf-VE');
        $entityManager->persist($liveDetails);
        $entityManager->persist((new TrainingOfferEntity())
            ->setTrainingId($live->getId() ?? 0)
            ->setAmount(25000)
            ->setCurrency('XOF')
            ->setActive(true));
        $entityManager->flush();

        return [$course, $live, $category, $otherCategory, $emptyCategory];
    }

    /** @param list<TrainingCategoryEntity> $categories @param list<TrainingTagEntity> $tags */
    private function training(string $title, string $slug, TrainingType $type, TrainingStatus $status, TrainingVisibility $visibility, TrainingAccessType $accessType, array $categories = [], array $tags = [], ?int $coverMediaId = null): TrainingEntity
    {
        return (new TrainingEntity($type))
            ->setTitle($title)
            ->setSlug($slug)
            ->setSummary('Résumé public de la formation.')
            ->setDescription('<h2>' . $title . '</h2><p>Contenu public de formation.</p><script>alert(1)</script>')
            ->setVisibility($visibility)
            ->setAccessType($accessType)
            ->setStatus($status)
            ->setPublishedAt($status === TrainingStatus::PUBLISHED ? new DateTimeImmutable('-1 day') : null)
            ->setCoverMediaId($coverMediaId)
            ->replaceCategories($categories)
            ->replaceTags($tags);
    }
}
