<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideo;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\ArchiveEditorialVideoCommand;
use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class ArchiveEditorialVideoHandler implements CommandHandler { public function __construct(private EditorialVideoRepositoryInterface $repository, private ?ContentLifecycleEventPublisher $eventPublisher = null) {} public function __invoke(ArchiveEditorialVideoCommand $command): void { $video = $this->repository->getById($command->id); $video->archive(); $video = $this->repository->save($video); $this->eventPublisher?->publish('EDITORIAL_VIDEO', 'ARCHIVED', $video->uuid, $video->title, $video->slug); } }
