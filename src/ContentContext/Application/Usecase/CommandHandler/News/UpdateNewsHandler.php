<?php

declare(strict_types=1);

namespace Websymphonie\ContentContext\Application\Usecase\CommandHandler\News;

use Symfony\Component\String\Slugger\SluggerInterface;
use Websymphonie\ContentContext\Application\Service\RichText\RichTextSanitizerInterface;
use Websymphonie\ContentContext\Application\Usecase\Command\News\UpdateNewsCommand;
use Websymphonie\ContentContext\Domain\Exception\NewsSlugAlreadyExistsException;
use Websymphonie\ContentContext\Domain\Repository\NewsCategoryRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\NewsRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\TagRepositoryInterface;
use Websymphonie\ContentContext\Domain\Repository\PhotoGalleryRepositoryInterface;
use Websymphonie\MediaContext\Application\Service\MediaUploadServiceInterface;
use Websymphonie\MediaContext\Domain\Exception\MediaInUseException;
use Websymphonie\MediaContext\Domain\Repository\MediaRepositoryInterface;
use Websymphonie\SharedContext\Application\Service\Messaging\CommandHandler;

final readonly class UpdateNewsHandler implements CommandHandler
{
    public function __construct(private NewsRepositoryInterface $repository, private NewsCategoryRepositoryInterface $categoryRepository, private TagRepositoryInterface $tagRepository, private PhotoGalleryRepositoryInterface $galleryRepository, private RichTextSanitizerInterface $richTextSanitizer, private SluggerInterface $slugger, private MediaUploadServiceInterface $mediaUpload, private MediaRepositoryInterface $mediaRepository) {}

    public function __invoke(UpdateNewsCommand $command): void
    {
        $news = $this->repository->getById($command->id);
        $slug = strtolower($this->slugger->slug($command->title)->toString());
        if ($news->status->value === 'DRAFT' && ($slug === '' || $this->repository->slugExists($slug, $news->id))) {
            throw NewsSlugAlreadyExistsException::withSlug($slug ?: $command->title);
        }

        $galleryId = $command->photoGalleryId !== null ? $this->galleryRepository->getById($command->photoGalleryId)->id : null;
        $oldCoverId = $news->coverMediaId;
        $media = null;
        try {
            $media = $command->cover !== null ? $this->mediaUpload->upload($command->cover, 'content/covers') : null;
            $news->update(trim($command->title), $slug ?: $news->slug, $command->excerpt ?: null, $this->richTextSanitizer->sanitize($command->body));
            $news->replaceCategories($this->categoryRepository->findByIds($command->categories));
            $news->replaceTags($this->tagRepository->findByIds($command->tags));
            $news->setPhotoGallery($galleryId);
            if ($media !== null) { $news->setCoverMedia($media->id); } elseif ($command->removeCover) { $news->setCoverMedia(null); }
            $this->repository->save($news);
            if ($oldCoverId !== null && (($media !== null) || $command->removeCover)) { $this->removeIfOrphaned($oldCoverId); }
        } catch (\Throwable $exception) {
            if ($media !== null) { try { $this->mediaUpload->delete($media); } catch (\Throwable) {} }
            throw $exception;
        }
    }

    private function removeIfOrphaned(int $mediaId): void
    {
        try { $this->mediaUpload->delete($this->mediaRepository->getById($mediaId)); } catch (MediaInUseException) {}
    }
}
