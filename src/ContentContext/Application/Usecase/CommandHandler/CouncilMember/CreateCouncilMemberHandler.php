<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\CouncilMember;

use Websymphonie\ContentContext\Application\Usecase\Command\CouncilMember\CreateCouncilMemberCommand;
use Websymphonie\ContentContext\Domain\Model\CouncilMember;
use Websymphonie\ContentContext\Domain\Repository\CouncilMemberRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class CreateCouncilMemberHandler implements CommandHandler
{
    public function __construct(private CouncilMemberRepositoryInterface $repository, private MediaUploadServiceInterface $mediaUpload)
    {
    }

    public function __invoke(CreateCouncilMemberCommand $command): CouncilMember
    {
        $media = null;
        try {
            $media = $command->portrait !== null ? $this->mediaUpload->upload($command->portrait, 'institution/portraits') : null;
            return $this->repository->save(new CouncilMember(0, '', trim($command->fullName), trim($command->function), $media?->id, $command->sortOrder, $command->mandateStartedAt, $command->mandateEndedAt));
        } catch (\Throwable $exception) {
            if ($media !== null) { try { $this->mediaUpload->delete($media); } catch (\Throwable) {} }
            throw $exception;
        }
    }
}
