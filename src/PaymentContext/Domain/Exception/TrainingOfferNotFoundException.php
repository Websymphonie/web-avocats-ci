<?php

declare(strict_types=1);

namespace Websymphonie\PaymentContext\Domain\Exception;

final class TrainingOfferNotFoundException extends PaymentException
{
    public static function withTraining(int $trainingId): self { return new self(sprintf('Aucun tarif n’est configuré pour la formation #%d.', $trainingId)); }
    public function translationId(): string { return 'exceptions.payment.training_offer_not_found'; }
}
