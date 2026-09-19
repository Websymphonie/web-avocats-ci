<?php
declare(strict_types=1);

namespace Websymphonie\NotificationContext\Application\Usecase\QueryHandler\Notification;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Websymphonie\IdentityContext\Infrastructure\Persistence\Doctrine\Entity\Users\User;
use Websymphonie\NotificationContext\Application\Usecase\Query\Notification\GetNotificationListQuery;
use Websymphonie\NotificationContext\Domain\Repository\Notification\NotificationModelRepository;
use Websymphonie\NotificationContext\Infrastructure\Persistence\Factory\NotificationFactory;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryHandler;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;
use Websymphonie\SharedContext\Presenter\ViewModel\PaginateListViewModel;

final readonly class GetNotificationListQueryHandler implements QueryHandler
{
    public function __construct(
        private PaginatorInterface          $paginator,
        private NotificationFactory         $factory,
        private NotificationModelRepository $repository,
        private Security                    $security,
        private CacheServiceInterface       $cacheService,
    )
    {
    }

    public function __invoke(GetNotificationListQuery $query): PaginateListViewModel
    {
        $authenticatedUser = $this->security->getUser();
        $query->user = $authenticatedUser instanceof User ? $authenticatedUser : null;
        $cacheKeyParts = [
            CacheEnum::CACHE_NOTIFICATION_LIST->value,
            $query->page,
            $query->limit,
            $query->message ?? 'null',
            $query->action->value ?? 'null',
            $query->type->value ?? 'null',
            $query->access->value ?? 'null',
            $query->user?->getId()
        ];

        $cacheKey = implode('_', $cacheKeyParts);
        $tags = [CacheEnum::CACHE_NOTIFICATION_LIST->value];

        $pagination = $this->cacheService->getCache($cacheKey, function () use ($query) {
            $data = $this->paginator->paginate(
                $this->repository->getNotificationQuery(query: $query),
                $query->page,
                $query->limit
            );
            $notificationModels = $this->factory->fromEntityList($data->getItems());
            $data->setItems($notificationModels);
            return $data;
        }, $tags);

        return new PaginateListViewModel(pagination: $pagination);
    }
}
