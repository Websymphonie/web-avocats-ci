<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use DateTimeImmutable;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
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
        foreach ([
            'DATABASE_URL' => 'sqlite:///:memory:',
            'MAILER_DSN' => 'null://null',
        ] as $name => $value) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
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
        self::assertStringNotContainsString('Réessayer l’envoi', (string) $client->getResponse()->getContent());
        self::assertNotSame($oldUuid, $newUuid);

        $client->request('GET', '/admin/contact/messages/' . $oldUuid, server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertStringContainsString('Réessayer l’envoi', (string) $client->getResponse()->getContent());
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

    public function testAuthorizedAdminCanRetryFailedMessage(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);
        $uuid = $this->createMessage('Message à relancer', new DateTimeImmutable(), ContactMessageDeliveryStatus::FAILED);
        $token = $this->csrfToken($client, 'contact_message_retry_' . $uuid);

        $client->request('POST', '/admin/contact/messages/' . $uuid . '/retry', [
            '_token' => $token,
        ], server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/admin/contact/messages/' . $uuid);
        $message = static::getContainer()->get('doctrine')->getRepository(ContactMessageEntity::class)->findOneBy([]);
        self::assertInstanceOf(ContactMessageEntity::class, $message);
        self::assertSame(ContactMessageDeliveryStatus::SENT, $message->getDeliveryStatus());
        $auditAction = static::getContainer()->get('doctrine.dbal.default_connection')->fetchOne(
            'SELECT action FROM audit_entry WHERE target_id = :target ORDER BY id DESC LIMIT 1',
            ['target' => $uuid],
        );
        self::assertSame('contact.message.delivery_retry_succeeded', $auditAction);
    }

    public function testRetryRequiresValidCsrfToken(): void
    {
        $client = $this->authenticatedClient(['ROLE_ADMIN']);
        $uuid = $this->createMessage('Message protégé', new DateTimeImmutable(), ContactMessageDeliveryStatus::FAILED);

        $client->request('POST', '/admin/contact/messages/' . $uuid . '/retry', [
            '_token' => 'invalid-token',
        ], server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /** @dataProvider deniedRoles */
    public function testUnauthorizedRolesCannotAccessMessages(string $role): void
    {
        $client = $this->authenticatedClient([$role]);
        $client->request('GET', '/admin/contact/messages', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /** @dataProvider deniedRoles */
    public function testUnauthorizedRolesCannotRetryMessages(string $role): void
    {
        $client = $this->authenticatedClient([$role]);
        $uuid = $this->createMessage('Message interdit', new DateTimeImmutable(), ContactMessageDeliveryStatus::FAILED);

        $client->request('POST', '/admin/contact/messages/' . $uuid . '/retry', [
            '_token' => 'invalid-token',
        ], server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testAnonymousVisitorsAreRedirectedToLogin(): void
    {
        $client = $this->clientWithSchema();
        $client->request('GET', '/admin/contact/messages', server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/auth/login');
    }

    public function testAnonymousVisitorCanOpenPublicContactForm(): void
    {
        $client = $this->clientWithSchema();
        $client->request('GET', '/contact', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSelectorExists('form');
    }

    public function testAnonymousContactPostUsesCsrfAndHoneypotWithoutExternalMail(): void
    {
        $client = $this->clientWithSchema();
        $client->disableReboot();
        $crawler = $client->request('GET', '/contact', server: ['HTTPS' => 'on', 'REMOTE_ADDR' => '192.0.2.10']);
        $form = $crawler->selectButton('Envoyer le message')->form([
            'contact_message[fullName]' => 'Visiteur de test',
            'contact_message[email]' => 'visiteur@example.test',
            'contact_message[phone]' => '',
            'contact_message[subject]' => 'Test public isolé',
            'contact_message[message]' => 'Message envoyé sur un transport de test.',
            'contact_message[consent]' => '1',
            'contact_message[antispam][phone]' => '',
            'contact_message[antispam][faxNumber]' => '',
        ]);
        $client->submit($form, [], ['HTTPS' => 'on', 'REMOTE_ADDR' => '192.0.2.10']);

        self::assertResponseRedirects('/contact');
        $message = static::getContainer()->get('doctrine')->getRepository(ContactMessageEntity::class)->findOneBy([
            'subject' => 'Test public isolé',
        ]);
        self::assertInstanceOf(ContactMessageEntity::class, $message);
        self::assertSame(ContactMessageDeliveryStatus::SENT, $message->getDeliveryStatus());

        $crawler = $client->request('GET', '/contact', server: ['HTTPS' => 'on', 'REMOTE_ADDR' => '192.0.2.11']);
        $spamForm = $crawler->selectButton('Envoyer le message')->form([
            'contact_message[fullName]' => 'Robot de test',
            'contact_message[email]' => 'robot@example.test',
            'contact_message[subject]' => 'Soumission honeypot',
            'contact_message[message]' => 'Ne doit pas être persisté.',
            'contact_message[consent]' => '1',
            'contact_message[antispam][phone]' => 'robot',
            'contact_message[antispam][faxNumber]' => '',
        ]);
        $client->submit($spamForm, [], ['HTTPS' => 'on', 'REMOTE_ADDR' => '192.0.2.11']);

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertNull(static::getContainer()->get('doctrine')->getRepository(ContactMessageEntity::class)->findOneBy([
            'subject' => 'Soumission honeypot',
        ]));
    }

    public function testAnonymousContactPostWithInvalidCsrfTokenIsNotPersisted(): void
    {
        $client = $this->clientWithSchema();
        $client->disableReboot();
        $crawler = $client->request('GET', '/contact', server: ['HTTPS' => 'on', 'REMOTE_ADDR' => '192.0.2.12']);
        $form = $crawler->selectButton('Envoyer le message')->form([
            'contact_message[fullName]' => 'Visiteur sans CSRF valide',
            'contact_message[email]' => 'csrf@example.test',
            'contact_message[subject]' => 'CSRF invalide',
            'contact_message[message]' => 'Ce message ne doit pas être enregistré.',
            'contact_message[consent]' => '1',
            'contact_message[antispam][phone]' => '',
            'contact_message[antispam][faxNumber]' => '',
            'contact_message[_token]' => 'invalid-token',
        ]);
        $client->submit($form, [], ['HTTPS' => 'on', 'REMOTE_ADDR' => '192.0.2.12']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertNull(static::getContainer()->get('doctrine')->getRepository(ContactMessageEntity::class)->findOneBy([
            'subject' => 'CSRF invalide',
        ]));
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

    private function csrfToken(KernelBrowser $client, string $id): string
    {
        $request = Request::create('/', 'GET', [], [], [], ['HTTPS' => 'on']);
        $request->setSession($client->getSession());
        $requestStack = static::getContainer()->get('request_stack');
        $requestStack->push($request);
        try {
            $token = static::getContainer()->get('security.csrf.token_manager')->getToken($id)->getValue();
            $request->getSession()->save();

            return $token;
        } finally {
            $requestStack->pop();
        }
    }
}
