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
use Websymphonie\ContentContext\Domain\Enum\PageGroup;
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

        foreach (['politique-cookies', 'a-propos', 'page-sans-date', 'page-inconnue'] as $slug) {
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

    public function testLegalPagesExposePublishedContextualNavigationWithActivePage(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();

        $client->request('GET', '/informations/mentions-legales', server: ['HTTPS' => 'on']);

        self::assertSelectorExists('aside[aria-label="Navigation : Informations légales"]');
        self::assertSelectorTextContains('aside[aria-label="Navigation : Informations légales"]', 'Mentions légales');
        self::assertSelectorTextContains('aside[aria-label="Navigation : Informations légales"]', 'Politique de confidentialité');
        self::assertSelectorTextContains('aside[aria-label="Navigation : Informations légales"]', 'Conditions générales d’utilisation');
        self::assertSelectorExists('aside[aria-label="Navigation : Informations légales"] a[aria-current="page"]');
        self::assertSame([
            'Mentions légales',
            'Aide juridique',
            'Politique de confidentialité',
            'Conditions générales d’utilisation',
        ], $client->getCrawler()->filter('aside[aria-label="Navigation : Informations légales"] a')->each(static fn ($node): string => trim($node->text())));
        self::assertStringNotContainsString('/informations/politique-cookies', $client->getCrawler()->filter('aside[aria-label="Navigation : Informations légales"]')->html());
        self::assertStringNotContainsString('/informations/politique-suppression-compte', $client->getCrawler()->filter('aside[aria-label="Navigation : Informations légales"]')->html());
    }

    public function testAccountPageDoesNotUseTheLegalContextualNavigation(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();

        $client->request('GET', '/informations/politique-suppression-compte', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorNotExists('aside[aria-label^="Navigation :"]');
    }

    public function testPageWithoutGroupDoesNotUseContextualNavigation(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();

        $client->request('GET', '/informations/page-sans-groupe', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorNotExists('aside[aria-label^="Navigation :"]');
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

        $publishedWithCover = $this->page('Conditions générales d’utilisation', 'conditions-generales-utilisation', PageStatus::PUBLISHED, new DateTimeImmutable('-2 days'), $cover->getId(), PageGroup::LEGAL, 30);
        $confidentiality = $this->page('Politique de confidentialité', 'politique-confidentialite', PageStatus::PUBLISHED, new DateTimeImmutable('-1 day'), null, PageGroup::LEGAL, 20);
        $legalNotice = $this->page('Mentions légales', 'mentions-legales', PageStatus::PUBLISHED, new DateTimeImmutable('-3 days'), null, PageGroup::LEGAL, 10);
        $sameOrder = $this->page('Aide juridique', 'aide-juridique', PageStatus::PUBLISHED, new DateTimeImmutable('-4 days'), null, PageGroup::LEGAL, 20);
        $accountPolicy = $this->page('Politique de suppression de compte', 'politique-suppression-compte', PageStatus::PUBLISHED, new DateTimeImmutable('-5 days'), $cover->getId(), PageGroup::ACCOUNT, 10);
        $draft = $this->page('Politique de cookies', 'politique-cookies', PageStatus::DRAFT, null, $cover->getId(), PageGroup::LEGAL, 40);
        $barDraft = $this->page('À propos du Barreau', 'a-propos', PageStatus::DRAFT, null, null, PageGroup::BAR, 10);
        $withoutPublicationDate = $this->page('Page sans date', 'page-sans-date', PageStatus::PUBLISHED, null);
        $withoutGroup = $this->page('Page sans groupe', 'page-sans-groupe', PageStatus::PUBLISHED, new DateTimeImmutable('-5 days'));

        foreach ([$publishedWithCover, $confidentiality, $legalNotice, $sameOrder, $accountPolicy, $draft, $barDraft, $withoutPublicationDate, $withoutGroup] as $page) {
            $entityManager->persist($page);
        }
        $entityManager->flush();

        return [$publishedWithCover, $confidentiality];
    }

    private function page(string $title, string $slug, PageStatus $status, ?DateTimeImmutable $publishedAt, ?int $coverMediaId = null, ?PageGroup $group = null, int $sortOrder = 0): PageEntity
    {
        return (new PageEntity())
            ->setTitle($title)
            ->setSlug($slug)
            ->setContent('<h2>' . $title . '</h2><p>Contenu public de la page.</p><script>alert(1)</script>')
            ->setStatus($status)
            ->setPublishedAt($publishedAt)
            ->setCoverMediaId($coverMediaId)
            ->setGroup($group)
            ->setSortOrder($sortOrder);
    }
}
