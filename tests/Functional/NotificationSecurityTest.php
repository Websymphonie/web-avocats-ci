<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class NotificationSecurityTest extends WebTestCase
{
    private static int $sequence = 0;

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

    public function testReadAllRejectsMissingCsrfToken(): void
    {
        $client = $this->authenticatedClient();
        $client->request('POST', '/admin/notification/read-all', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/admin/notification/list');
    }

    public function testReadAllRejectsInvalidCsrfToken(): void
    {
        $client = $this->authenticatedClient();
        $client->request('POST', '/admin/notification/read-all', ['_token' => 'invalid'], server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/admin/notification/list');
    }

    public function testReadAllAcceptsValidCsrfToken(): void
    {
        $client = $this->authenticatedClient();
        $client->request('POST', '/admin/notification/read-all', [
            '_token' => $this->csrfToken($client),
        ], server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/admin/notification/list');
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    private function authenticatedClient(): KernelBrowser
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $entityManager->persist(new Reglages('app_title', 'Application title', 'Avocat CI', 'text'));
        $entityManager->persist((new Images())->setName('app_logo')->setLabel('Logo'));
        $entityManager->persist((new Images())->setName('app_favicon')->setLabel('Favicon'));
        $entityManager->persist((new Currencies())->setCurrencyCode('XOF')->setCurrencyName('Franc CFA')->setRightSymbol('FCFA')->setDecimalPlace(0)->setIsActive(true));

        $user = (new User())
            ->setEmail(sprintf('notification-security-%d@example.test', ++self::$sequence))
            ->setName('Notification Security Test')
            ->setPassword('test-password');
        $user->setEnabled(true);
        $user->setRoles(['ROLE_USER']);
        $entityManager->persist($user);
        $entityManager->flush();
        $client->loginUser($user);
        $client->disableReboot();

        return $client;
    }

    private function csrfToken(KernelBrowser $client): string
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTPS' => 'on']);
        $request->setSession($client->getSession());
        $requestStack = static::getContainer()->get('request_stack');
        $requestStack->push($request);
        try {
            $token = static::getContainer()->get('security.csrf.token_manager')->getToken('notification_read_all')->getValue();
            $request->getSession()->save();

            return $token;
        } finally {
            $requestStack->pop();
        }
    }
}
