<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\News;

use Websymphonie\ContentContext\Application\Usecase\Command\News\PublishNewsCommand;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class PublishNewsHandler implements CommandHandler
{
    public function __construct(private NewsRepositoryInterface $repository) {}
    public function __invoke(PublishNewsCommand $command): void { $news = $this->repository->getById($command->id); $news->publish(); $this->repository->save($news); }
}
