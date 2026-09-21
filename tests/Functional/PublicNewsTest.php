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
use Websymphonie\ContentContext\Domain\Enum\NewsStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\News\NewsEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\NewsCategory\NewsCategoryEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Tag\TagEntity;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class PublicNewsTest extends WebTestCase
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

    public function testPublicListShowsPublishedNewsInPublicationOrderAndPaginates(): void
    {
        $client = $this->clientWithSchema();
        [$justice] = $this->createNewsDataset();

        $client->request('GET', '/actualites', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Actualités');
        self::assertStringContainsString('Actualité récente', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Actualité brouillon', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('/actualites?page=2', (string) $client->getResponse()->getContent());

        $client->request('GET', '/actualites?page=2', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Actualité autre catégorie', (string) $client->getResponse()->getContent());

        $client->request('GET', '/actualites?categorie=' . $justice->getSlug(), server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Actualité récente', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Actualité autre catégorie', (string) $client->getResponse()->getContent());
    }

    public function testPublishedNewsDetailRendersRichContentCoverAndFallback(): void
    {
        $client = $this->clientWithSchema();
        [$news] = $this->createNewsDataset();

        $client->request('GET', '/actualites/' . $news->getSlug(), server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Actualité récente');
        self::assertStringContainsString('Texte public sanitizé', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('/uploads/content/covers/public-news.jpg', (string) $client->getResponse()->getContent());

        $client->request('GET', '/actualites/actualite-ancienne', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertStringContainsString('/assets/default.jpg', (string) $client->getResponse()->getContent());
    }

    public function testDraftAndUnknownNewsAreNotPubliclyAccessible(): void
    {
        $client = $this->clientWithSchema();
        $this->createNewsDataset();

        $client->request('GET', '/actualites/actualite-brouillon', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $client->request('GET', '/actualites/does-not-exist', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function clientWithSchema(): KernelBrowser
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($entityManager))->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $entityManager->persist(new Reglages('app_title', 'Application title', 'Avocat CI', 'text'));
        $entityManager->persist(new Reglages('app_paginate_limit', 'Pagination', '2', 'number'));
        $entityManager->persist((new Images())->setName('app_favicon')->setLabel('Favicon'));
        $entityManager->persist((new Currencies())->setCurrencyCode('XOF')->setCurrencyName('Franc CFA')->setRightSymbol('FCFA')->setDecimalPlace(0)->setIsActive(true));
        $entityManager->flush();
        $client->disableReboot();

        return $client;
    }

    /** @return array{NewsEntity, NewsCategoryEntity} */
    private function createNewsDataset(): array
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $category = (new NewsCategoryEntity())->setName('Justice')->setSlug('justice');
        $otherCategory = (new NewsCategoryEntity())->setName('Institution')->setSlug('institution');
        $tag = (new TagEntity())->setName('Profession')->setSlug('profession');
        $cover = (new MediaEntity())
            ->setOriginalName('public-news.jpg')
            ->setStorageName('news-cover.jpg')
            ->setMimeType('image/jpeg')
            ->setSize(120)
            ->setWidth(1200)
            ->setHeight(675)
            ->setStoragePath('content/covers/public-news.jpg');
        $entityManager->persist($category);
        $entityManager->persist($otherCategory);
        $entityManager->persist($tag);
        $entityManager->persist($cover);
        $entityManager->flush();

        $recent = $this->news('Actualité récente', 'actualite-recente', NewsStatus::PUBLISHED, new DateTimeImmutable('-1 day'), [$category], [$tag], $cover->getId());
        $old = $this->news('Actualité ancienne', 'actualite-ancienne', NewsStatus::PUBLISHED, new DateTimeImmutable('-2 days'), [$category]);
        $other = $this->news('Actualité autre catégorie', 'actualite-autre-categorie', NewsStatus::PUBLISHED, new DateTimeImmutable('-3 days'), [$otherCategory]);
        $draft = $this->news('Actualité brouillon', 'actualite-brouillon', NewsStatus::DRAFT, null, [$category]);

        foreach ([$recent, $old, $other, $draft] as $news) {
            $entityManager->persist($news);
        }
        $entityManager->flush();

        return [$recent, $category];
    }

    /** @param list<NewsCategoryEntity> $categories @param list<TagEntity> $tags */
    private function news(string $title, string $slug, NewsStatus $status, ?DateTimeImmutable $publishedAt, array $categories = [], array $tags = [], ?int $coverMediaId = null): NewsEntity
    {
        return (new NewsEntity())
            ->setTitle($title)
            ->setSlug($slug)
            ->setExcerpt('Résumé public')
            ->setBody('<h2>' . $title . '</h2><p>Texte public sanitizé.</p>')
            ->setStatus($status)
            ->setPublishedAt($publishedAt)
            ->setCoverMediaId($coverMediaId)
            ->replaceCategories($categories)
            ->replaceTags($tags);
    }
}
