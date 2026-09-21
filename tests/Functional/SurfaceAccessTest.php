<?php
declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class SurfaceAccessTest extends WebTestCase
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

    public function testPublicHomeIsAccessibleAnonymously(): void
    {
        $client = $this->clientWithSchema();
        $client->request('GET', '/', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(200);
    }

    public function testMemberAreaRedirectsAnonymousVisitorsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/auth/login');
    }

    public function testMemberAreaIsAccessibleToAuthenticatedUsers(): void
    {
        $client = $this->authenticatedClient(['ROLE_USER']);
        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Bonjour Surface');
    }

    public function testAvocatSeesProfessionalDashboardGreeting(): void
    {
        $client = $this->authenticatedClient(['ROLE_AVOCAT']);
        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Bonjour Maître Surface');
    }

    public function testAuthenticatedUserCanOpenMemberNotificationsWithoutBackofficeRoute(): void
    {
        $client = $this->authenticatedClient(['ROLE_AVOCAT']);
        $client->request('GET', '/espace/notifications', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Restez informé');
        self::assertStringNotContainsString('/admin/notification', $client->getResponse()->getContent() ?: '');
    }

    public function testAuthenticatedUserCanOpenMemberProfileWithoutBackofficeRoute(): void
    {
        $client = $this->authenticatedClient(['ROLE_AVOCAT']);
        $client->request('GET', '/espace/profil', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Mon profil');
        self::assertStringNotContainsString('/admin/identity/users', $client->getResponse()->getContent() ?: '');
    }

    public function testBackofficeRedirectsAnonymousVisitorsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/auth/login');
    }

    public function testRegularUserIsDeniedFromBackofficeDashboard(): void
    {
        $client = $this->authenticatedClient(['ROLE_USER']);
        $client->request('GET', '/admin', server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/admin/dashboard/');

        $client->catchExceptions(false);
        $this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);
        $client->followRedirect();
    }

    public function testAvocatIsDeniedFromBackofficeDashboard(): void
    {
        $client = $this->authenticatedClient(['ROLE_AVOCAT']);
        $client->request('GET', '/admin', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/admin/dashboard/');

        $client->catchExceptions(false);
        $this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);
        $client->followRedirect();
    }

    public function testAvocatIsDeniedFromUserAccountBackoffice(): void
    {
        $client = $this->authenticatedClient(['ROLE_AVOCAT']);
        $client->request('GET', '/admin/identity/users/list', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAdminCanEnterBackoffice(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);
        $client->request('GET', '/admin', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/admin/dashboard/');
    }

    public function testSuperAdminCanEnterBackoffice(): void
    {
        $client = $this->authenticatedClient(['ROLE_SUPER_ADMIN']);
        $client->request('GET', '/admin', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/admin/dashboard/');
    }

    /** @param list<string> $roles */
    private function userWithRoles(array $roles): User
    {
        $user = (new User())
            ->setEmail('surface-test@example.test')
            ->setName('Surface Test')
            ->setPassword('test-password');
        $user->setEnabled(true);
        $user->setRoles($roles);

        return $user;
    }

    /** @param list<string> $roles */
    private function authenticatedClient(array $roles): KernelBrowser
    {
        $client = $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();

        $user = $this->userWithRoles($roles);
        $entityManager->persist($user);
        $entityManager->flush();
        $client->loginUser($user);
        $client->disableReboot();

        return $client;
    }

    private function clientWithSchema(): KernelBrowser
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $entityManager->persist(new Reglages('app_title', 'Application title', 'Avocat CI', 'text'));
        $entityManager->persist(new Reglages('app_paginate_limit', 'Pagination', '15', 'number'));
        $entityManager->persist(
            (new Images())
                ->setName('app_favicon')
                ->setLabel('Favicon')
        );
        $entityManager->persist(
            (new Currencies())
                ->setCurrencyCode('XOF')
                ->setCurrencyName('Franc CFA')
                ->setRightSymbol('FCFA')
                ->setDecimalPlace(0)
                ->setIsActive(true)
        );
        $entityManager->flush();

        return $client;
    }
}
