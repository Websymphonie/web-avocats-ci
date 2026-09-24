<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LawyerContext\Application\Usecase\Query\GetPublicLawyerDirectoryQuery;
use Websymphonie\LawyerContext\Application\Usecase\Query\GetPublicCabinetDirectoryQuery;
use Websymphonie\LawyerContext\Application\Usecase\Query\GetPublicLawyerProfileQuery;
use Websymphonie\LawyerContext\Application\Usecase\Query\GetPublicCabinetProfileQuery;
use Websymphonie\LawyerContext\Domain\Model\LawyerDirectoryEntry;
use Websymphonie\LawyerContext\Domain\Model\LawyerDirectoryResult;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\Cabinet\CabinetEntity;
use Websymphonie\LawyerContext\Infrastructure\Persistence\Doctrine\Entity\LawyerProfile\LawyerProfileEntity;
use Websymphonie\MediaContext\Infrastructure\Persistence\Doctrine\Entity\MediaEntity;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryBus;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class PublicLawyerDirectoryTest extends WebTestCase
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

    public function testDirectoryFiltersOnlyEligibleLawyersAndDoesNotExposeAccountData(): void
    {
        $client = $this->clientWithSchema();
        $this->createDirectoryDataset();

        $client->request('GET', '/avocats', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Trouver un avocat');
        self::assertSelectorExists('form[method="get"] input[name="name"]');
        self::assertSelectorExists('form[method="get"] input[name="location"]');
        self::assertSelectorExists('#directory-cabinet-search-desktop');
        self::assertSelectorExists('a[aria-current="page"]');
        self::assertSelectorTextContains('aside', 'Cabinets');
        self::assertSelectorTextContains('aside', 'Cabinet du Centre');
        self::assertSelectorTextNotContains('aside', 'Cabinet Ivoire');
        self::assertSelectorExists('article a[href^="/avocats/"]');
        self::assertSelectorTextContains('body', '17 résultats');

        $content = (string) $client->getResponse()->getContent();
        foreach (['private@example.test', 'suspended@example.test', 'disabled@example.test', 'non-lawyer@example.test', 'ROLE_AVOCAT'] as $privateData) {
            self::assertStringNotContainsString($privateData, $content);
        }
        self::assertStringNotContainsString('Profil privé', $content);
        self::assertStringNotContainsString('Avocat suspendu', $content);
        self::assertStringNotContainsString('Compte désactivé', $content);
        self::assertStringNotContainsString('Compte sans rôle avocat', $content);
        self::assertStringNotContainsString('Rôle avocat approchant', $content);
        self::assertStringNotContainsString('Profil privé sans compte', $content);
        self::assertStringNotContainsString('Profil suspendu sans compte', $content);
        $client->request('GET', '/avocats?name=awa', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('article', 'Awa Kouassi');
        self::assertSelectorTextNotContains('article', 'Mariam N\'Dri');
        self::assertStringContainsString('/uploads/institution/lawyers/directory-portrait.jpg', (string) $client->getResponse()->getContent());

        $client->request('GET', '/avocats?name=historique', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('article', 'Avocate historique autonome');

        $client->request('GET', '/avocats?cabinet=ivoire', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('article', 'Awa Kouassi');
        self::assertSelectorTextContains('article', 'Cabinet Ivoire');

        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $publicCabinet = $entityManager->getRepository(CabinetEntity::class)->findOneBy(['name' => 'Cabinet du Centre']);
        $privateCabinet = $entityManager->getRepository(CabinetEntity::class)->findOneBy(['name' => 'Cabinet Ivoire']);
        self::assertInstanceOf(CabinetEntity::class, $publicCabinet);
        self::assertInstanceOf(CabinetEntity::class, $privateCabinet);
        $publicCabinetUuid = (string) $publicCabinet->getUuidAsString();

        $client->request('GET', '/avocats?cabinet=' . $publicCabinetUuid, server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', '2 résultats');
        self::assertSelectorTextContains('body', 'Mariam N\'Dri');
        self::assertSelectorTextNotContains('body', 'Awa Kouassi');
        self::assertSelectorExists('a[href="/avocats?cabinet=' . $publicCabinetUuid . '"][aria-current="page"]');
        self::assertSelectorExists('form input[type="hidden"][name="cabinet"][value="' . $publicCabinetUuid . '"]');

        $client->request('GET', '/avocats?name=mariam&cabinet=' . $publicCabinetUuid . '&location=Bouaké', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', '1 résultat');
        self::assertSelectorTextContains('article', 'Mariam N\'Dri');

        $client->request('GET', '/avocats?cabinet=' . $privateCabinet->getUuidAsString(), server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#directory-empty-title', 'Aucun avocat ne correspond à votre recherche');
        self::assertSelectorNotExists('article');

        $client->request('GET', '/avocats?location=abidjan', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('article', 'Awa Kouassi');
        self::assertSelectorTextNotContains('article', 'Mariam N\'Dri');

        $client->request('GET', '/avocats?name=awa&cabinet=ivoire&location=abidjan', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSame(1, static::getContainer()->get(QueryBus::class)->handle(new GetPublicLawyerDirectoryQuery(
            name: 'awa', cabinet: 'ivoire', location: 'abidjan',
        ))->totalItemCount);

        $client->request('GET', '/avocats?name=avocat', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertStringContainsString('href="/avocats?name=avocat&amp;page=2"', (string) $client->getResponse()->getContent());
    }

    public function testDirectoryPaginatesInStableNameOrderAndShowsAnEmptyState(): void
    {
        $client = $this->clientWithSchema();
        $this->createDirectoryDataset();

        $client->request('GET', '/avocats?page=2', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', '17 résultats');
        $namesOnSecondPage = $client->getCrawler()->filter('article h3')->each(static fn ($node): string => trim($node->text()));
        self::assertContains('Mariam N\'Dri', $namesOnSecondPage);

        $client->request('GET', '/avocats?name=ce-nom-nexiste-pas', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#directory-empty-title', 'Aucun avocat ne correspond à votre recherche');
        self::assertSelectorExists('a[href="/avocats"]');
        self::assertSelectorExists('input[name="name"][value="ce-nom-nexiste-pas"]');
    }

    public function testCabinetFilterIsPreservedAcrossPaginationAndCombinedWithName(): void
    {
        $client = $this->clientWithSchema();
        $this->createDirectoryDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $cabinet = $entityManager->getRepository(CabinetEntity::class)->findOneBy(['name' => 'Cabinet du Centre']);
        self::assertInstanceOf(CabinetEntity::class, $cabinet);

        for ($index = 1; $index <= 13; ++$index) {
            $this->createLawyer(
                sprintf('directory-page-%02d@example.test', $index),
                sprintf('Annuaire avocat %02d', $index),
                true,
                true,
                'ACTIVE',
                $cabinet,
            );
        }
        $entityManager->flush();

        $cabinetUuid = (string) $cabinet->getUuidAsString();
        $client->request('GET', '/avocats?cabinet=' . $cabinetUuid . '&name=annuaire&location=Bouaké', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', '13 résultats');
        $paginationLinks = $client->getCrawler()->filter('nav[aria-label="Pagination"] a')->each(static fn ($node): string => (string) $node->attr('href'));
        $pageTwoLink = null;
        foreach ($paginationLinks as $paginationLink) {
            if (str_contains($paginationLink, 'page=2')) {
                $pageTwoLink = $paginationLink;
                break;
            }
        }
        self::assertNotNull($pageTwoLink);
        parse_str((string) parse_url($pageTwoLink, PHP_URL_QUERY), $pageTwoParameters);
        self::assertSame(['cabinet' => $cabinetUuid, 'name' => 'annuaire', 'location' => 'Bouaké', 'page' => '2'], $pageTwoParameters);

        $client->request('GET', '/avocats?cabinet=' . $cabinetUuid . '&name=annuaire&location=Bouaké&page=2', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', '13 résultats');
        self::assertSelectorTextContains('article', 'Annuaire avocat 13');
    }

    public function testCabinetSidebarContainsOnlyEligiblePublicCabinetsInNameOrder(): void
    {
        $client = $this->clientWithSchema();
        $this->createDirectoryDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $earlyCabinet = (new CabinetEntity())->setName('Cabinet Abidjan')->setDirectoryVisible(true);
        $noMemberCabinet = (new CabinetEntity())->setName('Cabinet sans membre')->setDirectoryVisible(true);
        $ineligibleCabinet = (new CabinetEntity())->setName('Cabinet sans avocat publiable')->setDirectoryVisible(true);
        $entityManager->persist($earlyCabinet);
        $entityManager->persist($noMemberCabinet);
        $entityManager->persist($ineligibleCabinet);
        $entityManager->flush();
        $this->createLawyer('sidebar-public@example.test', 'Avocat public Abidjan', true, true, 'ACTIVE', $earlyCabinet);
        $this->createLawyer('sidebar-hidden@example.test', 'Avocat non publiable', true, false, 'ACTIVE', $ineligibleCabinet);
        $entityManager->flush();

        $client->request('GET', '/avocats', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        $cabinetNames = static::getContainer()->get(QueryBus::class)->handle(new GetPublicCabinetDirectoryQuery());
        self::assertSame(['Cabinet Abidjan', 'Cabinet du Centre'], array_map(static fn ($cabinet): string => $cabinet->name, $cabinetNames));
        self::assertSelectorTextContains('aside', 'Cabinet Abidjan');
        self::assertSelectorTextContains('aside', 'Cabinet du Centre');
        self::assertSelectorTextNotContains('aside', 'Cabinet Ivoire');
        self::assertSelectorTextNotContains('aside', 'Cabinet sans membre');
        self::assertSelectorTextNotContains('aside', 'Cabinet sans avocat publiable');
        $html = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString($ineligibleCabinet->getUuidAsString(), $html);
        self::assertStringNotContainsString('legacySourceUuid', $html);
    }

    public function testDirectoryReadModelContainsOnlyPublicProjectionFields(): void
    {
        $this->clientWithSchema();
        $this->createDirectoryDataset();

        $result = static::getContainer()->get(QueryBus::class)->handle(new GetPublicLawyerDirectoryQuery());

        self::assertInstanceOf(LawyerDirectoryResult::class, $result);
        self::assertInstanceOf(LawyerDirectoryEntry::class, $result->items[0]);
        self::assertSame(['publicUuid', 'name', 'cabinetName', 'location', 'portraitMediaId'], array_keys(get_object_vars($result->items[0])));
        self::assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $result->items[0]->publicUuid);
        self::assertSame(17, $result->totalItemCount);
        self::assertSame(12, $result->itemNumberPerPage);
    }

    public function testPublicLawyerProfileUsesOnlyEligiblePublicProfileData(): void
    {
        $client = $this->clientWithSchema();
        $this->createDirectoryDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'public-awa@example.test']);
        $profile = $entityManager->getRepository(LawyerProfileEntity::class)->findOneBy(['user' => $user]);
        self::assertInstanceOf(LawyerProfileEntity::class, $profile);
        $profile->setBarNumber('CI-2024-001')->setSpecializationSummary('Droit des affaires')->setBio('Présentation professionnelle')->setProfessionalEmail('awa.pro@example.test')->setProfessionalPhone('+225 01 02 03')->setDirectoryVisible(true);
        $entityManager->flush();
        self::assertNotNull(static::getContainer()->get(QueryBus::class)->handle(new GetPublicLawyerProfileQuery((string) $profile->getUuidAsString())));

        $client->request('GET', '/avocats/' . $profile->getUuidAsString(), server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Awa Kouassi');
        self::assertSelectorTextContains('body', 'Numéro au Barreau : CI-2024-001');
        self::assertSelectorTextContains('body', 'Droit des affaires');
        self::assertSelectorExists('a[href="mailto:awa.pro@example.test"]');
        self::assertSelectorExists('a[href="tel:+225010203"]');
        self::assertSelectorExists('img[src="/uploads/institution/lawyers/directory-portrait.jpg"]');
        self::assertSelectorTextContains('body', 'Cabinet Ivoire');
        self::assertSelectorNotExists('a[href^="/cabinets/"]');
        self::assertSelectorExists('#directory-back[href="/avocats"]');
        $html = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString('Abidjan', $html);
        foreach (['public-awa@example.test', 'ROLE_AVOCAT', 'directoryUser', 'user_id'] as $privateValue) {
            self::assertStringNotContainsString($privateValue, $html);
        }

        $client->request('GET', '/avocats/not-a-uuid', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(404);
        foreach (['private@example.test', 'suspended@example.test', 'disabled@example.test', 'non-lawyer@example.test'] as $email) {
            $ineligibleUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            $ineligibleProfile = $entityManager->getRepository(LawyerProfileEntity::class)->findOneBy(['user' => $ineligibleUser]);
            self::assertInstanceOf(LawyerProfileEntity::class, $ineligibleProfile);
            $client->request('GET', '/avocats/' . $ineligibleProfile->getUuidAsString(), server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(404);
            $client->request('GET', '/avocats/' . $ineligibleProfile->getUuidAsString(), server: [
                'HTTPS' => 'on',
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            ]);
            self::assertResponseStatusCodeSame(404);
        }
        $publicUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'public-awa@example.test']);
        self::assertInstanceOf(User::class, $publicUser);
        $client->request('GET', '/avocats/' . $publicUser->getUuidAsString(), server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(404);
        $client->request('GET', '/avocats/1', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(404);
        $independentUser = $entityManager->getRepository(User::class)->findOneBy(['email' => 'public-koffi@example.test']);
        $independentProfile = $entityManager->getRepository(LawyerProfileEntity::class)->findOneBy(['user' => $independentUser]);
        self::assertInstanceOf(LawyerProfileEntity::class, $independentProfile);
        $client->request('GET', '/avocats/' . $independentProfile->getUuidAsString(), server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('#lawyer-cabinet-title');
        $client->request('GET', '/avocats/018f8f5e-7b2c-7abc-8def-0123456789ab', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(404);
    }

    public function testDirectoryLawyerProfileLinkUsesAJAXModalWithThePublicProfileProjection(): void
    {
        $client = $this->clientWithSchema();
        $this->createDirectoryDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'public-mariam@example.test']);
        $profile = $entityManager->getRepository(LawyerProfileEntity::class)->findOneBy(['user' => $user]);
        self::assertInstanceOf(LawyerProfileEntity::class, $profile);
        $profile->setBarNumber('CI-2025-014')
            ->setSpecializationSummary('Droit social')
            ->setBio('Présentation publique de Mariam')
            ->setProfessionalEmail('mariam.pro@example.test')
            ->setProfessionalPhone('+225 07 55 44 33')
            ->setDirectoryVisible(true);
        $entityManager->flush();
        $uuid = (string) $profile->getUuidAsString();
        $cabinet = $entityManager->getRepository(CabinetEntity::class)->findOneBy(['name' => 'Cabinet du Centre']);
        self::assertInstanceOf(CabinetEntity::class, $cabinet);

        $client->request('GET', '/avocats?cabinet=' . $cabinet->getUuidAsString() . '&name=mariam&location=Bouaké&page=1', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('a[href="/avocats/' . $uuid . '"][data-action="click->lawyer-profile-modal#open"]');
        self::assertSelectorExists('a[href="/avocats/' . $uuid . '"][aria-haspopup="dialog"]');
        self::assertStringNotContainsString('mariam.pro@example.test', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Présentation publique de Mariam', (string) $client->getResponse()->getContent());

        $client->request('GET', '/avocats/' . $uuid, server: [
            'HTTPS' => 'on',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('text/html', (string) $client->getResponse()->headers->get('Content-Type'));
        self::assertSame('X-Requested-With', $client->getResponse()->headers->get('Vary'));
        self::assertSelectorExists('[data-lawyer-profile-fragment]');
        self::assertSame('Mariam N\'Dri', $client->getCrawler()->filter('[data-lawyer-profile-fragment]')->attr('data-lawyer-name'));
        self::assertSelectorTextContains('#lawyer-specialties-title', 'Droit social');
        self::assertSelectorTextContains('section[aria-labelledby="lawyer-biography-title"]', 'Présentation publique de Mariam');
        self::assertSelectorExists('a[href="mailto:mariam.pro@example.test"]');
        self::assertSelectorExists('a[href="tel:+22507554433"]');
        self::assertSelectorExists('a[href="/cabinets/' . $cabinet->getUuidAsString() . '"]');
        self::assertSelectorNotExists('nav[aria-label="Navigation principale"]');

        $html = (string) $client->getResponse()->getContent();
        foreach (['public-mariam@example.test', 'ROLE_AVOCAT', 'legacySourceUuid', 'directoryUser', 'user_id'] as $privateValue) {
            self::assertStringNotContainsString($privateValue, $html);
        }

        $client->request('GET', '/avocats/' . $uuid, server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('h1#lawyer-title');
        self::assertSelectorExists('#directory-back[href="/avocats"]');
        self::assertSelectorTextContains('section[aria-labelledby="lawyer-biography-title"]', 'Présentation publique de Mariam');
    }

    public function testLawyerProfileModalShowsPortraitFallbackAndOmitsUnavailableContacts(): void
    {
        $client = $this->clientWithSchema();
        $this->createDirectoryDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => 'public-koffi@example.test']);
        $profile = $entityManager->getRepository(LawyerProfileEntity::class)->findOneBy(['user' => $user]);
        self::assertInstanceOf(LawyerProfileEntity::class, $profile);

        $client->request('GET', '/avocats/' . $profile->getUuidAsString(), server: [
            'HTTPS' => 'on',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame('Koffi Indépendant', $client->getCrawler()->filter('[data-lawyer-profile-fragment]')->attr('data-lawyer-name'));
        self::assertSelectorNotExists('[data-lawyer-profile-fragment] img');
        self::assertSelectorNotExists('[data-lawyer-profile-fragment] a[href^="mailto:"]');
        self::assertSelectorNotExists('[data-lawyer-profile-fragment] a[href^="tel:"]');
        self::assertSelectorTextContains('[data-lawyer-profile-fragment]', 'Aucune information professionnelle complémentaire');

    }

    public function testAccountlessPublicLawyerProfileUsesProfessionalDisplayName(): void
    {
        $client = $this->clientWithSchema();
        $this->createDirectoryDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $profile = $entityManager->getRepository(LawyerProfileEntity::class)->findOneBy(['displayName' => 'Avocate historique autonome']);
        self::assertInstanceOf(LawyerProfileEntity::class, $profile);
        self::assertNull($profile->getUser());

        $client->request('GET', '/avocats/' . $profile->getUuidAsString(), server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Avocate historique autonome');

        foreach (['privateProfile' => 'Profil privé sans compte', 'suspendedProfile' => 'Profil suspendu sans compte'] as $name) {
            $ineligible = $entityManager->getRepository(LawyerProfileEntity::class)->findOneBy(['displayName' => $name]);
            self::assertInstanceOf(LawyerProfileEntity::class, $ineligible);
            self::assertNull($ineligible->getUser());
            $client->request('GET', '/avocats/' . $ineligible->getUuidAsString(), server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(404);
        }
    }

    public function testPublicCabinetShowsOnlyEligibleMembersInStableOrder(): void
    {
        $client = $this->clientWithSchema();
        $this->createDirectoryDataset();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $cabinet = $entityManager->getRepository(CabinetEntity::class)->findOneBy(['name' => 'Cabinet du Centre']);
        self::assertInstanceOf(CabinetEntity::class, $cabinet);
        $cabinet->setAddress('12, rue des Avocats')->setPhones(['+225 07 11 22', '01 23 45 67 89'])->setEmail('contact@centre.example.test')->setWebsiteUrl('javascript:alert(1)')->setDescription('Cabinet de démonstration');
        $this->createLawyer('public-amina@example.test', 'Amina Yao', true, true, 'ACTIVE', $cabinet);
        $this->createLawyer('hidden-member@example.test', 'Profil interne', true, false, 'ACTIVE', $cabinet);
        $this->createLawyer('suspended-member@example.test', 'Avocat suspendu du cabinet', true, true, 'SUSPENDED', $cabinet);
        $this->createLawyer('disabled-member@example.test', 'Compte désactivé du cabinet', false, true, 'ACTIVE', $cabinet);
        $this->createLawyer('non-lawyer-member@example.test', 'Compte sans rôle du cabinet', true, true, 'ACTIVE', $cabinet, null, ['ROLE_USER']);
        $entityManager->flush();
        self::assertNotNull(static::getContainer()->get(QueryBus::class)->handle(new GetPublicCabinetProfileQuery((string) $cabinet->getUuidAsString())));

        $client->request('GET', '/cabinets/' . $cabinet->getUuidAsString(), server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Cabinet du Centre');
        self::assertSelectorTextContains('body', '12, rue des Avocats');
        self::assertSelectorTextContains('body', 'Amina Yao');
        self::assertSelectorTextContains('body', 'Mariam N\'Dri');
        self::assertSelectorTextNotContains('body', 'Profil interne');
        self::assertSelectorTextNotContains('body', 'Avocat suspendu du cabinet');
        self::assertSelectorTextNotContains('body', 'Compte sans rôle du cabinet');
        self::assertSelectorExists('#directory-back[href="/avocats"]');
        self::assertSelectorExists('a[href="tel:+225071122"]');
        self::assertSelectorExists('a[href="tel:0123456789"]');
        self::assertSelectorNotExists('a[href^="javascript:"]');
        $names = $client->getCrawler()->filter('section[aria-labelledby="cabinet-members-title"] a')->each(static fn ($node): string => trim($node->filter('span.block')->first()->text()));
        self::assertSame(['Amina Yao', 'Avocate historique autonome', 'Mariam N\'Dri'], $names);

        $cabinet->setDirectoryVisible(false);
        $entityManager->flush();
        $client->request('GET', '/cabinets/' . $cabinet->getUuidAsString(), server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(404);

        $cabinet->setDirectoryVisible(true)->setStatus('ARCHIVED');
        $entityManager->flush();
        $client->request('GET', '/cabinets/' . $cabinet->getUuidAsString(), server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(404);

        $cabinet->setDirectoryVisible(true)->setStatus('INACTIVE');
        $entityManager->flush();
        $client->request('GET', '/cabinets/' . $cabinet->getUuidAsString(), server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(404);

        $emptyPublicCabinet = (new CabinetEntity())->setName('Cabinet public sans membre')->setDirectoryVisible(true);
        $entityManager->persist($emptyPublicCabinet);
        $entityManager->flush();
        $client->request('GET', '/cabinets/' . $emptyPublicCabinet->getUuidAsString(), server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('#cabinet-members-title');
        $client->request('GET', '/cabinets/018f8f5e-7b2c-7abc-8def-0123456789ab', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(404);
    }

    public function testHomepageLawyerCardLinksToTheCanonicalDirectoryRoute(): void
    {
        $client = $this->clientWithSchema();

        $client->request('GET', '/', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSame('/avocats', $client->getCrawler()->filter('a:contains("Trouver un avocat")')->attr('href'));
        self::assertStringNotContainsString('/#trouver-un-avocat', (string) $client->getResponse()->getContent());
    }

    public function testLegacySourceUuidsAreUniqueWhenPresentAndMayBeNull(): void
    {
        $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $sourceUuid = \Symfony\Component\Uid\Uuid::fromString('26a8dd88-6873-4af0-91aa-f00ef65c028d');

        $firstProfile = (new LawyerProfileEntity())->setDisplayName('Profil sans compte 1')->setLegacySourceUuid($sourceUuid);
        $secondProfile = (new LawyerProfileEntity())->setDisplayName('Profil sans compte 2');
        $entityManager->persist($firstProfile);
        $entityManager->persist($secondProfile);
        $entityManager->flush();

        $duplicateProfile = (new LawyerProfileEntity())->setDisplayName('Profil sans compte doublon')->setLegacySourceUuid($sourceUuid);
        $entityManager->persist($duplicateProfile);
        $this->expectException(UniqueConstraintViolationException::class);
        $entityManager->flush();
    }

    public function testCabinetLegacySourceUuidIsUniqueWhenPresent(): void
    {
        $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $sourceUuid = \Symfony\Component\Uid\Uuid::fromString('c93c1eab-e71a-4c30-a55a-f667b1b84eaa');

        $entityManager->persist((new CabinetEntity())->setName('Cabinet source 1')->setLegacySourceUuid($sourceUuid));
        $entityManager->persist((new CabinetEntity())->setName('Cabinet sans provenance 1'));
        $entityManager->persist((new CabinetEntity())->setName('Cabinet sans provenance 2'));
        $entityManager->flush();
        $entityManager->persist((new CabinetEntity())->setName('Cabinet source doublon')->setLegacySourceUuid($sourceUuid));

        $this->expectException(UniqueConstraintViolationException::class);
        $entityManager->flush();
    }

    public function testCabinetPhoneCompatibilitySetterStoresAnOrderedList(): void
    {
        $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $cabinet = (new CabinetEntity())->setName('Cabinet téléphones')->setPhone('+225 01 02 03');
        self::assertSame(['+225 01 02 03'], $cabinet->getPhones());
        $cabinet->setPhones(['', '+225 01 02 03', ' 07 08 09 10  ']);
        $entityManager->persist($cabinet);
        $entityManager->flush();
        $entityManager->clear();

        $reloaded = $entityManager->getRepository(CabinetEntity::class)->findOneBy(['name' => 'Cabinet téléphones']);
        self::assertInstanceOf(CabinetEntity::class, $reloaded);
        self::assertSame(['+225 01 02 03', '07 08 09 10'], $reloaded->getPhones());
        self::assertSame('+225 01 02 03', $reloaded->getPhone());
    }

    public function testAccountlessLawyerProfileIsNotReturnedForMemberAccount(): void
    {
        $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = (new User())->setEmail('member-without-lawyer-profile@example.test')->setName('Membre')->setPassword('test-password');
        $entityManager->persist($user);
        $entityManager->persist((new LawyerProfileEntity())->setDisplayName('Profil autonome')->setDirectoryVisible(true));
        $entityManager->flush();

        self::assertNull($entityManager->getRepository(LawyerProfileEntity::class)->findOneByUser($user));
    }

    public function testDeletingLinkedUserPreservesLawyerProfile(): void
    {
        $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->getConnection()->executeStatement('PRAGMA foreign_keys = ON');
        $user = (new User())->setEmail('lawyer-to-delete@example.test')->setName('Nom du compte')->setPassword('test-password');
        $profile = (new LawyerProfileEntity())->setUser($user)->setDisplayName('Nom professionnel');
        $entityManager->persist($user);
        $entityManager->persist($profile);
        $entityManager->flush();
        $profileId = $profile->getId();

        // Simulate physical deletion: the application normally soft-deletes Users.
        $entityManager->getConnection()->executeStatement('DELETE FROM user WHERE id = ?', [$user->getId()]);
        $entityManager->clear();

        $preservedProfile = $entityManager->getRepository(LawyerProfileEntity::class)->find($profileId);
        self::assertInstanceOf(LawyerProfileEntity::class, $preservedProfile);
        self::assertNull($preservedProfile->getUser());
        self::assertSame('Nom professionnel', $preservedProfile->getDisplayName());
    }

    private function clientWithSchema(): KernelBrowser
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        (new SchemaTool($entityManager))->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $entityManager->persist(new Reglages('app_title', 'Application title', 'Avocat CI', 'text'));
        $entityManager->persist(new Reglages('app_paginate_limit', 'Pagination', '12', 'number'));
        $entityManager->persist((new Images())->setName('app_favicon')->setLabel('Favicon'));
        $entityManager->persist((new Currencies())->setCurrencyCode('XOF')->setCurrencyName('Franc CFA')->setRightSymbol('FCFA')->setDecimalPlace(0)->setIsActive(true));
        $entityManager->flush();
        $client->disableReboot();

        return $client;
    }

    private function createDirectoryDataset(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $publicCabinet = (new CabinetEntity())->setName('Cabinet Ivoire')->setCity('Abidjan')->setDirectoryVisible(false);
        $secondCabinet = (new CabinetEntity())->setName('Cabinet du Centre')->setCity('Bouaké')->setDirectoryVisible(true);
        $portrait = (new MediaEntity())
            ->setOriginalName('portrait.jpg')
            ->setStorageName('directory-portrait.jpg')
            ->setMimeType('image/jpeg')
            ->setSize(120)
            ->setWidth(320)
            ->setHeight(320)
            ->setStoragePath('institution/lawyers/directory-portrait.jpg');
        $entityManager->persist($publicCabinet);
        $entityManager->persist($secondCabinet);
        $entityManager->persist($portrait);
        $entityManager->flush();

        $this->createLawyer('public-awa@example.test', 'Awa Kouassi', true, true, 'ACTIVE', $publicCabinet, $portrait->getId());
        $this->createLawyer('public-mariam@example.test', 'Mariam N\'Dri', true, true, 'ACTIVE', $secondCabinet);
        $this->createLawyer('public-koffi@example.test', 'Koffi Indépendant', true, true, 'HONORARY', null);
        $historicalProfile = (new LawyerProfileEntity())
            ->setDisplayName('Avocate historique autonome')
            ->setProfessionalStatus('UNKNOWN')
            ->setDirectoryVisible(true)
            ->setCabinet($secondCabinet);
        $entityManager->persist($historicalProfile);
        for ($index = 3; $index <= 15; ++$index) {
            $this->createLawyer(sprintf('public-demo-%02d@example.test', $index), sprintf('Avocat Démo %02d', $index), true, true, 'ACTIVE', null);
        }

        $this->createLawyer('private@example.test', 'Profil privé', true, false, 'ACTIVE', null);
        $this->createLawyer('suspended@example.test', 'Avocat suspendu', true, true, 'SUSPENDED', null);
        $this->createLawyer('disabled@example.test', 'Compte désactivé', false, true, 'ACTIVE', null);
        $this->createLawyer('non-lawyer@example.test', 'Compte sans rôle avocat', true, true, 'ACTIVE', null, null, ['ROLE_USER']);
        $this->createLawyer('similar-role@example.test', 'Rôle avocat approchant', true, true, 'ACTIVE', null, null, ['ROLE_XAVOCAT']);
        $entityManager->persist((new LawyerProfileEntity())->setDisplayName('Profil privé sans compte')->setProfessionalStatus('UNKNOWN'));
        $entityManager->persist((new LawyerProfileEntity())->setDisplayName('Profil suspendu sans compte')->setProfessionalStatus('SUSPENDED')->setDirectoryVisible(true));
        $entityManager->flush();
    }

    /** @param list<string> $roles */
    private function createLawyer(
        string $email,
        string $name,
        bool $enabled,
        bool $visible,
        string $status,
        ?CabinetEntity $cabinet,
        ?int $portraitMediaId = null,
        array $roles = ['ROLE_AVOCAT'],
    ): void {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = (new User())->setEmail($email)->setName($name)->setPassword('test-password')->setRoles($roles);
        $user->setEnabled($enabled);
        $entityManager->persist($user);
        $entityManager->persist((new LawyerProfileEntity())
            ->setUser($user)
            ->setDisplayName($name)
            ->setCabinet($cabinet)
            ->setProfessionalStatus($status)
            ->setDirectoryVisible($visible)
            ->setPortraitMediaId($portraitMediaId));
    }
}
