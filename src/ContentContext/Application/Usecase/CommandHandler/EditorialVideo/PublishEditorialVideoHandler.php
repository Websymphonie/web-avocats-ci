<?php
declare(strict_types=1);
namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\EditorialVideo;
use Websymphonie\ContentContext\Application\Usecase\Command\EditorialVideo\PublishEditorialVideoCommand;
use Websymphonie\ContentContext\Domain\Repository\EditorialVideoRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;
final readonly class PublishEditorialVideoHandler implements CommandHandler { public function __construct(private EditorialVideoRepositoryInterface $repository) {} public function __invoke(PublishEditorialVideoCommand $command): void { $video = $this->repository->getById($command->id); $video->publish(); $this->repository->save($video); } }
