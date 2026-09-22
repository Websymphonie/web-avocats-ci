<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use DateTimeImmutable;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\ContentContext\Domain\Enum\EventFormat;
use Websymphonie\ContentContext\Domain\Enum\EventStatus;
use Websymphonie\ContentContext\Domain\Enum\NewsStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Event\EventEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\News\NewsEntity;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class PublicHomeTest extends WebTestCase
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

    public function testHomepageUsesPublishedContentAndCanonicalDetailLinks(): void
    {
        $client = $this->clientWithSchema();
        $this->createDataset();

        $client->request('GET', '/', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Actualité la plus récente', $content);
        self::assertStringContainsString('Actualité publiée précédente', $content);
        self::assertStringNotContainsString('Actualité brouillon', $content);
        self::assertStringContainsString('/actualites/actualite-la-plus-recente', $content);
        self::assertStringNotContainsString('/#actualite-', $content);
        self::assertStringContainsString('/uploads/content/covers/homepage-cover.jpg', $content);
        self::assertSame('/le-barreau', $client->getCrawler()->filter('#institution')->attr('href'));

        self::assertStringContainsString('Événement le plus proche', $content);
        self::assertStringContainsString('Événement suivant', $content);
        self::assertStringNotContainsString('Événement brouillon', $content);
        self::assertStringContainsString('/evenements/evenement-le-plus-proche', $content);
        self::assertStringNotContainsString('/#evenement-', $content);

        $newsTitles = $client->getCrawler()->filter('#actualites [data-homepage-event-carousel-target="slide"] h3')->each(static fn ($node): string => trim($node->text()));
        self::assertSame('Actualité la plus récente', $newsTitles[0]);

        $eventTitles = $client->getCrawler()->filter('#evenements [data-homepage-event-carousel-target="slide"] h3')->each(static fn ($node): string => trim($node->text()));
        self::assertSame('Événement le plus proche', $eventTitles[0]);
        self::assertSame('Événement suivant', $eventTitles[1]);
    }

    public function testHomepageShowsProfessionalEmptyStatesWithoutFakeContent(): void
    {
        $client = $this->clientWithSchema();

        $client->request('GET', '/', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#actualites', 'Aucune actualité publiée pour le moment.');
        self::assertSelectorTextContains('#evenements', 'Aucun événement à venir pour le moment.');
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

    private function createDataset(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $cover = (new MediaEntity())
            ->setOriginalName('homepage-cover.jpg')
            ->setStorageName('homepage-cover.jpg')
            ->setMimeType('image/jpeg')
            ->setSize(120)
            ->setWidth(1200)
            ->setHeight(675)
            ->setStoragePath('content/covers/homepage-cover.jpg');
        $entityManager->persist($cover);
        $entityManager->flush();

        foreach ([
            $this->news('Actualité la plus récente', 'actualite-la-plus-recente', new DateTimeImmutable('-1 hour'), $cover->getId()),
            $this->news('Actualité publiée précédente', 'actualite-publiee-precedente', new DateTimeImmutable('-2 hours')),
            $this->news('Actualité brouillon', 'actualite-brouillon-home', null, null, NewsStatus::DRAFT),
        ] as $news) {
            $entityManager->persist($news);
        }

        foreach ([
            $this->event('Événement le plus proche', 'evenement-le-plus-proche', new DateTimeImmutable('+1 day'), $cover->getId()),
            $this->event('Événement suivant', 'evenement-suivant', new DateTimeImmutable('+3 days')),
            $this->event('Événement passé', 'evenement-passe-home', new DateTimeImmutable('-1 day')),
            $this->event('Événement brouillon', 'evenement-brouillon-home', new DateTimeImmutable('+2 days'), null, EventStatus::DRAFT),
        ] as $event) {
            $entityManager->persist($event);
        }

        $entityManager->flush();
    }

    private function news(string $title, string $slug, ?DateTimeImmutable $publishedAt, ?int $coverMediaId = null, NewsStatus $status = NewsStatus::PUBLISHED): NewsEntity
    {
        return (new NewsEntity())
            ->setTitle($title)
            ->setSlug($slug)
            ->setExcerpt('Résumé public')
            ->setBody('<p>Contenu public.</p>')
            ->setStatus($status)
            ->setPublishedAt($publishedAt)
            ->setCoverMediaId($coverMediaId);
    }

    private function event(string $title, string $slug, DateTimeImmutable $startsAt, ?int $coverMediaId = null, EventStatus $status = EventStatus::PUBLISHED): EventEntity
    {
        return (new EventEntity())
            ->setTitle($title)
            ->setSlug($slug)
            ->setExcerpt('Résumé événement')
            ->setDescription('<p>Contenu événement.</p>')
            ->setFormat(EventFormat::IN_PERSON)
            ->setStartsAt($startsAt)
            ->setEndsAt($startsAt->modify('+2 hours'))
            ->setVenueName('Maison de l’Avocat')
            ->setAddress('Abidjan, Côte d’Ivoire')
            ->setStatus($status)
            ->setPublishedAt($status === EventStatus::PUBLISHED ? new DateTimeImmutable('-1 day') : null)
            ->setCoverMediaId($coverMediaId);
    }
}
