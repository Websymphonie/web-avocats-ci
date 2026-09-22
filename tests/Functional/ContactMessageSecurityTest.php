<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use DateTimeImmutable;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\ContactContext\Domain\Enum\ContactMessageDeliveryStatus;
use Websymphonie\ContactContext\Infrastructure\Persistence\Doctrine\Entity\ContactMessage\ContactMessageEntity;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class ContactMessageSecurityTest extends WebTestCase
{
    private static int $userSequence = 0;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        putenv('DATABASE_URL=sqlite:///:memory:');
        $_ENV['DATABASE_URL'] = 'sqlite:///:memory:';
        $_SERVER['DATABASE_URL'] = 'sqlite:///:memory:';
        parent::setUpBeforeClass();
    }

    public function testAuthorizedAdminCanListAndViewMessagesInRecentFirstOrder(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);
        $oldUuid = $this->createMessage('Ancienne demande', new DateTimeImmutable('-2 days'), ContactMessageDeliveryStatus::FAILED);
        $newUuid = $this->createMessage('Demande récente', new DateTimeImmutable('-1 hour'), ContactMessageDeliveryStatus::SENT);

        $client->request('GET', '/admin/contact/messages', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Demande récente', $content);
        self::assertStringContainsString('Ancienne demande', $content);
        self::assertLessThan(strpos($content, 'Ancienne demande'), strpos($content, 'Demande récente'));

        $client->request('GET', '/admin/contact/messages/' . $newUuid, server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertStringContainsString('Envoyé', (string) $client->getResponse()->getContent());
        self::assertNotSame($oldUuid, $newUuid);
    }

    public function testStatusFilterAndEscapedMessageAreApplied(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);
        $sentUuid = $this->createMessage('Visible envoyé', new DateTimeImmutable(), ContactMessageDeliveryStatus::SENT, '<script>alert(1)</script>');
        $this->createMessage('Masqué en échec', new DateTimeImmutable('-1 hour'), ContactMessageDeliveryStatus::FAILED);

        $client->request('GET', '/admin/contact/messages?status=SENT', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Visible envoyé', $content);
        self::assertStringNotContainsString('Masqué en échec', $content);

        $client->request('GET', '/admin/contact/messages/' . $sentUuid, server: ['HTTPS' => 'on']);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', (string) $client->getResponse()->getContent());
    }

    /** @dataProvider deniedRoles */
    public function testUnauthorizedRolesCannotAccessMessages(string $role): void
    {
        $client = $this->authenticatedClient([$role]);
        $client->request('GET', '/admin/contact/messages', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAnonymousVisitorsAreRedirectedToLogin(): void
    {
        $client = $this->clientWithSchema();
        $client->request('GET', '/admin/contact/messages', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/auth/login');
    }

    /** @return iterable<string, array{string}> */
    public static function deniedRoles(): iterable
    {
        yield 'avocat' => ['ROLE_AVOCAT'];
        yield 'user' => ['ROLE_USER'];
    }

    /** @param list<string> $roles */
    private function authenticatedClient(array $roles): KernelBrowser
    {
        $client = $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = (new User())
            ->setEmail(sprintf('contact-security-%d@example.test', ++self::$userSequence))
            ->setName('Contact Security Test')
            ->setPassword('test-password');
        $user->setEnabled(true);
        $user->setRoles($roles);
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
        (new SchemaTool($entityManager))->createSchema($entityManager->getMetadataFactory()->getAllMetadata());
        $entityManager->persist(new Reglages('app_title', 'Application title', 'Avocat CI', 'text'));
        $entityManager->persist(new Reglages('app_paginate_limit', 'Pagination', '15', 'number'));
        $entityManager->persist((new Images())->setName('app_logo')->setLabel('Logo'));
        $entityManager->persist((new Images())->setName('app_favicon')->setLabel('Favicon'));
        $entityManager->persist(
            (new Currencies())
                ->setCurrencyCode('XOF')
                ->setCurrencyName('Franc CFA')
                ->setRightSymbol('FCFA')
                ->setDecimalPlace(0)
                ->setIsActive(true),
        );
        $entityManager->flush();

        return $client;
    }

    private function createMessage(string $subject, DateTimeImmutable $submittedAt, ContactMessageDeliveryStatus $status, string $message = 'Message de test'): string
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entity = (new ContactMessageEntity())
            ->setFullName('Awa Koné')
            ->setEmail('awa@example.test')
            ->setPhone(null)
            ->setSubject($subject)
            ->setMessage($message)
            ->setConsentAt($submittedAt)
            ->setSubmittedAt($submittedAt)
            ->setDeliveryStatus($status)
            ->setSentAt($status === ContactMessageDeliveryStatus::SENT ? $submittedAt : null);
        $entityManager->persist($entity);
        $entityManager->flush();

        return $entity->getUuidAsString() ?? Uuid::v7()->toRfc4122();
    }
}
