<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class PasswordSecurityTest extends WebTestCase
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

    public function testUserCannotChangeAnotherUsersProfilePassword(): void
    {
        $client = static::createClient();
        [$author, $target] = $this->prepareUsers();
        $client->loginUser($author);
        $client->catchExceptions(false);

        $this->expectException(AccessDeniedException::class);
        $client->request(
            'POST',
            '/admin/identity/user/' . $target->getId() . '/change-profile-password',
            [],
            server: ['HTTPS' => 'on'],
        );
    }

    public function testUserCanChangeOwnProfilePassword(): void
    {
        $client = static::createClient();
        [$author] = $this->prepareUsers();
        $client->loginUser($author);
        $client->request('POST', '/admin/identity/user/' . $author->getId() . '/change-profile-password', [
            'profile_change_password' => [
                'currentPassword' => 'InitialPassword123!',
                'password' => 'NewPassword123!',
                'confirmPassword' => 'NewPassword123!',
                '_token' => $this->csrfToken($client),
            ],
        ], server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/auth/login');
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    /** @return array{User, User} */
    private function prepareUsers(): array
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());
        $entityManager->persist(new Reglages('app_title', 'Application title', 'Avocat CI', 'text'));
        $entityManager->persist((new Images())->setName('app_logo')->setLabel('Logo'));
        $entityManager->persist((new Images())->setName('app_favicon')->setLabel('Favicon'));
        $entityManager->persist((new Currencies())->setCurrencyCode('XOF')->setCurrencyName('Franc CFA')->setRightSymbol('FCFA')->setDecimalPlace(0)->setIsActive(true));

        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $author = $this->user('password-author', ['ROLE_SUPER_ADMIN']);
        $target = $this->user('password-target');
        $author->setPassword($hasher->hashPassword($author, 'InitialPassword123!'));
        $target->setPassword($hasher->hashPassword($target, 'TargetPassword123!'));
        $entityManager->persist($author);
        $entityManager->persist($target);
        $entityManager->flush();

        return [$author, $target];
    }

    /** @param list<string> $roles */
    private function user(string $prefix, array $roles = ['ROLE_USER']): User
    {
        $user = (new User())
            ->setEmail(sprintf('%s-%d@example.test', $prefix, ++self::$sequence))
            ->setName($prefix)
            ->setRoles($roles);
        $user->setEnabled(true);

        return $user;
    }

    private function csrfToken(KernelBrowser $client): string
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTPS' => 'on']);
        $request->setSession($client->getSession());
        $requestStack = static::getContainer()->get('request_stack');
        $requestStack->push($request);
        try {
            $token = static::getContainer()->get('security.csrf.token_manager')->getToken('profile_change_password')->getValue();
            $request->getSession()->save();

            return $token;
        } finally {
            $requestStack->pop();
        }
    }

}
