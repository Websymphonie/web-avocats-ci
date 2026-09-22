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
use Websymphonie\ContentContext\Domain\Enum\EditorialVideoStatus;
use Websymphonie\ContentContext\Domain\Enum\VideoProvider;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EditorialVideo\EditorialVideoEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\EditorialVideoCategory\EditorialVideoCategoryEntity;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class PublicEditorialVideosTest extends WebTestCase
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

    public function testPublicListingAndHomepageUsePublishedEditorialVideos(): void
    {
        $client = $this->clientWithSchema();
        $this->createVideoDataset();

        $client->request('GET', '/videos', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Vidéos');
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Vidéo récente', $content);
        self::assertStringContainsString('Vidéo ancienne', $content);
        self::assertStringNotContainsString('Vidéo brouillon', $content);
        self::assertStringContainsString('/videos/video-recente', $content);
        self::assertStringContainsString('Toutes les vidéos', $content);
        self::assertStringContainsString('Interviews', $content);
        $recentPosition = strpos($content, 'Vidéo récente');
        $oldPosition = strpos($content, 'Vidéo ancienne');
        self::assertNotFalse($recentPosition);
        self::assertNotFalse($oldPosition);
        self::assertLessThan($oldPosition, $recentPosition);

        $client->request('GET', '/', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('href="/videos"', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('>Vidéos</h3>', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('>Publications</h3>', (string) $client->getResponse()->getContent());
    }

    public function testPublishedYoutubeDetailUsesSafeEmbedWithoutAutoplay(): void
    {
        $client = $this->clientWithSchema();
        $this->createVideoDataset();

        $client->request('GET', '/videos/video-recente', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('youtube-nocookie.com/embed/abcDEF_123', $content);
        self::assertStringContainsString('title="Lecture vidéo : Vidéo récente"', $content);
        self::assertStringNotContainsString('autoplay', $content);
        self::assertStringContainsString('Description publique', $content);
        self::assertStringContainsString('Catégories vidéo', $content);
        self::assertStringContainsString('aria-current="page"', $content);
    }

    public function testPublicCategoryFilterUsesTheVideoCategorySlug(): void
    {
        $client = $this->clientWithSchema();
        $this->createVideoDataset();

        $client->request('GET', '/videos?category=interviews', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Vidéo récente', $content);
        self::assertStringNotContainsString('Vidéo ancienne', $content);
        self::assertStringContainsString('aria-current="page"', $content);
    }

    public function testDraftAndUnknownVideosAreNotPubliclyAccessible(): void
    {
        $client = $this->clientWithSchema();
        $this->createVideoDataset();

        $client->request('GET', '/videos/video-brouillon', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $client->request('GET', '/videos/inconnue', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
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

    private function createVideoDataset(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $interviews = (new EditorialVideoCategoryEntity())->setName('Interviews')->setSlug('interviews');
        $conferences = (new EditorialVideoCategoryEntity())->setName('Conférences')->setSlug('conferences');
        $entityManager->persist($interviews);
        $entityManager->persist($conferences);
        $recent = $this->video('Vidéo récente', 'video-recente', EditorialVideoStatus::PUBLISHED, new DateTimeImmutable('-1 day'))->setCategory($interviews);
        $old = $this->video('Vidéo ancienne', 'video-ancienne', EditorialVideoStatus::PUBLISHED, new DateTimeImmutable('-2 days'))->setCategory($conferences);
        $draft = $this->video('Vidéo brouillon', 'video-brouillon', EditorialVideoStatus::DRAFT, null)->setCategory($interviews);

        foreach ([$recent, $old, $draft] as $video) {
            $entityManager->persist($video);
        }
        $entityManager->flush();
    }

    private function video(string $title, string $slug, EditorialVideoStatus $status, ?DateTimeImmutable $publishedAt): EditorialVideoEntity
    {
        return (new EditorialVideoEntity())
            ->setTitle($title)
            ->setSlug($slug)
            ->setExcerpt('Résumé public')
            ->setDescription('<p>Description publique</p>')
            ->setProvider(VideoProvider::YOUTUBE)
            ->setVideoUrl('https://www.youtube.com/watch?v=abcDEF_123')
            ->setExternalVideoId('abcDEF_123')
            ->setStatus($status)
            ->setPublishedAt($publishedAt);
    }
}
