<?php

declare(strict_types=1);

namespace Websymphonie\ContactContext\Application\Usecase\CommandHandler;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;
use Websymphonie\ContactContext\Application\Service\ContactMessageDeliveryInterface;
use Websymphonie\ContactContext\Application\Usecase\Command\RetryContactMessageDeliveryCommand;
use Websymphonie\ContactContext\Domain\Event\ContactMessageDeliveryRetryEvent;
use Websymphonie\ContactContext\Domain\Enum\ContactMessageDeliveryStatus;
use Websymphonie\ContactContext\Domain\Model\ContactMessage;
use Websymphonie\ContactContext\Domain\Repository\ContactMessageRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Actor\CurrentActorProvider;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class RetryContactMessageDeliveryHandler implements CommandHandler
{
    public function __construct(
        private ContactMessageRepositoryInterface $repository,
        private ContactMessageDeliveryInterface $delivery,
        private LoggerInterface $logger,
        private ?EventDispatcherInterface $eventDispatcher = null,
        private ?CurrentActorProvider $actorProvider = null,
    ) {
    }

    public function __invoke(RetryContactMessageDeliveryCommand $command): ContactMessage
    {
        $message = $this->repository->claimForRetry($command->messageUuid);

        try {
            $this->delivery->send($message);
            $message->markSent(new DateTimeImmutable());
        } catch (Throwable $exception) {
            $message->markFailed();
            $message = $this->repository->save($message);
            $this->logger->error('La reprise de l’envoi du message de contact a échoué.', [
                'contact_message_id' => $message->id,
                'exceptionClass' => $exception::class,
                'failureMessage' => 'Contact message delivery retry failed.',
            ]);
            $this->dispatchAudit($message, ContactMessageDeliveryStatus::FAILED);

            return $message;
        }

        $message = $this->repository->save($message);
        $this->dispatchAudit($message, ContactMessageDeliveryStatus::SENT);

        return $message;
    }

    private function dispatchAudit(ContactMessage $message, ContactMessageDeliveryStatus $newStatus): void
    {
        $this->eventDispatcher?->dispatch(new ContactMessageDeliveryRetryEvent(
            messageUuid: $message->uuid,
            previousStatus: ContactMessageDeliveryStatus::FAILED->value,
            newStatus: $newStatus->value,
            actorUserId: $this->actorProvider?->currentUserId(),
        ));
    }
}
