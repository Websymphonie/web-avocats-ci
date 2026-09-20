<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideo;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\DeleteEditorialVideoCommand;
use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class DeleteEditorialVideoHandler implements CommandHandler { public function __construct(private EditorialVideoRepositoryInterface $repository, private ?ContentLifecycleEventPublisher $eventPublisher = null) {} public function __invoke(DeleteEditorialVideoCommand $command): void { $video = $this->repository->getById($command->id); $this->repository->delete($video); $this->eventPublisher?->publish('EDITORIAL_VIDEO', 'DELETED', $video->uuid, $video->title, $video->slug); } }
