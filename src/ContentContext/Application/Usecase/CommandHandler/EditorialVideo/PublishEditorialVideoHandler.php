<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideo;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\PublishEditorialVideoCommand;
use Websymphonie\ContentContext\Application\Service\ContentLifecycleEventPublisher;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class PublishEditorialVideoHandler implements CommandHandler { public function __construct(private EditorialVideoRepositoryInterface $repository, private ?ContentLifecycleEventPublisher $eventPublisher = null) {} public function __invoke(PublishEditorialVideoCommand $command): void { $video = $this->repository->getById($command->id); $video->publish(); $video = $this->repository->save($video); $this->eventPublisher?->publish('EDITORIAL_VIDEO', 'PUBLISHED', $video->uuid, $video->title, $video->slug, $video->publishedAt); } }
