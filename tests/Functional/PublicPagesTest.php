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
use Websymphonie\ContentContext\Domain\Enum\PageStatus;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Page\PageEntity;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class PublicPagesTest extends WebTestCase
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

    public function testPublishedPageWithCoverRendersSanitizedRichContent(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();

        $client->request('GET', '/informations/conditions-generales-utilisation', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Conditions générales d’utilisation');
        self::assertStringContainsString('Contenu public de la page.', (string) $client->getResponse()->getContent());
        self::assertStringContainsString('/uploads/content/covers/public-page.jpg', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('<script>', $client->getCrawler()->filter('.rich-content')->html());
    }

    public function testPublishedPageWithoutCoverRemainsComplete(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();

        $client->request('GET', '/informations/mentions-legales', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Mentions légales');
        self::assertStringContainsString('Contenu public de la page.', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Couverture de Mentions légales', (string) $client->getResponse()->getContent());
    }

    public function testDraftUnknownAndUnpublishedPagesReturnNotFound(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();

        foreach (['politique-cookies', 'page-sans-date', 'page-inconnue'] as $slug) {
            $client->request('GET', '/informations/' . $slug, server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND, $slug);
        }
    }

    public function testFooterContainsOnlyTheExpectedPublishedLegalPages(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();

        $client->request('GET', '/informations/mentions-legales', server: ['HTTPS' => 'on']);
        $content = (string) $client->getResponse()->getContent();

        self::assertStringContainsString('/informations/conditions-generales-utilisation', $content);
        self::assertStringContainsString('/informations/politique-confidentialite', $content);
        self::assertStringContainsString('/informations/mentions-legales', $content);
        self::assertStringNotContainsString('/informations/politique-cookies', $content);
        self::assertStringNotContainsString('/informations/politique-suppression-compte', $content);
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

    /** @return array{PageEntity, PageEntity} */
    private function createPageDataset(): array
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $cover = (new MediaEntity())
            ->setOriginalName('public-page.jpg')
            ->setStorageName('page-cover.jpg')
            ->setMimeType('image/jpeg')
            ->setSize(120)
            ->setWidth(1200)
            ->setHeight(600)
            ->setStoragePath('content/covers/public-page.jpg');
        $entityManager->persist($cover);
        $entityManager->flush();

        $publishedWithCover = $this->page('Conditions générales d’utilisation', 'conditions-generales-utilisation', PageStatus::PUBLISHED, new DateTimeImmutable('-2 days'), $cover->getId());
        $confidentiality = $this->page('Politique de confidentialité', 'politique-confidentialite', PageStatus::PUBLISHED, new DateTimeImmutable('-1 day'));
        $legalNotice = $this->page('Mentions légales', 'mentions-legales', PageStatus::PUBLISHED, new DateTimeImmutable('-3 days'));
        $draft = $this->page('Politique de cookies', 'politique-cookies', PageStatus::DRAFT, null, $cover->getId());
        $withoutPublicationDate = $this->page('Page sans date', 'page-sans-date', PageStatus::PUBLISHED, null);

        foreach ([$publishedWithCover, $confidentiality, $legalNotice, $draft, $withoutPublicationDate] as $page) {
            $entityManager->persist($page);
        }
        $entityManager->flush();

        return [$publishedWithCover, $confidentiality];
    }

    private function page(string $title, string $slug, PageStatus $status, ?DateTimeImmutable $publishedAt, ?int $coverMediaId = null): PageEntity
    {
        return (new PageEntity())
            ->setTitle($title)
            ->setSlug($slug)
            ->setContent('<h2>' . $title . '</h2><p>Contenu public de la page.</p><script>alert(1)</script>')
            ->setStatus($status)
            ->setPublishedAt($publishedAt)
            ->setCoverMediaId($coverMediaId);
    }
}
