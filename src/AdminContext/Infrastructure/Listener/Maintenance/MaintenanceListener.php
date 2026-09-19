<?php

declare(strict_types=1);

namespace Websymphonie\AdminContext\Infrastructure\Listener\Maintenance;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Websymphonie\AdminContext\Infrastructure\Persistence\Doctrine\Entity\Maintenance\Maintenances;
use Websymphonie\SharedContext\Domain\Enum\CacheEnum;
use Websymphonie\SharedContext\Domain\Service\Cache\CacheServiceInterface;
use Websymphonie\SharedContext\Presenter\Service\Filesystem\FilesystemesServices;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Maintenances::class)]
readonly class MaintenanceListener
{
    public function __construct(
        private FilesystemesServices  $fileSystemesServices,
        private CacheServiceInterface $cacheService
    )
    {
    }

    public function postUpdate(Maintenances $maintenance): void
    {
        $this->cacheService->invalidateTag(CacheEnum::CACHE_DETAIL_MAINTENANCE->value);
        if ($maintenance->getActive()) {
            $this->fileSystemesServices->touch('.maintenance-ON');
        } else {
            $this->fileSystemesServices->remove('.maintenance-ON');
        }
    }
}
