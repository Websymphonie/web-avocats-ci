<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\LawyerContext\Application\Usecase\Query\GetPublicLawyerDirectoryQuery;
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
        self::assertSelectorExists('form[method="get"] input[name="cabinet"]');
        self::assertSelectorExists('form[method="get"] input[name="location"]');
        self::assertSelectorTextContains('body', '16 résultats');

        $content = (string) $client->getResponse()->getContent();
        foreach (['private@example.test', 'suspended@example.test', 'disabled@example.test', 'non-lawyer@example.test', 'ROLE_AVOCAT'] as $privateData) {
            self::assertStringNotContainsString($privateData, $content);
        }
        self::assertStringNotContainsString('Profil privé', $content);
        self::assertStringNotContainsString('Avocat suspendu', $content);
        self::assertStringNotContainsString('Compte désactivé', $content);
        self::assertStringNotContainsString('Compte sans rôle avocat', $content);
        self::assertStringNotContainsString('Rôle avocat approchant', $content);
        $client->request('GET', '/avocats?name=awa', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('article', 'Awa Kouassi');
        self::assertSelectorTextNotContains('article', 'Mariam N\'Dri');
        self::assertStringContainsString('/uploads/institution/lawyers/directory-portrait.jpg', (string) $client->getResponse()->getContent());

        $client->request('GET', '/avocats?cabinet=ivoire', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('article', 'Awa Kouassi');
        self::assertSelectorTextContains('article', 'Cabinet Ivoire');

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
        self::assertSelectorTextContains('body', '16 résultats');
        $namesOnSecondPage = $client->getCrawler()->filter('article h3')->each(static fn ($node): string => trim($node->text()));
        self::assertContains('Mariam N\'Dri', $namesOnSecondPage);

        $client->request('GET', '/avocats?name=ce-nom-nexiste-pas', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#directory-empty-title', 'Aucun avocat ne correspond à votre recherche');
        self::assertSelectorExists('a[href="/avocats"]');
        self::assertSelectorExists('input[name="name"][value="ce-nom-nexiste-pas"]');
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
        self::assertSame(16, $result->totalItemCount);
        self::assertSame(12, $result->itemNumberPerPage);
    }

    public function testHomepageLawyerCardLinksToTheCanonicalDirectoryRoute(): void
    {
        $client = $this->clientWithSchema();

        $client->request('GET', '/', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSame('/avocats', $client->getCrawler()->filter('a:contains("Trouver un avocat")')->attr('href'));
        self::assertStringNotContainsString('/#trouver-un-avocat', (string) $client->getResponse()->getContent());
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
        for ($index = 3; $index <= 15; ++$index) {
            $this->createLawyer(sprintf('public-demo-%02d@example.test', $index), sprintf('Avocat Démo %02d', $index), true, true, 'ACTIVE', null);
        }

        $this->createLawyer('private@example.test', 'Profil privé', true, false, 'ACTIVE', null);
        $this->createLawyer('suspended@example.test', 'Avocat suspendu', true, true, 'SUSPENDED', null);
        $this->createLawyer('disabled@example.test', 'Compte désactivé', false, true, 'ACTIVE', null);
        $this->createLawyer('non-lawyer@example.test', 'Compte sans rôle avocat', true, true, 'ACTIVE', null, null, ['ROLE_USER']);
        $this->createLawyer('similar-role@example.test', 'Rôle avocat approchant', true, true, 'ACTIVE', null, null, ['ROLE_XAVOCAT']);
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
            ->setCabinet($cabinet)
            ->setProfessionalStatus($status)
            ->setDirectoryVisible($visible)
            ->setPortraitMediaId($portraitMediaId));
    }
}
