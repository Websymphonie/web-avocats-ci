<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\CouncilMember;

use Websymphonie\ContentContext\Application\Usecase\Command\CouncilMember\UpdateCouncilMemberCommand;
use Websymphonie\ContentContext\Domain\Model\CouncilMember;
use Websymphonie\ContentContext\Domain\Repository\CouncilMemberRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdateCouncilMemberHandler implements CommandHandler
{
    public function __construct(private CouncilMemberRepositoryInterface $repository, private MediaUploadServiceInterface $mediaUpload, private MediaRepositoryInterface $mediaRepository)
    {
    }

    public function __invoke(UpdateCouncilMemberCommand $command): CouncilMember
    {
        $member = $this->repository->getById($command->id);
        $oldPortraitId = $member->portraitMediaId;
        $media = null;
        try {
            $media = $command->portrait !== null ? $this->mediaUpload->upload($command->portrait, 'institution/portraits') : null;
            $member->update(trim($command->fullName), trim($command->function), $command->sortOrder, $command->mandateStartedAt, $command->mandateEndedAt);
            if ($media !== null) { $member->setPortraitMedia($media->id); }
            elseif ($command->removePortrait) { $member->setPortraitMedia(null); }
            $saved = $this->repository->save($member);
            if ($oldPortraitId !== null && (($media !== null) || $command->removePortrait)) { $this->removeIfOrphaned($oldPortraitId); }
            return $saved;
        } catch (\Throwable $exception) {
            if ($media !== null) { try { $this->mediaUpload->delete($media); } catch (\Throwable) {} }
            throw $exception;
        }
    }

    private function removeIfOrphaned(int $mediaId): void
    {
        try {
            $this->mediaUpload->delete($this->mediaRepository->getById($mediaId));
        } catch (\Throwable) {
            // A portrait replacement must not fail after the new reference is persisted.
        }
    }
}
