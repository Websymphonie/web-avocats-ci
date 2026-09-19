<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideo;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\BulkDeleteEditorialVideosCommand;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class BulkDeleteEditorialVideosHandler implements CommandHandler { public function __construct(private EditorialVideoRepositoryInterface $repository) {} public function __invoke(BulkDeleteEditorialVideosCommand $command): void { foreach ($this->repository->findByIds(array_values(array_unique($command->ids))) as $video) { $this->repository->delete($video); } } }
