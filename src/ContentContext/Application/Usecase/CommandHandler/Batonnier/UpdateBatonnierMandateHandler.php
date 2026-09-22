<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Batonnier;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Websymphonie\ContentContext\Application\Usecase\Command\Batonnier\UpdateBatonnierMandateCommand;
use Websymphonie\ContentContext\Domain\Exception\BatonnierMandateConflictException;
use Websymphonie\ContentContext\Domain\Model\BatonnierMandate;
use Websymphonie\ContentContext\Domain\Repository\BatonnierMandateRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdateBatonnierMandateHandler implements CommandHandler
{
    public function __construct(private BatonnierMandateRepositoryInterface $repository, private MediaUploadServiceInterface $mediaUpload, private MediaRepositoryInterface $mediaRepository)
    {
    }

    public function __invoke(UpdateBatonnierMandateCommand $command): BatonnierMandate
    {
        $mandate = $this->repository->getById($command->id);
        $current = $this->repository->findCurrent();
        if ($command->mandateEndedAt === null && $current !== null && $current->id !== $mandate->id) {
            throw new BatonnierMandateConflictException();
        }

        $oldPortraitId = $mandate->portraitMediaId;
        $media = null;
        try {
            $media = $command->portrait !== null ? $this->mediaUpload->upload($command->portrait, 'institution/portraits') : null;
            $mandate->update(trim($command->fullName), $command->mandateStartedAt ?? throw new \InvalidArgumentException('La date de début du mandat est obligatoire.'), $command->mandateEndedAt, $command->summary);
            if ($media !== null) { $mandate->setPortraitMedia($media->id); }
            elseif ($command->removePortrait) { $mandate->setPortraitMedia(null); }
            $saved = $this->repository->save($mandate);
            if ($oldPortraitId !== null && (($media !== null) || $command->removePortrait)) { $this->removeIfOrphaned($oldPortraitId); }
            return $saved;
        } catch (UniqueConstraintViolationException $exception) {
            if ($media !== null) { try { $this->mediaUpload->delete($media); } catch (\Throwable) {} }
            throw new BatonnierMandateConflictException(previous: $exception);
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
            // Replacing a portrait must not fail after the new reference is persisted.
        }
    }
}
