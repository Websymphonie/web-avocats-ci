<?php
declare(strict_types=1);

namespace Websymphonie\SharedContext\Presenter;

use Override;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyController;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandBus;
use Websymphonie\SharedContext\Application\Service\Messaging\MessageBus;
use Websymphonie\SharedContext\Application\Service\Messaging\QueryBus;
use Websymphonie\SharedContext\Domain\Service\Flash\FlashServiceInterface;
use Websymphonie\SharedContext\Presenter\Service\Image\ImageHelperInterface;

abstract class AbstractController extends SymfonyController
{
    #[Override]
    public static function getSubscribedServices(): array
    {
        $subscribedServices = parent::getSubscribedServices();
        $subscribedServices[] = CommandBus::class;
        $subscribedServices[] = QueryBus::class;
        $subscribedServices[] = MessageBus::class;
        $subscribedServices[] = FlashServiceInterface::class;
        $subscribedServices[] = ImageHelperInterface::class;
        return $subscribedServices;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function handleCommand(object $command): mixed
    {
        return $this->container->get(CommandBus::class)->handle($command);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function handleQuery(object $query): mixed
    {
        return $this->container->get(QueryBus::class)->handle($query);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function handleMessage(object $message): void
    {
        $this->container->get(MessageBus::class)->dispatch($message);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function flash(): FlashServiceInterface
    {
        return $this->container->get(FlashServiceInterface::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function imageHelper(): ImageHelperInterface
    {
        return $this->container->get(ImageHelperInterface::class);
    }
}