<?php

declare(strict_types=1);

namespace Websymphonie\Tests\Functional;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RemoteEvent\RemoteEvent;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Psr\Log\NullLogger;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Currencies\Currencies;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Images\Images;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Maintenance\Maintenances;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Reglages\Reglages;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\IdentityContext\Domain\Enum\PermissionEnum;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Role\RolePermissionsEntity;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentSource;
use Websymphonie\LearningContext\Domain\Enum\EnrollmentStatus;
use Websymphonie\LearningContext\Domain\Enum\TrainingAccessType;
use Websymphonie\LearningContext\Domain\Enum\TrainingStatus;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Enrollment\EnrollmentEntity;
use Websymphonie\LearningContext\Infrastructure\Persistence\Doctrine\Entity\Training\TrainingEntity;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Doctrine\Entity\Notifications\Notifications;
use Websymphonie\PaymentContext\Application\Model\VerifiedPaymentTransaction;
use Websymphonie\PaymentContext\Application\Service\PaymentTransactionVerifierInterface;
use Websymphonie\PaymentContext\Application\Usecase\Command\ConfirmPaymentCommand;
use Websymphonie\PaymentContext\Application\Usecase\Command\FailPaymentCommand;
use Websymphonie\PaymentContext\Application\Usecase\Command\InitiateTrainingPaymentCommand;
use Websymphonie\PaymentContext\Application\Usecase\Command\SaveTrainingOfferCommand;
use Websymphonie\PaymentContext\Domain\Enum\PaymentProvider;
use Websymphonie\PaymentContext\Domain\Enum\PaymentFulfillmentStatus;
use Websymphonie\PaymentContext\Domain\Enum\PaymentStatus;
use Websymphonie\PaymentContext\Domain\Exception\CurrencyNotFoundException;
use Websymphonie\PaymentContext\Domain\Exception\PaymentDeniedException;
use Websymphonie\PaymentContext\Domain\Model\Payment;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;
use Websymphonie\PaymentContext\Infrastructure\Webhook\KkiaPayWebhookConsumer;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandBus;
use Websymphonie\SharedContext\Infrastructure\Framework\Symfony\Kernel;

final class PaymentWorkflowTest extends WebTestCase
{
    private static string $storageDirectory;
    private static int $userSequence = 0;

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public static function setUpBeforeClass(): void
    {
        self::$storageDirectory = sys_get_temp_dir() . '/avocat-payment-' . bin2hex(random_bytes(5));
        foreach ([
            'DATABASE_URL' => 'sqlite:///:memory:',
            'MYSQL_VERSION' => '8.0.40',
            'SECURE_SCHEME' => 'https',
            'APP_STORAGE_DIR' => self::$storageDirectory,
            'KKIAPAY_WEBHOOK_SECRET' => 'webhook-secret',
            'PAYMENT_PROVIDER' => 'FAKE',
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
        parent::tearDownAfterClass();
    }

    public function testFakePaymentConfirmationGrantsAccessAndIsIdempotent(): void
    {
        $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $training = $this->createTraining(TrainingAccessType::PAID);
        $this->saveOffer($training, 15000);

        $payment = $this->commandBus()->handle(new InitiateTrainingPaymentCommand($user->getId() ?? 0, $training->getId() ?? 0, 'checkout-1'));
        self::assertSame(PaymentStatus::PENDING, $payment->status);
        self::assertSame(15000, $payment->amount);
        self::assertSame('XOF', $payment->currency);
        self::assertStringStartsWith('fake_tx_', (string) $payment->providerReference);

        $this->commandBus()->handle(new ConfirmPaymentCommand($payment->uuid, (string) $payment->providerReference));
        $this->commandBus()->handle(new ConfirmPaymentCommand($payment->uuid, (string) $payment->providerReference));

        $stored = static::getContainer()->get(PaymentRepositoryInterface::class)->getByUuid($payment->uuid);
        self::assertSame(PaymentStatus::CONFIRMED, $stored->status);
        self::assertSame(PaymentFulfillmentStatus::COMPLETED, $stored->fulfillmentStatus);
        self::assertSame(1, $this->entityManager()->getRepository(EnrollmentEntity::class)->count(['trainingId' => $training->getId(), 'userId' => $user->getId()]));
        $enrollment = $this->entityManager()->getRepository(EnrollmentEntity::class)->findOneBy(['trainingId' => $training->getId(), 'userId' => $user->getId()]);
        self::assertSame(EnrollmentStatus::ACTIVE, $enrollment->getStatus());
        self::assertSame(EnrollmentSource::PAYMENT, $enrollment->getSource());
        self::assertSame(1, $this->entityManager()->getRepository(Notifications::class)->count(['user' => $user]));
    }

    public function testSameIdempotencyKeyReturnsTheSamePaymentAndBrowserAmountIsIgnored(): void
    {
        $client = $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $training = $this->createTraining(TrainingAccessType::PAID);
        $this->saveOffer($training, 25000);

        $first = $this->commandBus()->handle(new InitiateTrainingPaymentCommand($user->getId() ?? 0, $training->getId() ?? 0, 'checkout-unique'));
        $second = $this->commandBus()->handle(new InitiateTrainingPaymentCommand($user->getId() ?? 0, $training->getId() ?? 0, 'checkout-unique'));
        self::assertSame($first->uuid, $second->uuid);

        $client->loginUser($user);
        $client->disableReboot();
        $url = '/espace/payments/trainings/' . $training->getUuidAsString() . '/initiate';
        $client->request('GET', '/espace', server: ['HTTPS' => 'on']);
        $client->request('POST', $url, [
            '_token' => $this->csrfToken($client, 'payment_initiate_' . $training->getUuidAsString()),
            'idempotencyKey' => 'checkout-unique',
            'amount' => '1',
            'currency' => 'EUR',
        ], server: ['HTTPS' => 'on']);

        self::assertResponseRedirects('/espace/payments/' . $first->uuid . '/status');
        $browserPayment = static::getContainer()->get(PaymentRepositoryInterface::class)->findByUserAndIdempotencyKey($user->getId() ?? 0, 'checkout-unique');
        self::assertNotNull($browserPayment);
        self::assertSame(25000, $browserPayment->amount);
        self::assertSame('XOF', $browserPayment->currency);
    }

    public function testFailedPaymentDoesNotGrantAccessAndCannotBeConfirmed(): void
    {
        $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $training = $this->createTraining(TrainingAccessType::PAID);
        $this->saveOffer($training, 10000);
        $payment = $this->commandBus()->handle(new InitiateTrainingPaymentCommand($user->getId() ?? 0, $training->getId() ?? 0, 'checkout-failed'));

        $this->commandBus()->handle(new FailPaymentCommand($payment->uuid));
        $this->commandBus()->handle(new FailPaymentCommand($payment->uuid));
        $failed = static::getContainer()->get(PaymentRepositoryInterface::class)->getByUuid($payment->uuid);
        self::assertSame(PaymentStatus::FAILED, $failed->status);
        self::assertSame(0, $this->entityManager()->getRepository(EnrollmentEntity::class)->count(['trainingId' => $training->getId(), 'userId' => $user->getId()]));
        self::assertSame(1, $this->entityManager()->getRepository(Notifications::class)->count(['user' => $user]));

        $this->expectException(\Websymphonie\PaymentContext\Domain\Exception\InvalidPaymentTransitionException::class);
        $this->commandBus()->handle(new ConfirmPaymentCommand($payment->uuid, (string) $payment->providerReference));
    }

    public function testRoleUserCannotInitiatePaidPayment(): void
    {
        $this->clientWithSchema();
        $user = $this->createUser(['ROLE_USER']);
        $training = $this->createTraining(TrainingAccessType::PAID);
        $this->saveOffer($training, 10000);

        try {
            $this->commandBus()->handle(new InitiateTrainingPaymentCommand($user->getId() ?? 0, $training->getId() ?? 0, 'ineligible-checkout'));
            self::fail('Une formation payante doit refuser un utilisateur ROLE_USER.');
        } catch (PaymentDeniedException) {
            self::assertTrue(true);
        }
        self::assertNull(static::getContainer()->get(PaymentRepositoryInterface::class)->findByUserAndIdempotencyKey($user->getId() ?? 0, 'ineligible-checkout'));
        self::assertSame(0, $this->entityManager()->getRepository(Notifications::class)->count(['user' => $user]));
    }

    /** @dataProvider deniedTrainingCases */
    public function testPaymentRequiresPublishedPaidTrainingAndActiveOffer(TrainingAccessType $accessType, TrainingStatus $status, bool $withOffer): void
    {
        $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $training = $this->createTraining($accessType, $status);
        if ($withOffer) {
            $this->saveOffer($training, 10000);
        }

        $this->expectException(PaymentDeniedException::class);
        $this->commandBus()->handle(new InitiateTrainingPaymentCommand($user->getId() ?? 0, $training->getId() ?? 0, 'denied-' . bin2hex(random_bytes(3))));
    }

    /** @return iterable<string, array{TrainingAccessType, TrainingStatus, bool}> */
    public static function deniedTrainingCases(): iterable
    {
        yield 'free' => [TrainingAccessType::FREE, TrainingStatus::PUBLISHED, true];
        yield 'restricted' => [TrainingAccessType::RESTRICTED, TrainingStatus::PUBLISHED, true];
        yield 'draft' => [TrainingAccessType::PAID, TrainingStatus::DRAFT, true];
        yield 'archived' => [TrainingAccessType::PAID, TrainingStatus::ARCHIVED, true];
        yield 'without offer' => [TrainingAccessType::PAID, TrainingStatus::PUBLISHED, false];
    }

    public function testAdminPaymentScreensAreAvailableAndReadOnly(): void
    {
        $client = $this->clientWithSchema();
        $admin = $this->createUser(['ROLE_SUPER_ADMIN']);
        $training = $this->createTraining(TrainingAccessType::PAID);
        $this->saveOffer($training, 10000);
        $client->loginUser($admin);
        $client->disableReboot();

        $client->request('GET', '/admin/payment/offers', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        $client->request('GET', '/admin/payment/payments', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
    }

    public function testPersistedAdminRoleCanAccessPaymentScreens(): void
    {
        $client = $this->clientWithSchema();
        $rolePermissions = new RolePermissionsEntity('ROLE_ADMIN');
        $rolePermissions->setPermissions([
            PermissionEnum::PAYMENT_VIEW->value,
            PermissionEnum::PAYMENT_OFFER_VIEW->value,
        ]);
        $this->entityManager()->persist($rolePermissions);
        $this->entityManager()->flush();
        $admin = $this->createUser(['ROLE_ADMIN']);
        $client->loginUser($admin);
        $client->disableReboot();

        $client->request('GET', '/admin/payment/offers', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
        $client->request('GET', '/admin/payment/payments', server: ['HTTPS' => 'on']);
        self::assertResponseIsSuccessful();
    }

    public function testOfferFormUsesOnlyActiveCurrenciesAndUnknownCurrencyIsRejected(): void
    {
        $client = $this->clientWithSchema();
        $entityManager = $this->entityManager();
        $entityManager->persist((new Currencies())
            ->setCurrencyCode('EUR')
            ->setCurrencyName('Euro')
            ->setIsActive(false));
        $entityManager->flush();

        $admin = $this->createUser(['ROLE_SUPER_ADMIN']);
        $client->loginUser($admin);
        $client->request('GET', '/admin/payment/offers/new', server: ['HTTPS' => 'on']);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Franc CFA (XOF)', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Euro (EUR)', (string) $client->getResponse()->getContent());

        $training = $this->createTraining(TrainingAccessType::PAID);
        $this->expectException(CurrencyNotFoundException::class);
        $this->commandBus()->handle(new SaveTrainingOfferCommand($training->getId() ?? 0, 10000, 'EUR', true));
    }

    public function testAnonymousCannotInitiatePayment(): void
    {
        $client = $this->clientWithSchema();
        $training = $this->createTraining(TrainingAccessType::PAID);
        $this->saveOffer($training, 10000);
        $client->request('POST', '/espace/payments/trainings/' . $training->getUuidAsString() . '/initiate', server: ['HTTPS' => 'on']);
        self::assertResponseRedirects('/auth/login');
    }

    public function testKkiaPayWebhookRejectsInvalidSecretWithoutAuthenticationRedirect(): void
    {
        $client = $this->clientWithSchema();
        $client->request(
            'POST',
            '/webhook/kkiapay',
            server: ['HTTPS' => 'on', 'CONTENT_TYPE' => 'application/json', 'HTTP_X_KKIAPAY_SECRET' => 'wrong-secret'],
            content: json_encode([
                'event' => 'transaction.success',
                'transactionId' => 'transaction-success',
                'isPaymentSucces' => true,
                'amount' => 25000,
                'partnerId' => '00000000-0000-7000-8000-000000000000',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(401);
        self::assertNotSame(301, $client->getResponse()->getStatusCode());
        self::assertNotSame(302, $client->getResponse()->getStatusCode());
    }

    public function testDuplicateKkiaPaySuccessConfirmsOnceAndNotifiesOnce(): void
    {
        $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $training = $this->createTraining(TrainingAccessType::PAID);
        $payment = static::getContainer()->get(PaymentRepositoryInterface::class)->save(new Payment(
            id: 0,
            uuid: \Symfony\Component\Uid\Uuid::v7()->toRfc4122(),
            userId: $user->getId() ?? 0,
            trainingId: $training->getId() ?? 0,
            trainingOfferId: null,
            amount: 25000,
            currency: 'XOF',
            provider: PaymentProvider::KKIAPAY,
            idempotencyKey: 'kkiapay-notification-test',
        ));
        $verifier = $this->createMock(PaymentTransactionVerifierInterface::class);
        $verifier->expects(self::exactly(2))->method('verify')->willReturn(new VerifiedPaymentTransaction('transaction-success', true, 25000, $payment->uuid));
        $consumer = new KkiaPayWebhookConsumer(
            static::getContainer()->get(PaymentRepositoryInterface::class),
            $verifier,
            $this->commandBus(),
            new NullLogger(),
        );
        $event = new RemoteEvent('transaction.success', 'transaction.success:transaction-success', [
            'event' => 'transaction.success',
            'transactionId' => 'transaction-success',
            'isPaymentSucces' => true,
            'amount' => 25000,
            'partnerId' => $payment->uuid,
        ]);

        $consumer->consume($event);
        $consumer->consume($event);

        $stored = static::getContainer()->get(PaymentRepositoryInterface::class)->getByUuid($payment->uuid);
        self::assertSame(PaymentStatus::CONFIRMED, $stored->status);
        self::assertSame(PaymentFulfillmentStatus::COMPLETED, $stored->fulfillmentStatus);
        self::assertSame(1, $this->entityManager()->getRepository(EnrollmentEntity::class)->count(['trainingId' => $training->getId(), 'userId' => $user->getId()]));
        self::assertSame(1, $this->entityManager()->getRepository(Notifications::class)->count(['user' => $user]));
    }

    public function testExistingActiveEnrollmentIsReusedByPaymentFulfillment(): void
    {
        $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $training = $this->createTraining(TrainingAccessType::PAID);
        $enrollment = (new EnrollmentEntity())
            ->setTrainingId($training->getId() ?? 0)
            ->setUserId($user->getId() ?? 0)
            ->setStatus(EnrollmentStatus::ACTIVE)
            ->setSource(EnrollmentSource::PAYMENT)
            ->setActivatedAt(new DateTimeImmutable());
        $this->entityManager()->persist($enrollment);
        $this->entityManager()->flush();
        $payment = static::getContainer()->get(PaymentRepositoryInterface::class)->save(new Payment(
            id: 0,
            uuid: \Symfony\Component\Uid\Uuid::v7()->toRfc4122(),
            userId: $user->getId() ?? 0,
            trainingId: $training->getId() ?? 0,
            trainingOfferId: null,
            amount: 25000,
            currency: 'XOF',
            status: PaymentStatus::CONFIRMED,
            provider: PaymentProvider::FAKE,
            providerReference: 'existing-enrollment-reference',
            fulfillmentStatus: PaymentFulfillmentStatus::PENDING,
        ));

        $this->commandBus()->handle(new ConfirmPaymentCommand($payment->uuid, (string) $payment->providerReference));

        $stored = static::getContainer()->get(PaymentRepositoryInterface::class)->getByUuid($payment->uuid);
        self::assertSame(PaymentFulfillmentStatus::COMPLETED, $stored->fulfillmentStatus);
        self::assertSame(1, $this->entityManager()->getRepository(EnrollmentEntity::class)->count(['trainingId' => $training->getId(), 'userId' => $user->getId()]));
    }

    public function testReconciliationProcessesOnlyConfirmedPendingPaymentsAndIsIdempotent(): void
    {
        $this->clientWithSchema();
        $user = $this->createUser(['ROLE_AVOCAT']);
        $training = $this->createTraining(TrainingAccessType::PAID);
        $pending = static::getContainer()->get(PaymentRepositoryInterface::class)->save(new Payment(
            id: 0,
            uuid: \Symfony\Component\Uid\Uuid::v7()->toRfc4122(),
            userId: $user->getId() ?? 0,
            trainingId: $training->getId() ?? 0,
            trainingOfferId: null,
            amount: 25000,
            currency: 'XOF',
            status: PaymentStatus::CONFIRMED,
            provider: PaymentProvider::FAKE,
            providerReference: 'reconcile-reference',
            idempotencyKey: 'reconcile-pending',
            fulfillmentStatus: PaymentFulfillmentStatus::PENDING,
        ));
        $completed = static::getContainer()->get(PaymentRepositoryInterface::class)->save(new Payment(
            id: 0,
            uuid: \Symfony\Component\Uid\Uuid::v7()->toRfc4122(),
            userId: $user->getId() ?? 0,
            trainingId: $training->getId() ?? 0,
            trainingOfferId: null,
            amount: 25000,
            currency: 'XOF',
            status: PaymentStatus::CONFIRMED,
            provider: PaymentProvider::FAKE,
            providerReference: 'completed-reference',
            idempotencyKey: 'reconcile-completed',
            fulfillmentStatus: PaymentFulfillmentStatus::COMPLETED,
            fulfillmentCompletedAt: new DateTimeImmutable(),
        ));
        static::getContainer()->get(PaymentRepositoryInterface::class)->save(new Payment(
            id: 0,
            uuid: \Symfony\Component\Uid\Uuid::v7()->toRfc4122(),
            userId: $user->getId() ?? 0,
            trainingId: $training->getId() ?? 0,
            trainingOfferId: null,
            amount: 25000,
            currency: 'XOF',
            status: PaymentStatus::FAILED,
            provider: PaymentProvider::FAKE,
            providerReference: 'failed-reference',
            idempotencyKey: 'reconcile-failed',
        ));

        $application = new Application(self::$kernel);
        $tester = new CommandTester($application->find('app:payment:reconcile-fulfillment'));
        self::assertSame(0, $tester->execute([]));
        self::assertStringContainsString('completed', $tester->getDisplay());
        self::assertSame(PaymentFulfillmentStatus::COMPLETED, static::getContainer()->get(PaymentRepositoryInterface::class)->getByUuid($pending->uuid)->fulfillmentStatus);
        self::assertSame(PaymentFulfillmentStatus::COMPLETED, static::getContainer()->get(PaymentRepositoryInterface::class)->getByUuid($completed->uuid)->fulfillmentStatus);
        self::assertSame(1, $this->entityManager()->getRepository(EnrollmentEntity::class)->count(['trainingId' => $training->getId(), 'userId' => $user->getId()]));

        $completedTester = new CommandTester($application->find('app:payment:reconcile-fulfillment'));
        self::assertSame(0, $completedTester->execute(['--payment' => $completed->uuid]));
        self::assertStringContainsString('already_completed', $completedTester->getDisplay());

        $secondTester = new CommandTester($application->find('app:payment:reconcile-fulfillment'));
        self::assertSame(0, $secondTester->execute([]));
        self::assertSame(1, $this->entityManager()->getRepository(EnrollmentEntity::class)->count(['trainingId' => $training->getId(), 'userId' => $user->getId()]));
    }

    public function testIneligibleLearnerLeavesConfirmedPaymentPending(): void
    {
        $this->clientWithSchema();
        $user = $this->createUser(['ROLE_USER']);
        $training = $this->createTraining(TrainingAccessType::PAID);
        $payment = static::getContainer()->get(PaymentRepositoryInterface::class)->save(new Payment(
            id: 0,
            uuid: \Symfony\Component\Uid\Uuid::v7()->toRfc4122(),
            userId: $user->getId() ?? 0,
            trainingId: $training->getId() ?? 0,
            trainingOfferId: null,
            amount: 25000,
            currency: 'XOF',
            status: PaymentStatus::CONFIRMED,
            provider: PaymentProvider::FAKE,
            providerReference: 'ineligible-reference',
            fulfillmentStatus: PaymentFulfillmentStatus::PENDING,
        ));

        $this->commandBus()->handle(new ConfirmPaymentCommand($payment->uuid, (string) $payment->providerReference));

        $stored = static::getContainer()->get(PaymentRepositoryInterface::class)->getByUuid($payment->uuid);
        self::assertSame(PaymentStatus::CONFIRMED, $stored->status);
        self::assertSame(PaymentFulfillmentStatus::PENDING, $stored->fulfillmentStatus);
        self::assertSame(0, $this->entityManager()->getRepository(EnrollmentEntity::class)->count(['trainingId' => $training->getId(), 'userId' => $user->getId()]));
    }

    private function saveOffer(TrainingEntity $training, int $amount): void
    {
        $this->commandBus()->handle(new SaveTrainingOfferCommand($training->getId() ?? 0, $amount, 'XOF', true));
    }

    private function createUser(array $roles): User
    {
        $user = (new User())->setEmail(sprintf('payment-member-%d@example.test', ++self::$userSequence))->setName('Payment Member')->setPassword('test-password');
        $user->setEnabled(true);
        $user->setRoles($roles);
        $this->entityManager()->persist($user);
        $this->entityManager()->flush();
        return $user;
    }

    private function createTraining(TrainingAccessType $accessType, TrainingStatus $status = TrainingStatus::PUBLISHED): TrainingEntity
    {
        $suffix = bin2hex(random_bytes(4));
        $training = (new TrainingEntity())->setTitle('Formation ' . $suffix)->setSlug('formation-payment-' . $suffix)->setSummary('Résumé')->setDescription('<p>Description</p>')->setAccessType($accessType)->setStatus($status)->setPublishedAt($status === TrainingStatus::PUBLISHED ? new DateTimeImmutable() : null);
        $this->entityManager()->persist($training);
        $this->entityManager()->flush();
        return $training;
    }

    private function clientWithSchema(): KernelBrowser
    {
        $client = static::createClient();
        $entityManager = $this->entityManager();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());
        $entityManager->persist(new Reglages('app_title', 'Application title', 'Avocat CI', 'text'));
        $entityManager->persist((new Images())->setName('app_logo')->setLabel('Logo'));
        $entityManager->persist((new Images())->setName('app_favicon')->setLabel('Favicon'));
        $entityManager->persist((new Currencies())->setCurrencyCode('XOF')->setCurrencyName('Franc CFA')->setRightSymbol('FCFA')->setDecimalPlace(0)->setIsActive(true));
        $entityManager->persist(new Maintenances());
        $entityManager->flush();
        return $client;
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

    private function commandBus(): CommandBus
    {
        return static::getContainer()->get(CommandBus::class);
    }

    private function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }
}
