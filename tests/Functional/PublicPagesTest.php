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
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\Batonnier\BatonnierMandateEntity;
use Websymphonie\ContentContext\Infrastructure\Persistence\Doctrine\Entity\CouncilMember\CouncilMemberEntity;
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
        self::assertSelectorTextContains('.rich-content', 'Editeur du Site');
        self::assertSelectorTextContains('.rich-content', 'HARRELL GROUP');
        self::assertSelectorTextContains('.rich-content', 'CINETCORE-VENAME');
        self::assertStringNotContainsString('Contenu de démonstration', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Couverture de Mentions légales', (string) $client->getResponse()->getContent());
    }

    public function testMigratedPrivacyPageUsesTheExistingCanonicalUrl(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();

        $client->request('GET', '/informations/politique-confidentialite', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Vie privée');
        self::assertSelectorTextContains('.rich-content', 'Données traitées');
        self::assertSelectorTextContains('.rich-content', 'Données de connexion');
        self::assertSelectorTextContains('.rich-content', 'portabilité de vos données');
        self::assertSelectorExists('.rich-content a[href="mailto:info@ordredesavocats.ci"]');
        self::assertStringNotContainsString('Contenu de démonstration', (string) $client->getResponse()->getContent());
    }

    public function testDraftUnknownAndUnpublishedPagesReturnNotFound(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();

        foreach (['politique-cookies', 'bar-draft', 'carpa', 'page-sans-date', 'page-inconnue'] as $slug) {
            $client->request('GET', '/informations/' . $slug, server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND, $slug);
        }

        $client->request('GET', '/carpa/presentation', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testPublishedCarpaPageUsesCanonicalBarSurfaces(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $carpaPage = $entityManager->getRepository(PageEntity::class)->findOneBy(['slug' => 'presentation', 'editorialGroup' => PageGroup::CARPA]);
        self::assertInstanceOf(PageEntity::class, $carpaPage);

        $carpaPage->setContent('<p>La Caisse Autonome de Règlement Pécuniaire des Avocats participe au cadre institutionnel du Barreau.</p><p><a href="/contact">Contacter le Barreau</a></p>')
            ->setStatus(PageStatus::PUBLISHED)
            ->setPublishedAt(new DateTimeImmutable('-1 day'));
        $entityManager->flush();

        $client->request('GET', '/carpa/presentation', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'CARPA');
        self::assertSelectorTextContains('nav[aria-label="Fil d’Ariane"]', 'La CARPA');

        $client->request('GET', '/le-barreau', server: ['HTTPS' => 'on']);
        self::assertStringNotContainsString('/carpa/presentation', (string) $client->getResponse()->getContent());
        self::assertSelectorExists('nav[aria-label="Navigation principale"] a[href="/carpa"]');
        self::assertSelectorExists('nav[aria-label="Navigation mobile"] a[href="/carpa"]');
    }

    public function testPublishedBarPageUsesCanonicalRouteAndContextualNavigation(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->page('Le Bâtonnier', 'le-batonnier', PageStatus::PUBLISHED, new DateTimeImmutable('-2 days'), null, PageGroup::BAR, 30));
        $entityManager->persist($this->page('Conseil de l’Ordre', 'conseil-de-l-ordre', PageStatus::PUBLISHED, new DateTimeImmutable('-3 days'), null, PageGroup::BAR, 40));
        $entityManager->flush();

        $client->request('GET', '/le-barreau/presentation', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Présentation du Barreau');
        self::assertSelectorTextContains('nav[aria-label="Fil d’Ariane"]', 'Le Barreau');
        self::assertSelectorExists('aside[aria-label="Navigation : Le Barreau"]');
        self::assertSame([
            'Présentation du Barreau',
            'Historique du Barreau',
            'Le Bâtonnier',
            'Conseil de l’Ordre',
        ], $client->getCrawler()->filter('aside[aria-label="Navigation : Le Barreau"] a')->each(static fn ($node): string => trim($node->text())));
        self::assertSame('/le-barreau/presentation', $client->getCrawler()->filter('aside[aria-label="Navigation : Le Barreau"] a[aria-current="page"]')->attr('href'));
        self::assertStringContainsString('/le-barreau/historique', $client->getCrawler()->filter('aside[aria-label="Navigation : Le Barreau"]')->html());
        self::assertStringNotContainsString('bar-draft', $client->getCrawler()->filter('aside[aria-label="Navigation : Le Barreau"]')->html());
        self::assertSame('/le-barreau', $client->getCrawler()->filter('nav[aria-label="Fil d’Ariane"] a')->last()->attr('href'));
        self::assertSelectorNotExists('a[href="/espace/ressources/fonds-de-solidarite"]');

        $desktopMenuItems = $client->getCrawler()
            ->filter('nav[aria-label="Navigation principale"] div.hidden.items-center')
            ->children()
            ->each(static fn ($node): string => trim($node->filter('summary')->count() > 0 ? $node->filter('summary')->text() : $node->text()));
        self::assertSame(['Accueil', 'Le Barreau', 'La CARPA'], array_slice($desktopMenuItems, 0, 3));
        self::assertSame(['Vue d’ensemble', 'Présentation du Barreau', 'Historique du Barreau', 'Le Bâtonnier', 'Conseil de l’Ordre'], $client->getCrawler()->filter('nav[aria-label="Navigation principale"] div.hidden.items-center details a')->each(static fn ($node): string => trim($node->text())));
        self::assertSame('/le-barreau/presentation', $client->getCrawler()->filter('nav[aria-label="Navigation principale"] div.hidden.items-center details a[aria-current="page"]')->attr('href'));
        self::assertStringNotContainsString('Fonds de Solidarité', $client->getCrawler()->filter('nav[aria-label="Navigation principale"] div.hidden.items-center details')->text());
        self::assertStringNotContainsString('CARPA', $client->getCrawler()->filter('nav[aria-label="Navigation principale"] div.hidden.items-center details')->text());
        self::assertSame(['Vue d’ensemble', 'Présentation du Barreau', 'Historique du Barreau', 'Le Bâtonnier', 'Conseil de l’Ordre'], $client->getCrawler()->filter('nav[aria-label="Navigation mobile"] details a')->each(static fn ($node): string => trim($node->text())));

        $client->request('GET', '/le-barreau/historique', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Historique du Barreau');
        self::assertSelectorTextContains('.rich-content', 'Contenu public de la page.');
        self::assertSelectorExists('aside[aria-label="Navigation : Le Barreau"] a[aria-current="page"][href="/le-barreau/historique"]');
        self::assertSame('/le-barreau', $client->getCrawler()->filter('nav[aria-label="Fil d’Ariane"] a')->last()->attr('href'));
        self::assertStringContainsString('/uploads/content/covers/public-page.jpg', (string) $client->getResponse()->getContent());
    }

    public function testLbcPageUsesDedicatedCanonicalUrlAndPublicNavigation(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->page(
            'Lutte contre le Blanchiment des Capitaux (LBC/FT/FP)',
            'lbc-ft-fp',
            PageStatus::PUBLISHED,
            new DateTimeImmutable('-1 day'),
            null,
            PageGroup::LBC,
            10,
        ));
        $entityManager->flush();

        $client->request('GET', '/le-barreau', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertStringNotContainsString('Lutte contre le Blanchiment', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('/le-barreau/lbc-ft-fp', (string) $client->getResponse()->getContent());

        $client->request('GET', '/le-barreau/presentation', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorExists('aside[aria-label="Navigation : Le Barreau"]');
        self::assertStringNotContainsString('LBC/FT/FP', $client->getCrawler()->filter('aside[aria-label="Navigation : Le Barreau"]')->text());
        self::assertStringNotContainsString('LBC/FT/FP', $client->getCrawler()->filter('nav[aria-label="Navigation principale"] details')->text());
        self::assertStringNotContainsString('LBC/FT/FP', $client->getCrawler()->filter('nav[aria-label="Navigation mobile"] details')->text());
        self::assertSelectorExists('nav[aria-label="Navigation principale"] a[href="/lbc-ft-fp"]');
        self::assertSelectorExists('nav[aria-label="Navigation mobile"] a[href="/lbc-ft-fp"]');

        $client->request('GET', '/lbc-ft-fp', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Lutte contre le Blanchiment des Capitaux (LBC/FT/FP)');
        self::assertSelectorExists('nav[aria-label="Navigation principale"] a[aria-current="page"][href="/lbc-ft-fp"]');
        self::assertSelectorExists('nav[aria-label="Navigation mobile"] a[aria-current="page"][href="/lbc-ft-fp"]');
        self::assertSelectorNotExists('aside[aria-label="Navigation : Le Barreau"]');
        self::assertSelectorNotExists('aside[aria-label="Navigation : LBC/FT/FP"]');

        $client->request('GET', '/le-barreau/lbc-ft-fp', server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/lbc-ft-fp', Response::HTTP_MOVED_PERMANENTLY);

        $client->request('GET', '/informations/lbc-ft-fp', server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/lbc-ft-fp', Response::HTTP_MOVED_PERMANENTLY);
    }

    public function testPublishedSolidarityFundPageRendersMemberResourcesCtaAndBarSidebar(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $fundPage = $entityManager->getRepository(PageEntity::class)->findOneBy(['slug' => 'fonds-de-solidarite']);
        self::assertInstanceOf(PageEntity::class, $fundPage);
        $fundPage->setContent('<h2>La politique CARE</h2><ol><li>La santé pour tous</li><li>La lutte contre la précarité</li><li>L’engagement en faveur des droits</li><li>Le bien-être des avocats</li><li>L’écoute et le dialogue</li></ol><p>Le dispositif Yako accompagne les familles lors d’un deuil. Contactez le Barreau via <a href="/contact">le formulaire de contact</a>.</p>')
            ->setStatus(PageStatus::PUBLISHED)
            ->setPublishedAt(new DateTimeImmutable('-1 day'));
        $entityManager->flush();

        $client->request('GET', '/le-barreau/fonds-de-solidarite', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorTextContains('h1', 'Fonds de Solidarité');
        self::assertSelectorTextContains('.rich-content', 'La politique CARE');
        self::assertSelectorTextContains('.rich-content', 'L’écoute et le dialogue');
        self::assertSelectorTextContains('.rich-content', 'Yako');
        self::assertSelectorExists('.rich-content a[href="/contact"]');
        self::assertSelectorExists('aside[aria-label="Navigation : Le Barreau"] a[aria-current="page"][href="/le-barreau/fonds-de-solidarite"]');
        self::assertSelectorExists('a[href="/espace/ressources/fonds-de-solidarite"]');
        self::assertSelectorTextContains('a[href="/espace/ressources/fonds-de-solidarite"]', 'Accéder aux ressources');

        $client->request('GET', '/le-barreau', server: ['HTTPS' => 'on']);
        self::assertStringContainsString('/le-barreau/fonds-de-solidarite', (string) $client->getResponse()->getContent());
    }

    public function testBarreauHubListsPublishedBarPagesInEditorialOrder(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->page('Le Bâtonnier', 'le-batonnier', PageStatus::PUBLISHED, new DateTimeImmutable('-2 days'), null, PageGroup::BAR, 30));
        $entityManager->persist($this->page('Conseil de l’Ordre', 'conseil-de-l-ordre', PageStatus::PUBLISHED, new DateTimeImmutable('-3 days'), null, PageGroup::BAR, 40));
        $fundPage = $entityManager->getRepository(PageEntity::class)->findOneBy(['slug' => 'fonds-de-solidarite']);
        self::assertInstanceOf(PageEntity::class, $fundPage);
        $fundPage->setStatus(PageStatus::PUBLISHED)->setPublishedAt(new DateTimeImmutable('-1 day'))->setSortOrder(50);
        $carpaPage = $entityManager->getRepository(PageEntity::class)->findOneBy(['slug' => 'presentation', 'editorialGroup' => PageGroup::CARPA]);
        self::assertInstanceOf(PageEntity::class, $carpaPage);
        $carpaPage->setContent('<p>Présentation institutionnelle de la CARPA.</p>')
            ->setStatus(PageStatus::PUBLISHED)
            ->setPublishedAt(new DateTimeImmutable('-1 day'))
            ->setSortOrder(60);
        $entityManager->flush();

        $client->request('GET', '/le-barreau', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Le Barreau');
        self::assertSame([
            '/le-barreau/presentation',
            '/le-barreau/historique',
            '/le-barreau/le-batonnier',
            '/le-barreau/conseil-de-l-ordre',
            '/le-barreau/fonds-de-solidarite',
        ], $client->getCrawler()->filter('main a[href^="/le-barreau/"]')->each(static fn ($node): string => $node->attr('href')));
        self::assertStringNotContainsString('/le-barreau/bar-draft', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('/le-barreau/mentions-legales', (string) $client->getResponse()->getContent());
    }

    public function testBarreauHubShowsAnHonestEmptyState(): void
    {
        $client = $this->clientWithSchema();

        $client->request('GET', '/le-barreau', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Les contenus institutionnels seront publiés prochainement.');
        self::assertCount(0, $client->getCrawler()->filter('main a[href^="/le-barreau/"]'));
    }

    public function testBatonnierPageShowsCurrentStructuredMandateWhenAvailable(): void
    {
        $client = $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $page = $this->page('Le Bâtonnier', 'le-batonnier', PageStatus::PUBLISHED, new DateTimeImmutable('-1 day'), null, PageGroup::BAR, 10);
        $mandate = (new BatonnierMandateEntity())
            ->setFullName('Me Démonstration')
            ->setMandateStartedAt(new DateTimeImmutable('2025-01-01'))
            ->setMandateEndedAt(null)
            ->setSummary('Présentation institutionnelle du mandat.');
        $entityManager->persist($page);
        $entityManager->persist($mandate);
        $entityManager->flush();

        $client->request('GET', '/le-barreau/le-batonnier', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#batonnier-profile-title', 'Me Démonstration');
        self::assertSelectorTextContains('main', 'Présentation institutionnelle du mandat.');
        self::assertSelectorTextContains('main', 'Mandat depuis 2025');
    }

    public function testBatonnierPageRemainsValidWithoutCurrentMandate(): void
    {
        $client = $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $page = $this->page('Le Bâtonnier', 'le-batonnier', PageStatus::PUBLISHED, new DateTimeImmutable('-1 day'), null, PageGroup::BAR, 10);
        $entityManager->persist($page);
        $entityManager->persist((new BatonnierMandateEntity())
            ->setFullName('Ancien Bâtonnier')
            ->setMandateStartedAt(new DateTimeImmutable('2020-01-01'))
            ->setMandateEndedAt(new DateTimeImmutable('2024-01-01')));
        $entityManager->flush();

        $client->request('GET', '/le-barreau/le-batonnier', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('#batonnier-profile-title');
        self::assertSelectorTextContains('h1', 'Le Bâtonnier');
    }

    public function testCurrentBatonnierPageDoesNotReintroduceLegacyContactDetails(): void
    {
        $client = $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->page('Le Bâtonnier', 'le-batonnier', PageStatus::PUBLISHED, new DateTimeImmutable('-1 day'), null, PageGroup::BAR, 30));
        $entityManager->persist((new BatonnierMandateEntity())
            ->setFullName('Me Florence LOAN épse MESSAN')
            ->setMandateStartedAt(new DateTimeImmutable('2024-10-02'))
            ->setMandateEndedAt(null)
            ->setSummary('Première femme à diriger l’Ordre des Avocats de Côte d’Ivoire.'));
        $entityManager->flush();

        $client->request('GET', '/le-barreau/le-batonnier', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#batonnier-profile-title', 'Me Florence LOAN épse MESSAN');
        self::assertSelectorTextContains('main', 'Mandat depuis 2024');
        $content = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString('secretariat@ordredesavocats.ci', $content);
        self::assertStringNotContainsString('Maison de l’Avocat', $content);
        self::assertStringNotContainsString('Écrire au Bâtonnier', $content);
    }

    public function testCouncilPageShowsOnlyCurrentMembersInEditorialOrder(): void
    {
        $client = $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->page('Conseil de l’Ordre', 'conseil-de-l-ordre', PageStatus::PUBLISHED, new DateTimeImmutable('-1 day'), null, PageGroup::BAR, 30));
        $entityManager->persist((new CouncilMemberEntity())->setFullName('Me Binta Yao')->setFunction('Membre')->setSortOrder(20));
        $entityManager->persist((new CouncilMemberEntity())->setFullName('Me Alain Koffi')->setFunction('Secrétaire')->setSortOrder(10));
        $entityManager->persist((new CouncilMemberEntity())->setFullName('Ancien membre')->setFunction('Membre')->setSortOrder(1)->setMandateEndedAt(new DateTimeImmutable('2024-01-01')));
        $entityManager->flush();

        $client->request('GET', '/le-barreau/conseil-de-l-ordre', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#council-title', 'Le Conseil de l’Ordre');
        self::assertSame(['Me Alain Koffi', 'Me Binta Yao'], $client->getCrawler()->filter('section[aria-labelledby="council-title"] h3')->each(static fn ($node): string => trim($node->text())));
        self::assertStringNotContainsString('Ancien membre', (string) $client->getResponse()->getContent());
    }

    public function testCouncilPageRemainsValidWithoutCurrentMembers(): void
    {
        $client = $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->page('Conseil de l’Ordre', 'conseil-de-l-ordre', PageStatus::PUBLISHED, new DateTimeImmutable('-1 day'), null, PageGroup::BAR, 30));
        $entityManager->flush();

        $client->request('GET', '/le-barreau/conseil-de-l-ordre', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('#council-title');
        self::assertSelectorTextContains('h1', 'Conseil de l’Ordre');
    }

    public function testBarRouteRejectsDraftAndNonBarPages(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();

        foreach (['bar-draft', 'fonds-de-solidarite', 'bar-without-publication-date', 'mentions-legales'] as $slug) {
            $client->request('GET', '/le-barreau/' . $slug, server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND, $slug);
        }
    }

    public function testLegacyInformationRoutePermanentlyRedirectsPublishedBarPage(): void
    {
        $client = $this->clientWithSchema();
        $this->createPageDataset();

        $client->request('GET', '/informations/presentation', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_MOVED_PERMANENTLY);
        self::assertSame('/le-barreau/presentation', $client->getResponse()->headers->get('Location'));
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
        self::assertSame('Vie privée', trim($client->getCrawler()->filter('footer a[href="/informations/politique-confidentialite"]')->text()));
        self::assertSame('/le-barreau', $client->getCrawler()->filter('footer a')->first()->attr('href'));
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
        self::assertSelectorTextContains('aside[aria-label="Navigation : Informations légales"]', 'Vie privée');
        self::assertSelectorTextContains('aside[aria-label="Navigation : Informations légales"]', 'Conditions générales d’utilisation');
        self::assertSelectorExists('aside[aria-label="Navigation : Informations légales"] a[aria-current="page"]');
        self::assertSame([
            'Mentions légales',
            'Aide juridique',
            'Vie privée',
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
        $confidentiality = $this->page('Vie privée', 'politique-confidentialite', PageStatus::PUBLISHED, new DateTimeImmutable('-1 day'), null, PageGroup::LEGAL, 20)
            ->setContent('<h2>1. Données traitées &amp; Finalités</h2><h3>1.1. Données de connexion</h3><p>Nous conservons les informations du journal de serveur Web.</p><p>Vous pouvez demander la portabilité de vos données.</p><p><a href="mailto:info@ordredesavocats.ci">info@ordredesavocats.ci</a></p>');
        $legalNotice = $this->page('Mentions légales', 'mentions-legales', PageStatus::PUBLISHED, new DateTimeImmutable('-3 days'), null, PageGroup::LEGAL, 10)
            ->setContent('<h2>Editeur du Site</h2><p>Barreau de Côte d’Ivoire</p><h2>Conception et Réalisation du site</h2><p>HARRELL GROUP</p><h2>Hébergement</h2><p>CINETCORE-VENAME</p>');
        $sameOrder = $this->page('Aide juridique', 'aide-juridique', PageStatus::PUBLISHED, new DateTimeImmutable('-4 days'), null, PageGroup::LEGAL, 20);
        $accountPolicy = $this->page('Politique de suppression de compte', 'politique-suppression-compte', PageStatus::PUBLISHED, new DateTimeImmutable('-5 days'), $cover->getId(), PageGroup::ACCOUNT, 10);
        $draft = $this->page('Politique de cookies', 'politique-cookies', PageStatus::DRAFT, null, $cover->getId(), PageGroup::LEGAL, 40);
        $barPresentation = $this->page('Présentation du Barreau', 'presentation', PageStatus::PUBLISHED, new DateTimeImmutable('-6 days'), null, PageGroup::BAR, 10);
        $barHistory = $this->page('Historique du Barreau', 'historique', PageStatus::PUBLISHED, new DateTimeImmutable('-7 days'), $cover->getId(), PageGroup::BAR, 20);
        $barDraft = $this->page('Page BAR brouillon', 'bar-draft', PageStatus::DRAFT, null, null, PageGroup::BAR, 30);
        $fundDraft = $this->page('Fonds de Solidarité', 'fonds-de-solidarite', PageStatus::DRAFT, null, null, PageGroup::BAR, 20)->setContent('');
        $carpaDraft = $this->page('CARPA', 'presentation', PageStatus::DRAFT, null, null, PageGroup::CARPA, 30)->setContent('');
        $barWithoutPublicationDate = $this->page('Page BAR sans date', 'bar-without-publication-date', PageStatus::PUBLISHED, null, null, PageGroup::BAR, 40);
        $withoutPublicationDate = $this->page('Page sans date', 'page-sans-date', PageStatus::PUBLISHED, null);
        $withoutGroup = $this->page('Page sans groupe', 'page-sans-groupe', PageStatus::PUBLISHED, new DateTimeImmutable('-5 days'));

        foreach ([$publishedWithCover, $confidentiality, $legalNotice, $sameOrder, $accountPolicy, $draft, $barPresentation, $barHistory, $barDraft, $fundDraft, $carpaDraft, $barWithoutPublicationDate, $withoutPublicationDate, $withoutGroup] as $page) {
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
