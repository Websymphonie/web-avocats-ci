<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\Event;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\Event\UpdateEventCommand;
use Websymphonie\ContentContext\Domain\Enum\EventStatus;
use Websymphonie\ContentContext\Domain\Exception\EventSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Exception\InvalidEventDetailsException;
use Websymphonie\ContentContext\Domain\Repository\EventCategoryRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\EventRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Domain\Exception\MediaInUseException;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdateEventHandler implements CommandHandler
{
    public function __construct(private EventRepositoryInterface $repository, private EventCategoryRepositoryInterface $categoryRepository, private TagRepositoryInterface $tagRepository, private PhotoGalleryRepositoryInterface $galleryRepository, private RichTextSanitizerInterface $sanitizer, private SluggerInterface $slugger, private MediaUploadServiceInterface $mediaUpload, private MediaRepositoryInterface $mediaRepository) {}
    public function __invoke(UpdateEventCommand $command): void
    {
        $event = $this->repository->getById($command->id);
        $slug = strtolower($this->slugger->slug($command->title)->toString());
        if ($event->status === EventStatus::DRAFT && ($slug === '' || $this->repository->slugExists($slug, $event->id))) { throw EventSlugAlreadyExistsException::withSlug($slug ?: $command->title); }
        $event->update(trim($command->title), $slug ?: $event->slug, $command->excerpt ?: null, $this->sanitizer->sanitize($command->description), $command->format, $command->startsAt ?? throw new InvalidEventDetailsException('La date de début est requise.'), $command->endsAt, self::clean($command->venueName), self::clean($command->address), self::clean($command->onlineUrl));
        $galleryId = $command->photoGalleryId !== null ? $this->galleryRepository->getById($command->photoGalleryId)->id : null;
        $oldCoverId = $event->coverMediaId;
        $media = null;
        try {
            $media = $command->cover !== null ? $this->mediaUpload->upload($command->cover, 'content/covers') : null;
            $event->replaceCategories($this->categoryRepository->findByIds($command->categories));
            $event->replaceTags($this->tagRepository->findByIds($command->tags));
            $event->setPhotoGallery($galleryId);
            if ($media !== null) { $event->setCoverMedia($media->id); } elseif ($command->removeCover) { $event->setCoverMedia(null); }
            $this->repository->save($event);
            if ($oldCoverId !== null && (($media !== null) || $command->removeCover)) { $this->removeIfOrphaned($oldCoverId); }
        } catch (\Throwable $exception) {
            if ($media !== null) { try { $this->mediaUpload->delete($media); } catch (\Throwable) {} }
            throw $exception;
        }
    }
    private function removeIfOrphaned(int $mediaId): void { try { $this->mediaUpload->delete($this->mediaRepository->getById($mediaId)); } catch (MediaInUseException) {} }
    private static function clean(?string $value): ?string { $value = $value !== null ? trim($value) : null; return $value === '' ? null : $value; }
}
