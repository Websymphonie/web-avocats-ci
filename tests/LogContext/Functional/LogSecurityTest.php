<?php

declare(strict_types=1);

namespace Websymphonie\Tests\LogContext\Functional;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\ContentContext\Domain\Event\ContentLifecycleEvent;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Repository\Users\UserRepository;
use Websymphonie\LogContext\Application\Usecase\Command\Audit\RecordAuditEntryCommand;
use Websymphonie\LogContext\Domain\Enum\AuditActorType;
use Websymphonie\LogContext\Domain\Repository\Audit\AuditEntryRepository;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\AuthLog\AuthLog;
use Websymphonie\LogContext\Infrastructure\Persistence\Doctrine\Entity\Log\Logs;
use Websymphonie\LogContext\Infrastructure\Listener\DbLogListener;
use Websymphonie\PaymentContext\Domain\Event\PaymentConfirmedEvent;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandBus;
use Websymphonie\SharedContext\Domain\Service\EventDispatcher\EventDispatcher;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class LogSecurityTest extends WebTestCase
{
    private static string $storageDirectory;
    private static string|false $previousStorageDirectory = false;
    private static int $userSequence = 0;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        self::$previousStorageDirectory = getenv('APP_STORAGE_DIR');
        self::$storageDirectory = sys_get_temp_dir() . '/avocat-log-security-' . bin2hex(random_bytes(5));

        foreach ([
            'DATABASE_URL' => 'sqlite:///:memory:',
            'MYSQL_VERSION' => '8.0.40',
            'SECURE_SCHEME' => 'https',
            'APP_STORAGE_DIR' => self::$storageDirectory,
        ] as $name => $value) {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        (new Filesystem())->remove(self::$storageDirectory);

        if (self::$previousStorageDirectory === false) {
            putenv('APP_STORAGE_DIR');
            unset($_ENV['APP_STORAGE_DIR'], $_SERVER['APP_STORAGE_DIR']);
        } else {
            putenv('APP_STORAGE_DIR=' . self::$previousStorageDirectory);
            $_ENV['APP_STORAGE_DIR'] = self::$previousStorageDirectory;
            $_SERVER['APP_STORAGE_DIR'] = self::$previousStorageDirectory;
        }

        parent::tearDownAfterClass();
    }

    public function testPasswordUpdateCapturesOnlyTheFactOfChange(): void
    {
        $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $oldHash = 'old-password-hash-value';
        $newHash = 'new-password-hash-value';
        $user = (new User())
            ->setEmail('log-password@example.test')
            ->setName('Logging Test')
            ->setPassword($oldHash);
        $user->setEnabled(true);
        $entityManager->persist($user);
        $entityManager->flush();

        /** @var UserRepository $repository */
        $repository = static::getContainer()->get(UserRepository::class);
        $repository->upgradePassword($user, $newHash);

        $rows = static::getContainer()->get('doctrine.dbal.default_connection')
            ->fetchAllAssociative('SELECT message, context, extra FROM logs');
        $messages = implode("\n", array_column($rows, 'message'));
        $serialized = json_encode($rows, JSON_THROW_ON_ERROR);

        self::assertNotSame('', $serialized);
        self::assertStringContainsString('Champ "password" modifié', $messages);
        self::assertStringContainsString('[REDACTED]', $serialized);
        self::assertStringNotContainsString($oldHash, $serialized);
        self::assertStringNotContainsString($newHash, $serialized);
    }

    public function testAuthLogAcceptsAFullIpv6Address(): void
    {
        $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $ipv6 = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
        $authLog = (new AuthLog('ipv6@example.test', $ipv6))->setIsSuccessFulAuth(false);
        $entityManager->persist($authLog);
        $entityManager->flush();

        $stored = $entityManager->find(AuthLog::class, $authLog->getId());

        self::assertInstanceOf(AuthLog::class, $stored);
        self::assertSame($ipv6, $stored->getUserIP());
    }

    public function testTechnicalLogsKeepTheirCurrentDeleteBehavior(): void
    {
        $this->clientWithSchema();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $log = (new Logs())
            ->setMessage('technical event')
            ->setLevel(200)
            ->setLevelName('INFO')
            ->setContext(['operation' => 'test'])
            ->setExtra([]);
        $entityManager->persist($log);
        $entityManager->flush();
        $id = $log->getId();

        $entityManager->remove($log);
        $entityManager->flush();

        self::assertNotNull($id);
        self::assertNull($entityManager->find(Logs::class, $id));
    }

    public function testBusinessAuditIsPersistedSanitizedAndDeduplicated(): void
    {
        $this->clientWithSchema();
        $commandBus = static::getContainer()->get(CommandBus::class);
        $deduplicationKey = hash('sha256', 'learning.training.published|training-1');
        $command = new RecordAuditEntryCommand(
            context: 'LEARNING',
            action: 'learning.training.published',
            actorType: AuditActorType::USER,
            actorId: 'user-uuid',
            targetType: 'Training',
            targetId: 'training-1',
            metadata: ['password' => 'secret', 'status' => 'PUBLISHED'],
            deduplicationKey: $deduplicationKey,
        );

        $first = $commandBus->handle($command);
        $second = $commandBus->handle($command);

        self::assertNotNull($first->id);
        self::assertSame($first->id, $second->id);
        self::assertSame('[REDACTED]', $first->metadata['password']);

        /** @var AuditEntryRepository $repository */
        $repository = static::getContainer()->get(AuditEntryRepository::class);
        $stored = $repository->findById($first->id);
        self::assertNotNull($stored);
        self::assertSame('PUBLISHED', $stored->metadata['status']);
        self::assertSame('Training', $stored->targetType);

        $systemEntry = $commandBus->handle(new RecordAuditEntryCommand(
            context: 'PAYMENT',
            action: 'payment.webhook.received',
            actorType: AuditActorType::SYSTEM,
            actorId: 'kkiapay_webhook',
            metadata: ['providerReference' => 'provider-1'],
        ));
        self::assertSame('SYSTEM', $systemEntry->actor->type->value);
    }

    public function testBusinessEventsReachAuditAndReplayDoesNotDuplicateEntries(): void
    {
        $this->clientWithSchema();
        /** @var EventDispatcher $dispatcher */
        $dispatcher = static::getContainer()->get(EventDispatcher::class);
        $occurredAt = new \DateTimeImmutable('2026-09-20T10:00:00+00:00');
        $contentEvent = new ContentLifecycleEvent('NEWS', 'PUBLISHED', 'news-audit-1', 'Audit', 'audit', 42, $occurredAt);

        $dispatcher->dispatch([$contentEvent, $contentEvent]);
        $dispatcher->dispatch([new PaymentConfirmedEvent('payment-audit-1', 42, 10, 5000, 'XOF', 'KKIAPAY', 'transaction-audit-1', $occurredAt)]);

        $rows = static::getContainer()->get('doctrine.dbal.default_connection')->fetchAllAssociative('SELECT action, actor_type, actor_id, target_type, target_id FROM audit_entry ORDER BY id ASC');
        $actions = array_column($rows, 'action');
        self::assertSame(['content.news.published', 'payment.payment.confirmed'], $actions);
        self::assertSame('USER', $rows[0]['actor_type']);
        self::assertSame('42', (string) $rows[0]['actor_id']);
        self::assertSame('News', $rows[0]['target_type']);
        self::assertSame('kkiapay_webhook', $rows[1]['actor_id']);
        self::assertSame('payment-audit-1', $rows[1]['target_id']);
    }

    public function testLogBackofficeDeniesAnonymousAndNonSuperRoles(): void
    {
        $client = $this->clientWithSchema();
        $client->request('GET', '/admin/log/list', server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/auth/login');
        $client->request('GET', '/admin/log/audit', server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/auth/login');

        foreach (['ROLE_ADMIN', 'ROLE_AVOCAT', 'ROLE_USER'] as $role) {
            $entityManager = static::getContainer()->get('doctrine')->getManager();
            $user = (new User())
                ->setEmail(sprintf('log-access-%d@example.test', ++self::$userSequence))
                ->setName('Log Access Test')
                ->setPassword('test-password')
                ->setRoles([(string) $role]);
            $user->setEnabled(true);
            $entityManager->persist($user);
            $entityManager->flush();
            $client->loginUser($user);
            $client->request('GET', '/admin/log/list', server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, (string) $role);
            $client->request('GET', '/admin/log/audit', server: ['HTTPS' => 'on']);
            self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, (string) $role);
        }
    }

    public function testSuperAdminCanAccessLogBackoffice(): void
    {
        $client = $this->clientWithSchema();
        $commandBus = static::getContainer()->get(CommandBus::class);
        $commandBus->handle(new RecordAuditEntryCommand(
            context: 'LEARNING',
            action: 'learning.training.published',
            actorType: AuditActorType::SYSTEM,
            actorId: 'system',
            targetType: 'Training',
            targetId: 'training-1',
            metadata: ['status' => 'PUBLISHED'],
        ));
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = (new User())
            ->setEmail(sprintf('log-super-access-%d@example.test', ++self::$userSequence))
            ->setName('Log Super Access Test')
            ->setPassword('test-password')
            ->setRoles(['ROLE_SUPER_ADMIN']);
        $user->setEnabled(true);
        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);
        $client->request('GET', '/admin/log/list', server: ['HTTPS' => 'on']);

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $client->request('GET', '/admin/log/audit', server: ['HTTPS' => 'on']);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertStringContainsString('learning.training.published', $client->getResponse()->getContent());
    }

    private function clientWithSchema(): KernelBrowser
    {
        DbLogListener::enable();
        $client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        $entityManager->persist(new Reglages('app_title', 'Application title', 'Avocat CI', 'text'));
        $entityManager->persist(new Reglages('app_paginate_limit', 'Pagination limit', '20', 'text'));
        $entityManager->persist((new Images())->setName('app_logo')->setLabel('Logo'));
        $entityManager->persist((new Images())->setName('app_favicon')->setLabel('Favicon'));
        $entityManager->persist(
            (new Currencies())
                ->setCurrencyCode('XOF')
                ->setCurrencyName('Franc CFA')
                ->setRightSymbol('FCFA')
                ->setDecimalPlace(0)
                ->setIsActive(true)
        );
        $entityManager->flush();
        $client->disableReboot();

        return $client;
    }

}
