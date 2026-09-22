<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Usecase\CommandHandler;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Throwable;
use Websymphonie\ContactContext\Application\Service\ContactMessageDeliveryInterface;
use Websymphonie\ContactContext\Application\Service\ContactThrottleInterface;
use Websymphonie\ContactContext\Application\Usecase\Command\SubmitContactMessageCommand;
use Websymphonie\ContactContext\Domain\Model\ContactMessage;
use Websymphonie\ContactContext\Domain\Repository\ContactMessageRepositoryInterface;

final readonly class SubmitContactMessageHandler
{
    public function __construct(
        private ContactMessageRepositoryInterface $repository,
        private ContactThrottleInterface $throttle,
        private ContactMessageDeliveryInterface $delivery,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(SubmitContactMessageCommand $command): ContactMessage
    {
        if (!$command->consent) {
            throw new \InvalidArgumentException('Le consentement est nécessaire pour envoyer le message.');
        }

        if (!$this->throttle->consume($command->ip)) {
            throw new ContactRateLimitExceeded('Le nombre de demandes autorisées est temporairement dépassé.');
        }

        $now = new DateTimeImmutable();
        $message = $this->repository->save(new ContactMessage(
            id: 0,
            uuid: '',
            fullName: $command->fullName,
            email: $command->email,
            phone: $command->phone,
            subject: $command->subject,
            message: $command->message,
            consentAt: $now,
            submittedAt: $now,
        ));

        try {
            $this->delivery->send($message);
            $message->markSent(new DateTimeImmutable());
        } catch (Throwable $exception) {
            $message->markFailed();
            $this->logger->error('L’envoi du formulaire de contact a échoué.', [
                'contact_message_id' => $message->id,
                'exception' => $exception,
            ]);
        }

        return $this->repository->save($message);
    }
}
