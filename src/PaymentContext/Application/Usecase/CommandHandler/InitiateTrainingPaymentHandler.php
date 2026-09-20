<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Application\Usecase\CommandHandler;

use Websymphonie\IdentityContext\Application\Service\User\UserDirectoryInterface;
use Websymphonie\PaymentContext\Application\Service\ActiveTrainingEnrollmentCheckerInterface;
use Websymphonie\PaymentContext\Application\Service\PaymentGatewayInterface;
use Websymphonie\PaymentContext\Application\Service\TrainingCatalogInterface;
use Websymphonie\PaymentContext\Application\Usecase\Command\InitiateTrainingPaymentCommand;
use Websymphonie\PaymentContext\Domain\Enum\PaymentProvider;
use Websymphonie\PaymentContext\Domain\Exception\PaymentDeniedException;
use Websymphonie\PaymentContext\Domain\Model\Payment;
use Websymphonie\PaymentContext\Domain\Repository\PaymentRepositoryInterface;
use Websymphonie\PaymentContext\Domain\Repository\TrainingOfferRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class InitiateTrainingPaymentHandler implements CommandHandler
{
    public function __construct(private UserDirectoryInterface $users, private TrainingCatalogInterface $trainings, private TrainingOfferRepositoryInterface $offers, private PaymentRepositoryInterface $payments, private ActiveTrainingEnrollmentCheckerInterface $enrollments, private PaymentGatewayInterface $gateway) {}

    public function __invoke(InitiateTrainingPaymentCommand $command): Payment
    {
        $key = trim($command->idempotencyKey);
        if ($key === '') { throw new PaymentDeniedException('Une clé d’idempotence est requise.'); }
        $existing = $this->payments->findByUserAndIdempotencyKey($command->userId, $key);
        if ($existing !== null) { return $existing; }
        if ($this->users->getById($command->userId)?->enabled !== true) { throw new PaymentDeniedException('Votre compte ne peut pas initier ce paiement.'); }
        $training = $this->trainings->getById($command->trainingId);
        if (!$training->isPublishedPaid()) { throw new PaymentDeniedException(); }
        if ($this->enrollments->hasActiveEnrollment($command->userId, $training->id)) { throw new PaymentDeniedException('Vous disposez déjà d’un accès actif à cette formation.'); }
        if ($this->payments->findPendingByUserAndTraining($command->userId, $training->id) !== null) { throw new PaymentDeniedException('Un paiement est déjà en attente pour cette formation.'); }
        $offer = $this->offers->findByTrainingId($training->id);
        if ($offer === null || !$offer->active) { throw new PaymentDeniedException('Aucun tarif actif n’est disponible pour cette formation.'); }
        $payment = new Payment(0, '', $command->userId, $training->id, $offer->id, $offer->amount, $offer->currency, provider: PaymentProvider::FAKE, idempotencyKey: $key);
        $initialization = $this->gateway->initializePayment($payment);
        $payment->assignProviderReference($initialization->providerReference);
        return $this->payments->save($payment);
    }
}
