<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Batonnier;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Websymphonie\ContentContext\Application\Usecase\Command\Batonnier\CreateBatonnierMandateCommand;
use Websymphonie\ContentContext\Domain\Exception\BatonnierMandateConflictException;
use Websymphonie\ContentContext\Domain\Model\BatonnierMandate;
use Websymphonie\ContentContext\Domain\Repository\BatonnierMandateRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class CreateBatonnierMandateHandler implements CommandHandler
{
    public function __construct(private BatonnierMandateRepositoryInterface $repository, private MediaUploadServiceInterface $mediaUpload)
    {
    }

    public function __invoke(CreateBatonnierMandateCommand $command): BatonnierMandate
    {
        if ($command->mandateEndedAt === null && $this->repository->findCurrent() !== null) {
            throw new BatonnierMandateConflictException();
        }

        $media = null;
        try {
            $media = $command->portrait !== null ? $this->mediaUpload->upload($command->portrait, 'institution/portraits') : null;
            return $this->repository->save(new BatonnierMandate(0, '', trim($command->fullName), $media?->id, $command->mandateStartedAt ?? throw new \InvalidArgumentException('La date de début du mandat est obligatoire.'), $command->mandateEndedAt, $command->summary));
        } catch (UniqueConstraintViolationException $exception) {
            if ($media !== null) { try { $this->mediaUpload->delete($media); } catch (\Throwable) {} }
            throw new BatonnierMandateConflictException(previous: $exception);
        } catch (\Throwable $exception) {
            if ($media !== null) { try { $this->mediaUpload->delete($media); } catch (\Throwable) {} }
            throw $exception;
        }
    }
}
