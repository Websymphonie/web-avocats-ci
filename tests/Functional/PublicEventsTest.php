<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use DateTimeImmutable;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\ContentContext\Domain\Enum\EventFormat;
use Websymphonie\ContentContext\Domain\Enum\EventStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Event\EventEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EventCategory\EventCategoryEntity;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class PublicEventsTest extends WebTestCase
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

    public function testPublicListShowsUpcomingThenPastEventsAndFiltersByCategory(): void
    {
        $client = $this->clientWithSchema();
        [$nearest, $category] = $this->createEventDataset();

        $client->request('GET', '/evenements', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Événements');
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Événement à venir', $content);
        self::assertStringContainsString('Événement passé récent', $content);
        self::assertStringNotContainsString('Événement brouillon', $content);
        $titles = $client->getCrawler()->filter('h3, h4')->each(static fn ($node): string => trim($node->text()));
        $upcomingPosition = array_search('Événement à venir', $titles, true);
        $pastPosition = array_search('Événement passé récent', $titles, true);
        self::assertIsInt($upcomingPosition);
        self::assertIsInt($pastPosition);
        self::assertLessThan($pastPosition, $upcomingPosition);
        self::assertStringContainsString('/evenements/' . $nearest->getSlug(), $content);

        $client->request('GET', '/evenements?categorie=' . $category->getSlug(), server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Événement à venir', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Événement autre catégorie', (string) $client->getResponse()->getContent());
    }

    public function testPublishedEventDetailRendersRichContentCoverAndFallback(): void
    {
        $client = $this->clientWithSchema();
        [$nearest] = $this->createEventDataset();

        $client->request('GET', '/evenements/' . $nearest->getSlug(), server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Événement à venir');
        self::assertStringContainsString('Texte événement sanitizé.', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('/uploads/content/covers/public-event.jpg', (string) $client->getResponse()->getContent());

        $client->request('GET', '/evenements/evenement-passe-recent', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertStringContainsString('/assets/default.jpg', (string) $client->getResponse()->getContent());
    }

    public function testDraftAndUnknownEventsAreNotPubliclyAccessible(): void
    {
        $client = $this->clientWithSchema();
        $this->createEventDataset();

        $client->request('GET', '/evenements/evenement-brouillon', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $client->request('GET', '/evenements/does-not-exist', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testEventsNavigationIsAvailableFromHomepageAndActiveInPublicMenu(): void
    {
        $client = $this->clientWithSchema();
        [$nearest] = $this->createEventDataset();

        $client->request('GET', '/', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('href="/evenements"', (string) $client->getResponse()->getContent());

        $client->request('GET', '/evenements', server: ['HTTPS' => 'on']);
        self::assertSelectorTextContains('nav[aria-label="Navigation principale"] a[aria-current="page"]', 'Événements');

        $client->request('GET', '/evenements/' . $nearest->getSlug(), server: ['HTTPS' => 'on']);
        self::assertSelectorTextContains('nav[aria-label="Navigation principale"] a[aria-current="page"]', 'Événements');
    }

    private function clientWithSchema(): KernelBrowser
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

    /** @return array{EventEntity, EventCategoryEntity} */
    private function createEventDataset(): array
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $category = (new EventCategoryEntity())->setName('Conférences')->setSlug('conferences');
        $otherCategory = (new EventCategoryEntity())->setName('Institution')->setSlug('institution');
        $cover = (new MediaEntity())
            ->setOriginalName('public-event.jpg')
            ->setStorageName('event-cover.jpg')
            ->setMimeType('image/jpeg')
            ->setSize(120)
            ->setWidth(1200)
            ->setHeight(675)
            ->setStoragePath('content/covers/public-event.jpg');
        $entityManager->persist($category);
        $entityManager->persist($otherCategory);
        $entityManager->persist($cover);
        $entityManager->flush();

        $nearest = $this->event('Événement à venir', 'evenement-a-venir', EventStatus::PUBLISHED, new DateTimeImmutable('+1 day 10:00'), [$category], $cover->getId());
        $later = $this->event('Événement à venir plus tard', 'evenement-a-venir-plus-tard', EventStatus::PUBLISHED, new DateTimeImmutable('+4 days 10:00'), [$category]);
        $recent = $this->event('Événement passé récent', 'evenement-passe-recent', EventStatus::PUBLISHED, new DateTimeImmutable('-1 day 10:00'), [$category]);
        $other = $this->event('Événement autre catégorie', 'evenement-autre-categorie', EventStatus::PUBLISHED, new DateTimeImmutable('-2 days 10:00'), [$otherCategory]);
        $draft = $this->event('Événement brouillon', 'evenement-brouillon', EventStatus::DRAFT, new DateTimeImmutable('+2 days 10:00'), [$category]);

        foreach ([$nearest, $later, $recent, $other, $draft] as $event) {
            $entityManager->persist($event);
        }
        $entityManager->flush();

        return [$nearest, $category];
    }

    /** @param list<EventCategoryEntity> $categories */
    private function event(string $title, string $slug, EventStatus $status, DateTimeImmutable $startsAt, array $categories = [], ?int $coverMediaId = null): EventEntity
    {
        return (new EventEntity())
            ->setTitle($title)
            ->setSlug($slug)
            ->setExcerpt('Résumé événement')
            ->setDescription('<h2>' . $title . '</h2><p>Texte événement sanitizé.</p><script>alert(1)</script>')
            ->setFormat(EventFormat::IN_PERSON)
            ->setStartsAt($startsAt)
            ->setEndsAt($startsAt->modify('+2 hours'))
            ->setVenueName('Maison de l’Avocat')
            ->setAddress('Abidjan, Côte d’Ivoire')
            ->setStatus($status)
            ->setPublishedAt($status === EventStatus::PUBLISHED ? new DateTimeImmutable('-2 days') : null)
            ->setCoverMediaId($coverMediaId)
            ->replaceCategories($categories);
    }
}
