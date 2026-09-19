<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Application\Usecase\QueryHandler\Notification;

use Symfony\Bundle\SecurityBundle\Security;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Application\Usecase\Query\Notification\GetNotificationDetailsQuery;
use Websymphonie\NotificationContext\Domain\Exception\Notification\NotificationNotFound;
use Websymphonie\NotificationContext\Domain\Repository\Notification\NotificationModelRepository;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Factory\NotificationFactory;
use Websymphonie\NotificationContext\Presenter\ViewModel\Notification\NotificationDetailViewModel;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;

final readonly class GetNotificationDetailsQueryHandler implements QueryHandler
{
    public function __construct(
        private NotificationModelRepository $repository,
        private NotificationFactory         $factory,
        private Security                    $security,
    )
    {
    }

    public function __invoke(GetNotificationDetailsQuery $query): NotificationDetailViewModel
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw NotificationNotFound::withId($query->notificationId);
        }

        $requestModel = $this->factory->fromEntity($this->repository->getAccessibleById($query->notificationId, $user));

        if ($requestModel === null) {
            throw NotificationNotFound::withId($query->notificationId);
        }

        return new NotificationDetailViewModel($requestModel);
    }
}
